<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Models\Zone;
use App\Models\Admin;
use App\Models\Store;
use App\Models\Vendor;
use App\Models\Translation;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Models\VendorEmployee;
use App\Models\BusinessSetting;
use App\CentralLogics\StoreLogic;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Models\SubscriptionTransaction;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use MatanYadaev\EloquentSpatial\Objects\Point;

class VendorLoginController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $vendor_type= $request->vendor_type;

        $data = [
            'email' => $request->email,
            'password' => $request->password
        ];

        if($vendor_type == 'owner'){
            if (auth('vendor')->attempt($data)) {
                $token = $this->genarate_token($request['email']);
                $vendor = Vendor::where(['email' => $request['email']])->first();

            $storeSubscriptionCheck=  $this->storeSubscriptionCheck($vendor?->stores[0],$vendor,$token);

                    if(data_get($storeSubscriptionCheck,'type') != null){
                        return response()->json(data_get($storeSubscriptionCheck,'data'), data_get($storeSubscriptionCheck,'code'));
                    }


                if($vendor->stores[0]->store_type=="company"){
                    $errors = [];
                    array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
                    return response()->json([
                        'errors' => $errors
                    ], 401);
                }
                $vendor->auth_token = $token;
                $vendor->save();
                return response()->json(['token' => $token, 'zone_wise_topic'=> $vendor->stores[0]->zone->store_wise_topic], 200);
            }  else {
                $errors = [];
                array_push($errors, ['code' => 'auth-001', 'message' => translate('Credential_do_not_match,_please_try_again')]);
                return response()->json([
                    'errors' => $errors
                ], 401);
            }
        }elseif($vendor_type == 'employee'){

            if (auth('vendor_employee')->attempt($data)) {
                $token = $this->genarate_token($request['email']);
                $vendor = VendorEmployee::where(['email' => $request['email']])->first();
                // if($vendor->store->status == 0)
                // {
                //     return response()->json([
                //         'errors' => [
                //             ['code' => 'auth-002', 'message' => translate('messages.Your_account_is_suspended')]
                //         ]
                //     ], 403);
                // }

                $storeSubscriptionCheck=  $this->storeSubscriptionCheck($vendor?->store,$vendor,$token);
                if(data_get($storeSubscriptionCheck,'type') != null){
                    return response()->json(data_get($storeSubscriptionCheck,'data'), data_get($storeSubscriptionCheck,'code'));
                }

                $vendor->auth_token = $token;
                $vendor->save();
                $role = $vendor->role ? json_decode($vendor->role->modules):[];
                return response()->json(['token' => $token, 'zone_wise_topic'=> $vendor->store->zone->store_wise_topic, 'role'=>$role], 200);
            } else {
                $errors = [];
                array_push($errors, ['code' => 'auth-001', 'message' => translate('Credential_do_not_match,_please_try_again')]);
                return response()->json([
                    'errors' => $errors
                ], 401);
            }
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => translate('Credential_do_not_match,_please_try_again')]);
            return response()->json([
                'errors' => $errors
            ], 401);
        }

    }

    private function genarate_token($email)
    {
        $token = Str::random(120);
        $is_available = Vendor::where('auth_token', $token)->where('email', '!=', $email)->count();
        if($is_available)
        {
            $this->genarate_token($email);
        }
        return $token;
    }

    public function register(Request $request)
    {
        $status = BusinessSetting::where('key', 'toggle_store_registration')->first();
        if(!isset($status) || $status->value == '0')
        {
            return response()->json(['errors' => Helpers::error_processor('self-registration', translate('messages.store_self_registration_disabled'))]);
        }

        $validator = Validator::make($request->all(), [
            'f_name' => 'required|max:100',
            'l_name' => 'nullable|max:100',
            // 'name' => 'required|max:191',
            // 'address' => 'required|max:1000',
            'latitude' => 'required',
            'longitude' => 'required',
            'email' => 'required|unique:vendors',
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|max:20|unique:vendors',
            'minimum_delivery_time' => 'required',
            'maximum_delivery_time' => 'required',
            'delivery_time_type'=>'required',
            'password' => ['required', Password::min(8)->mixedCase()->letters()->numbers()->symbols()->uncompromised()],
            'zone_id' => 'required',
            'module_id' => 'required',
            'logo' => 'required',
            'tax' => 'required',
            'tax_id'=>'required|unique:stores,tax_id',
            'register_no'=>'required',
//            'tax_document'=>'required|file|max:5120|mimes:jpg,png,jpeg,gif,bmp,tif,tiff,pdf,doc,docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'tax_document'=>'required|file|max:5120|mimes:jpg,png,jpeg,gif,bmp,tif,tiff',
            'registration_document'=>'required|file|max:5120|mimes:jpg,png,jpeg,gif,bmp,tif,tiff',
//            'agreement_document'=>'required|file|max:5120|mimes:jpg,png,jpeg,gif,bmp,tif,tiff',

        ],[
            'password.required' => translate('The password is required'),
            'password.min_length' => translate('The password must be at least :min characters long'),
            'password.mixed' => translate('The password must contain both uppercase and lowercase letters'),
            'password.letters' => translate('The password must contain letters'),
            'password.numbers' => translate('The password must contain numbers'),
            'password.symbols' => translate('The password must contain symbols'),
            'password.uncompromised' => translate('The password is compromised. Please choose a different one'),
        ]);

        if($request->zone_id)
        {
            $zone = Zone::query()
            ->whereContains('coordinates', new Point($request->latitude, $request->longitude, POINT_SRID))
            ->where('id',$request->zone_id)
            ->first();
            if(!$zone){
                $validator->getMessageBag()->add('latitude', translate('messages.coordinates_out_of_zone'));
                return response()->json(['errors' => Helpers::error_processor($validator)], 403);
            }
        }

        $data = json_decode($request->translations, true);

        if (count($data) < 1) {
            $validator->getMessageBag()->add('translations', translate('messages.Name and description in english is required'));
        }

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $vendor = new Vendor();
        $vendor->f_name = $request->f_name;
        $vendor->l_name = $request->l_name;
        $vendor->email = $request->email;
        $vendor->phone = $request->phone;
        $vendor->password = bcrypt($request->password);
        $vendor->status = null;
        $vendor->save();

        $store = new Store;
        $store->name = $data[0]['value'];
        $store->phone = $request->phone;
        $store->email = $request->email;
        $store->logo = Helpers::upload('store/', 'png', $request->file('logo'));
        $store->cover_photo = Helpers::upload('store/cover/', 'png', $request->file('cover_photo'));
        $store->address = $data[1]['value'];
        $store->latitude = $request->latitude;
        $store->longitude = $request->longitude;
        $store->vendor_id = $vendor->id;
        $store->zone_id = $request->zone_id;
        $store->tax = $request->tax;
        $store->delivery_time = $request->minimum_delivery_time .'-'. $request->maximum_delivery_time.' '.$request->delivery_time_type;
        $store->module_id = $request->module_id;
        $store->status = 0;
        $store->tax_id =  $request->tax_id;
        $store->register_no = $request->register_no;
        // $license_extension = $request->file('license')->extension();
        // $store->license = Helpers::upload('store/', $license_extension, $request->file('license'));
        $tax_document_extension = $request->file('tax_document')->extension();
        $store->tax_document = Helpers::upload('store/', $tax_document_extension, $request->file('tax_document'));

        $registration_document_extension = $request->file('registration_document')->extension();
        $store->registration_document = Helpers::upload('store/', $registration_document_extension, $request->file('registration_document'));

//        $agreement_document_extension = $request->file('agreement_document')->extension();
//        $store->agreement_document = Helpers::upload('store/', $agreement_document_extension, $request->file('agreement_document'));
        $store->store_business_model = 'none';
        $store->save();
        $store->module->increment('stores_count');
        if(config('module.'.$store->module->module_type)['always_open'])
        {
            StoreLogic::insert_schedule($store->id);
        }

        foreach ($data as $key=>$i) {
            $data[$key]['translationable_type'] = 'App\Models\Store';
            $data[$key]['translationable_id'] = $store->id;
        }
        Translation::insert($data);

        try{
            $admin= Admin::where('role_id', 1)->first();
            $mail_status = Helpers::get_mail_status('registration_mail_status_store');
            if(config('mail.status') && $mail_status == '1'){
                Mail::to($request['email'])->send(new \App\Mail\VendorSelfRegistration('pending', $vendor->f_name.' '.$vendor->l_name,'store'));
            }
            $mail_status = Helpers::get_mail_status('store_registration_mail_status_admin');
            if(config('mail.status') && $mail_status == '1'){
                Mail::to($admin['email'])->send(new \App\Mail\StoreRegistration('pending', $vendor->f_name.' '.$vendor->l_name));
            }
        }catch(\Exception $ex){
            info($ex->getMessage());
        }

        return response()->json([
            'store_id'=> $store->id,
            'message'=>translate('messages.application_placed_successfully')],200);
    }





    private function storeSubscriptionCheck($store, $vendor,$token){


        if($store?->store_business_model == 'subscription' && $store->store_sub_trans && $store->store_sub_trans->transaction_status == 0){
            return [ 'type' => 'pending_payment',
                        'code' => 200,
                        'data'=> ['pending_payment' => ['id' =>$store->store_sub_trans->id ]
                        ]
                ];
        }

        if( $store?->store_business_model == 'none')
        {
            return [ 'type' => 'subscribed',
            'code' => 200,
            'data'=> [
                'subscribed' => ['store_id' => $store?->id, 'type' => 'new_join']
                ]
            ];
        }


        if($store->status == 0 && $vendor->status == 0)
        {

            return [ 'type' => 'errors',
            'code' => 403,
            'data'=> [
                'errors' => [
                    ['code' => 'auth-002', 'message' => translate('messages.Your_registration_is_not_approved_yet._You_can_login_once_admin_approved_the_request')]
                    ]
                ]
            ];



        } elseif($store->status == 0 && $vendor->status == 1){

            return [ 'type' => 'errors',
            'code' => 403,
            'data'=> [
                'errors' => [
                    ['code' => 'auth-002', 'message' => translate('messages.Your_account_is_suspended')]
                    ]
                ]
            ];

        }


        if ( $store?->store_business_model == 'subscription' ) {
            $store_sub = $store?->store_sub;
            if (isset($store_sub)) {
                if ($store_sub?->mobile_app == 0 ) {
                    return [ 'type' => 'errors',
                    'code' => 401,
                    'data'=> [
                        'errors' => [
                            ['code' => 'no_mobile_app', 'message' => translate('messages.Your Subscription Plan is not Active for Mobile App')]
                            ]
                        ]
                    ];
                }
            }
        }


        if( $store?->store_business_model == 'unsubscribed' && isset($store?->store_sub_update_application)){
            $vendor->auth_token = $token;
            $vendor?->save();
                    if($store?->store_sub_update_application?->max_product== 'unlimited' ){
                        $max_product_uploads= -1;
                    }
                    else{
                        $max_product_uploads= $store?->store_sub_update_application?->max_product - $store?->foods()?->count();
                        if($max_product_uploads > 0){
                            $max_product_uploads ?? 0;
                        }elseif($max_product_uploads < 0) {
                            $max_product_uploads = 0;
                        }
                    }

                $data['subscription_other_data'] =  [
                    'total_bill'=>  (float) SubscriptionTransaction::where('store_id', $store->id)->where('package_id', $store?->store_sub_update_application?->package?->id)->sum('paid_amount'),
                    'max_product_uploads' => (int) $max_product_uploads,
                    ];

            return response()->json(['token' => $token, 'zone_wise_topic'=> $store?->zone?->store_wise_topic,
            'subscription' => $store?->store_sub_update_application,
            'subscription_other_data' => $data['subscription_other_data'],
            'balance' =>(float)($vendor?->wallet?->balance ?? 0),
            'store_id' =>(int) $store?->id,
            'package' => $store?->store_sub_update_application?->package
            ], 205);
        }

        if($store?->store_business_model == 'unsubscribed' && !isset($store?->store_sub_update_application)){

            return [ 'type' => 'subscribed',
            'code' => 200,
            'data'=> [
                'subscribed' => [
                    'store_id' => $store?->id, 'type' => 'new_join']
                ]
            ];

        }
 return null ;
    }


}
