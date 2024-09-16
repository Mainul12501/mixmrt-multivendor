<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Setting;
use App\Models\Zone;
use App\Models\Order;
use App\Models\Module;
use App\Models\Currency;
use App\Models\DMVehicle;
use App\Models\DataSetting;
use App\Models\SocialMedia;
use App\Traits\AddonHelper;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Models\BusinessSetting;
use App\Models\OfflinePayments;
use App\Models\ReactTestimonial;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\OfflinePaymentMethod;
use Illuminate\Support\Facades\Http;
use App\Models\FlutterSpecialCriteria;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use MatanYadaev\EloquentSpatial\Objects\Point;

class ConfigController extends Controller
{
    private $map_api_key;

    use AddonHelper;

    function __construct()
    {
        $map_api_key_server=BusinessSetting::where(['key'=>'map_api_key_server'])->first();
        $map_api_key_server=$map_api_key_server?$map_api_key_server->value:null;
        $this->map_api_key = $map_api_key_server;
    }

    public function configuration()
    {
        $key = ['currency_code','cash_on_delivery','digital_payment','default_location','free_delivery_over','business_name','logo','address','phone','email_address','country','currency_symbol_position','app_minimum_version_android','app_url_android','app_minimum_version_ios','app_url_ios','app_url_android_store','app_minimum_version_ios_store','app_url_ios_store','app_minimum_version_ios_deliveryman','app_url_ios_deliveryman','app_minimum_version_android_deliveryman','app_minimum_version_android_store', 'app_url_android_deliveryman', 'customer_verification','schedule_order','order_delivery_verification','per_km_shipping_charge','minimum_shipping_charge','show_dm_earning','canceled_by_deliveryman','canceled_by_store','timeformat','toggle_veg_non_veg','toggle_dm_registration','toggle_store_registration','schedule_order_slot_duration','parcel_per_km_shipping_charge','parcel_minimum_shipping_charge','web_app_landing_page_settings','footer_text','landing_page_links','loyalty_point_exchange_rate', 'loyalty_point_item_purchase_point', 'loyalty_point_status', 'loyalty_point_minimum_point', 'wallet_status', 'dm_tips_status', 'ref_earning_status','ref_earning_exchange_rate','refund_active_status','refund','cancelation','shipping_policy','prescription_order_status','tax_included','icon','cookies_text','home_delivery_status','takeaway_status','additional_charge','additional_charge_status','additional_charge_name','dm_picture_upload_status','partial_payment_status','partial_payment_method','add_fund_status','offline_payment_status','websocket_url','websocket_port','websocket_status','guest_checkout_status','disbursement_type','restaurant_disbursement_waiting_time','dm_disbursement_waiting_time' , 'min_amount_to_pay_store' ,'min_amount_to_pay_dm','dm_agreement','store_agreement' ,'admin_commission',
        'new_customer_discount_status','new_customer_discount_amount','new_customer_discount_amount_type','new_customer_discount_amount_validity','new_customer_discount_validity_type','store_review_reply','subscription_business_model','commission_business_model','subscription_deadline_warning_days','subscription_deadline_warning_message','subscription_free_trial_days','subscription_free_trial_type','subscription_free_trial_status'



    ];

        $settings =  array_column(BusinessSetting::whereIn('key',$key)->get()->toArray(), 'value', 'key');
        $image_key = ['logo','icon','web_app_landing_page_settings'];
        $data = [];

        foreach ($image_key as $value){
            $data[$value.'_storage'] = BusinessSetting::where('key',$value)->first()?->storage[0]?->value ??'public';
        }


        $DataSetting =  DataSetting::where('type','flutter_landing_page')->where('key','download_user_app_links')->pluck('value', 'key')->toArray();
        $DataSetting =  isset($DataSetting['download_user_app_links'])? json_decode($DataSetting['download_user_app_links'],true):[];
        $landing_page_links=  isset($settings['landing_page_links'])?json_decode($settings['landing_page_links'] , true):[];
        $landing_page_links['app_url_android_status']=  data_get($DataSetting,'playstore_url_status',null);
        $landing_page_links['app_url_android']= data_get($DataSetting,'playstore_url',null);
        $landing_page_links['app_url_ios_status']=  data_get($DataSetting,'apple_store_url_status',null);
        $landing_page_links['app_url_ios']= data_get($DataSetting,'apple_store_url',null);


        $currency_symbol = Currency::where(['currency_code' => Helpers::currency_code()])->first()->currency_symbol;
        $cod = json_decode($settings['cash_on_delivery'], true);
        $digital_payment = json_decode($settings['digital_payment'], true);
        $default_location=isset($settings['default_location'])?json_decode($settings['default_location'], true):0;
        $free_delivery_over = $settings['free_delivery_over'];
        $free_delivery_over = isset($free_delivery_over)?(float)$free_delivery_over:$free_delivery_over;
        $additional_charge = isset($settings['additional_charge'])?(float)$settings['additional_charge']:0;
        $module = null;
        if(Module::active()->count()==1)
        {
            $module = Module::active()->first();
        }
        $languages = Helpers::get_business_settings('language');
        $lang_array = [];
        foreach ($languages as $language) {
            array_push($lang_array, [
                'key' => $language,
                'value' => Helpers::get_language_name($language)
            ]);
        }
        $system_languages = Helpers::get_business_settings('system_language');
        $sys_lang_array = [];
        foreach ($system_languages as $language) {
            array_push($sys_lang_array, [
                'key' => $language['code'],
                'value' => Helpers::get_language_name($language['code']),
                'direction' => $language['direction'],
                'default' => $language['default']
            ]);
        }
        $social_login = [];
        foreach (Helpers::get_business_settings('social_login') as $social) {
            $config = [
                'login_medium' => $social['login_medium'],
                'status' => (boolean)$social['status']
            ];
            array_push($social_login, $config);
        }
        $apple_login = [];
        $apples = Helpers::get_business_settings('apple_login');
        if(isset($apples)){
            foreach (Helpers::get_business_settings('apple_login') as $apple) {
                $config = [
                    'login_medium' => $apple['login_medium'],
                    'status' => (boolean)$apple['status'],
                    'client_id' => $apple['client_id']
                ];
                array_push($apple_login, $config);
            }
        }

        //addon settings publish status
        $published_status = 0; // Set a default value
        $payment_published_status = config('get_payment_publish_status');
        if (isset($payment_published_status[0]['is_published'])) {
            $published_status = $payment_published_status[0]['is_published'];
        }

        $active_addon_payment_lists = $published_status == 1 ? $this->getPaymentMethods() : $this->getDefaultPaymentMethods();

        $digital_payment_infos = array(
            'digital_payment' => (boolean)($digital_payment['status'] == 1 ? true : false),
            'plugin_payment_gateways' =>  (boolean)($published_status ? true : false),
            'default_payment_gateways' =>  (boolean)($published_status ? false : true)
        );
        $awsUrl = config('filesystems.disks.s3.url');
        $awsBucket = config('filesystems.disks.s3.bucket');
        $awsBaseURL = rtrim($awsUrl, '/').'/'.ltrim($awsBucket.'/');

        if( data_get($settings, 'subscription_free_trial_type') == 'year'){
            $trial_period =data_get($settings, 'subscription_free_trial_days') > 0 ? data_get($settings, 'subscription_free_trial_days')  / 365 : 0;
        } else if( data_get($settings, 'subscription_free_trial_type') == 'month'){
            $trial_period =data_get($settings, 'subscription_free_trial_days') > 0 ? data_get($settings, 'subscription_free_trial_days')  / 30 : 0;
        } else{
            $trial_period =data_get($settings, 'subscription_free_trial_days') > 0 ? data_get($settings, 'subscription_free_trial_days') : 0 ;
        }

        return response()->json([
            'business_name' => $settings['business_name'],
            // 'business_open_time' => $settings['business_open_time'],
            // 'business_close_time' => $settings['business_close_time'],
            'logo' => $settings['logo'],
            'logo_full_url' => Helpers::get_full_url('business',$settings['logo'],$data['logo_storage']??'public'),
            'address' => $settings['address'],
            'phone' => $settings['phone'],
            'email' => $settings['email_address'],
            // 'store_location_coverage' => Branch::where(['id'=>1])->first(['longitude','latitude','coverage']),
            // 'minimum_order_value' => (float)$settings['minimum_order_value'],
            'base_urls' => [
                'item_image_url' => asset('storage/app/public/product'),
                'refund_image_url' => asset('storage/app/public/refund'),
                'customer_image_url' => asset('storage/app/public/profile'),
                'banner_image_url' => asset('storage/app/public/banner'),
                'category_image_url' => asset('storage/app/public/category'),
                'brand_image_url' => asset('storage/app/public/brand'),
                'review_image_url' => asset('storage/app/public/review'),
                'notification_image_url' => asset('storage/app/public/notification'),
                'store_image_url' => asset('storage/app/public/store'),
                'vendor_image_url' => asset('storage/app/public/vendor'),
                'store_cover_photo_url' => asset('storage/app/public/store/cover'),
                'delivery_man_image_url' => asset('storage/app/public/delivery-man'),
                'chat_image_url' => asset('storage/app/public/conversation'),
                'campaign_image_url' => asset('storage/app/public/campaign'),
                'business_logo_url' => asset('storage/app/public/business'),
                'order_attachment_url' => asset('storage/app/public/order'),
                'module_image_url' => asset('storage/app/public/module'),
                'parcel_category_image_url' => asset('storage/app/public/parcel_category'),
                'landing_page_image_url' => asset('public/assets/landing/image'),
                'react_landing_page_images' => asset('storage/app/public/react_landing') ,
                'react_landing_page_feature_images' => asset('storage/app/public/react_landing/feature') ,
                'gateway_image_url' => asset('storage/app/public/payment_modules/gateway_image'),
            ],

            's3_base_urls' => [
                'item_image_url' => $awsBaseURL.'product',
                'refund_image_url' => $awsBaseURL.'refund',
                'customer_image_url' => $awsBaseURL.'profile',
                'banner_image_url' => $awsBaseURL.'banner',
                'category_image_url' => $awsBaseURL.'category',
                'brand_image_url' => $awsBaseURL.'brand',
                'review_image_url' => $awsBaseURL.'review',
                'notification_image_url' => $awsBaseURL.'notification',
                'store_image_url' => $awsBaseURL.'store',
                'vendor_image_url' => $awsBaseURL.'vendor',
                'store_cover_photo_url' => $awsBaseURL.'store/cover',
                'delivery_man_image_url' => $awsBaseURL.'delivery-man',
                'chat_image_url' => $awsBaseURL.'conversation',
                'campaign_image_url' => $awsBaseURL.'campaign',
                'business_logo_url' => $awsBaseURL.'business',
                'order_attachment_url' => $awsBaseURL.'order',
                'module_image_url' => $awsBaseURL.'module',
                'parcel_category_image_url' => $awsBaseURL.'parcel_category',
                'landing_page_image_url' => $awsBaseURL.'landing/image',
                'react_landing_page_images' => $awsBaseURL.'react_landing',
                'react_landing_page_feature_images' => $awsBaseURL.'react_landing/feature',
                'gateway_image_url' => $awsBaseURL.'payment_modules/gateway_image',
            ],
            'country' => $settings['country'],
            'default_location'=> [ 'lat'=> $default_location?$default_location['lat']:'23.757989', 'lng'=> $default_location?$default_location['lng']:'90.360587' ],
            'currency_symbol' => $currency_symbol,
            'currency_symbol_direction' => $settings['currency_symbol_position'],
            'app_minimum_version_android' => (float)$settings['app_minimum_version_android'],
            'app_url_android' => $settings['app_url_android'],
            'app_url_ios' => $settings['app_url_ios'],
            'app_minimum_version_ios' => (float)$settings['app_minimum_version_ios'],
            'app_minimum_version_android_store' => (float)(isset($settings['app_minimum_version_android_store']) ? $settings['app_minimum_version_android_store'] : 0),
            'app_url_android_store' => (isset($settings['app_url_android_store']) ? $settings['app_url_android_store'] : null),
            'app_minimum_version_ios_store' => (float)(isset($settings['app_minimum_version_ios_store']) ? $settings['app_minimum_version_ios_store'] : 0),
            'app_url_ios_store' => (isset($settings['app_url_ios_store']) ? $settings['app_url_ios_store'] : null),
            'app_minimum_version_android_deliveryman' => (float)(isset($settings['app_minimum_version_android_deliveryman']) ? $settings['app_minimum_version_android_deliveryman'] : 0),
            'app_url_android_deliveryman' => (isset($settings['app_url_android_deliveryman']) ? $settings['app_url_android_deliveryman'] : null),
            'app_minimum_version_ios_deliveryman' => (float)(isset($settings['app_minimum_version_ios_deliveryman']) ? $settings['app_minimum_version_ios_deliveryman'] : 0),
            'app_url_ios_deliveryman' => (isset($settings['app_url_ios_deliveryman']) ? $settings['app_url_ios_deliveryman'] : null),
            'customer_verification' => (boolean)$settings['customer_verification'],
            'prescription_order_status' => isset($settings['prescription_order_status'])?(boolean)$settings['prescription_order_status']:false,
            'schedule_order' => (boolean)$settings['schedule_order'],
            'order_delivery_verification' => (boolean)$settings['order_delivery_verification'],
            'cash_on_delivery' => (boolean)($cod['status'] == 1 ? true : false),
            'digital_payment' => (boolean)($digital_payment['status'] == 1 ? true : false),
            'digital_payment_info' => $digital_payment_infos,
            'per_km_shipping_charge' => (double)$settings['per_km_shipping_charge'],
            'minimum_shipping_charge' => (double)$settings['minimum_shipping_charge'],
            'free_delivery_over'=>$free_delivery_over,
            'demo'=>(boolean)(env('APP_MODE')=='demo'?true:false),
            'maintenance_mode' => (boolean)Helpers::get_business_settings('maintenance_mode') ?? 0,
            'order_confirmation_model'=>config('order_confirmation_model'),
            'show_dm_earning' => (boolean)$settings['show_dm_earning'],
            'canceled_by_deliveryman' => (boolean)$settings['canceled_by_deliveryman'],
            'canceled_by_store' => (boolean)$settings['canceled_by_store'],
            'timeformat' => (string)$settings['timeformat'],
            'language' => $lang_array,
            'sys_language' => $sys_lang_array,
            'social_login' => $social_login,
            'apple_login' => $apple_login,
            'toggle_veg_non_veg' => (boolean)$settings['toggle_veg_non_veg'],
            'toggle_dm_registration' => (boolean)$settings['toggle_dm_registration'],
            'toggle_store_registration' => (boolean)$settings['toggle_store_registration'],
            'refund_active_status' => (boolean)$settings['refund_active_status'],
            'schedule_order_slot_duration' => (int)$settings['schedule_order_slot_duration'],
            'digit_after_decimal_point' => (int)config('round_up_to_digit'),
            'module_config'=>config('module'),
            'module'=>$module,
            'parcel_per_km_shipping_charge' => (float)$settings['parcel_per_km_shipping_charge'],
            'parcel_minimum_shipping_charge' => (float)$settings['parcel_minimum_shipping_charge'],
//            'landing_page_settings'=> isset($settings['web_app_landing_page_settings'])?json_decode($settings['web_app_landing_page_settings'], true):null,
//            'landing_page_settings_full_url'=> $data['web_app_landing_page_settings_storage']??'public',
            'social_media'=>SocialMedia::active()->get()->toArray(),
            'footer_text'=>isset($settings['footer_text'])?$settings['footer_text']:'',
            'cookies_text'=>isset($settings['cookies_text'])?$settings['cookies_text']:'',
            'fav_icon' => $settings['icon'],
            'fav_icon_full_url' => Helpers::get_full_url('business',$settings['icon'],$data['icon_storage']??'public'),
            'landing_page_links'=>$landing_page_links,
            //Added Business Setting
            'dm_tips_status' => (int)(isset($settings['dm_tips_status']) ? $settings['dm_tips_status'] : 0),
            'loyalty_point_exchange_rate' => (int)(isset($settings['loyalty_point_item_purchase_point']) ? $settings['loyalty_point_exchange_rate'] : 0),
            'loyalty_point_item_purchase_point' => (float)(isset($settings['loyalty_point_item_purchase_point']) ? $settings['loyalty_point_item_purchase_point'] : 0.0),
            'loyalty_point_status' => (int)(isset($settings['loyalty_point_status']) ? $settings['loyalty_point_status'] : 0),
            'customer_wallet_status' => (int)(isset($settings['wallet_status']) ? $settings['wallet_status'] : 0),
            'ref_earning_status' => (int)(isset($settings['ref_earning_status']) ? $settings['ref_earning_status'] : 0),
            'ref_earning_exchange_rate' => (double)(isset($settings['ref_earning_exchange_rate']) ? $settings['ref_earning_exchange_rate'] : 0),
            'refund_policy' => (int)(self::get_settings_status('refund_policy_status')),
            'cancelation_policy' => (int)(self::get_settings_status('cancellation_policy_status')),
            'shipping_policy' => (int)(self::get_settings_status('shipping_policy_status')),
            'loyalty_point_minimum_point' => (int)(isset($settings['loyalty_point_minimum_point']) ? $settings['loyalty_point_minimum_point'] : 0),
            'tax_included' => (int)(isset($settings['tax_included']) ? $settings['tax_included'] : 0),
            'home_delivery_status' => (int)(isset($settings['home_delivery_status']) ? $settings['home_delivery_status'] : 0),
            'takeaway_status' => (int)(isset($settings['takeaway_status']) ? $settings['takeaway_status'] : 0),
            'active_payment_method_list' => $active_addon_payment_lists,
            'additional_charge_status' => (int)(isset($settings['additional_charge_status']) ? $settings['additional_charge_status'] : 0),
            'additional_charge_name' => (isset($settings['additional_charge_name']) ? $settings['additional_charge_name'] : 'Service Charge'),
            'additional_charge'=>$additional_charge,
            'partial_payment_status' => (int)(isset($settings['partial_payment_status']) ? $settings['partial_payment_status'] : 0),
            'partial_payment_method' => (isset($settings['partial_payment_method']) ? $settings['partial_payment_method'] : ''),
            'dm_picture_upload_status' => (int)(isset($settings['dm_picture_upload_status']) ? $settings['dm_picture_upload_status'] : 0),
            'add_fund_status' => (int)(isset($settings['add_fund_status']) ? $settings['add_fund_status'] : 0),
            'offline_payment_status' => (int)(isset($settings['offline_payment_status']) ? $settings['offline_payment_status'] : 0),
            'websocket_status' => (int) (isset($settings['websocket_status']) ? $settings['websocket_status'] : 0),
            'websocket_url' => (isset($settings['websocket_url']) ? $settings['websocket_url'] : ''),
            'websocket_port' => (int)(isset($settings['websocket_port']) ? $settings['websocket_port'] : 6001),
            'websocket_key' => env('PUSHER_APP_KEY'),
            'guest_checkout_status' => (int)(isset($settings['guest_checkout_status']) ? $settings['guest_checkout_status'] : 0),
            'disbursement_type' => (string)(isset($settings['disbursement_type']) ? $settings['disbursement_type'] : 'manual'),
            'restaurant_disbursement_waiting_time' => (int)(isset($settings['restaurant_disbursement_waiting_time']) ? $settings['restaurant_disbursement_waiting_time'] : 0),
            'dm_disbursement_waiting_time' => (int)(isset($settings['dm_disbursement_waiting_time']) ? $settings['dm_disbursement_waiting_time'] : 0),
            'min_amount_to_pay_store' => (float)(isset($settings['min_amount_to_pay_store']) ? $settings['min_amount_to_pay_store'] : 0),
            'min_amount_to_pay_dm' => (float)(isset($settings['min_amount_to_pay_dm']) ? $settings['min_amount_to_pay_dm'] : 0),
            'dm_agreement' => (boolean) (isset($settings['dm_agreement']) ?1  : 0),
            'store_agreement' => (boolean) (isset($settings['store_agreement']) ?1 : 0),
            'new_customer_discount_status' => (int)(isset($settings['new_customer_discount_status']) ? $settings['new_customer_discount_status'] : 0),
            'new_customer_discount_amount' => (float)(isset($settings['new_customer_discount_amount']) ? $settings['new_customer_discount_amount'] : 0),
            'new_customer_discount_amount_type' => (isset($settings['new_customer_discount_amount_type']) ? $settings['new_customer_discount_amount_type'] : 'amount'),
            'new_customer_discount_amount_validity' => (int)(isset($settings['new_customer_discount_amount_validity']) ? $settings['new_customer_discount_amount_validity'] : 0),
            'new_customer_discount_validity_type' => (isset($settings['new_customer_discount_validity_type']) ? $settings['new_customer_discount_validity_type'] : 'day'),
            'store_review_reply' => (int)(isset($settings['store_review_reply']) ? $settings['store_review_reply'] : 0),
            'admin_commission' => (float)(isset($settings['admin_commission']) ? $settings['admin_commission'] : 0),
            'subscription_business_model' => (int)(isset($settings['subscription_business_model']) ? $settings['subscription_business_model'] : 1),
            'commission_business_model' => (int)(isset($settings['commission_business_model']) ? $settings['commission_business_model'] : 1),
            'subscription_deadline_warning_days' => (int)(isset($settings['subscription_deadline_warning_days']) ? $settings['subscription_deadline_warning_days'] : 1),
            'subscription_deadline_warning_message' => isset($settings['subscription_deadline_warning_message']) ? $settings['subscription_deadline_warning_message'] : null,
            'subscription_free_trial_days' => (int)$trial_period,
            'subscription_free_trial_type' => (isset($settings['subscription_free_trial_type']) ? $settings['subscription_free_trial_type'] : 'day'),
            'subscription_free_trial_status' => (int)(isset($settings['subscription_free_trial_status']) ? $settings['subscription_free_trial_status'] : 0),
        ]);
    }

    public static function get_settings_status($name)
    {
        $data = DataSetting::where(['key' => $name])->first()?->value;
        return $data??0;
    }

    public function get_zone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'required',
            'lng' => 'required',
        ]);

        if ($validator->errors()->count()>0) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $zones = Zone::with('modules')->whereContains('coordinates', new Point($request->lat, $request->lng, POINT_SRID))->latest()->get(['id', 'status', 'cash_on_delivery', 'digital_payment','offline_payment', 'increased_delivery_fee','increased_delivery_fee_status','increase_delivery_charge_message']);
        if(count($zones)<1)
        {
            return response()->json([
                'errors'=>[
                    ['code'=>'coordinates','message'=>translate('messages.service_not_available_in_this_area')]
                ]
            ], 404);
        }
        $data = array_filter($zones->toArray(), function($zone){
            if($zone['status'] == 1) {
                return $zone;
            }
        });

        if (count($data) > 0) {
            return response()->json(['zone_id' => json_encode(array_column($data, 'id')), 'zone_data'=>array_values($data)], 200);
        }

        return response()->json([
            'errors'=>[
                ['code'=>'coordinates','message'=>translate('messages.we_are_temporarily_unavailable_in_this_area')]
            ]
        ], 403);
    }

    public function place_api_autocomplete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search_text' => 'required',
        ]);

        if ($validator->errors()->count()>0) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $response = Http::get('https://maps.googleapis.com/maps/api/place/autocomplete/json?input='.$request['search_text'].'&key='.$this->map_api_key.'&language='.app()->getLocale());
        return $response->json();
    }


    public function distance_api(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'origin_lat' => 'required',
            'origin_lng' => 'required',
            'destination_lat' => 'required',
            'destination_lng' => 'required',
        ]);

        if ($validator->errors()->count()>0) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $response = Http::get('https://maps.googleapis.com/maps/api/distancematrix/json?origins='.$request['origin_lat'].','.$request['origin_lng'].'&destinations='.$request['destination_lat'].','.$request['destination_lng'].'&key='.$this->map_api_key.'&mode=walking');
        return $response->json();
    }


    public function place_api_details(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'placeid' => 'required',
        ]);

        if ($validator->errors()->count()>0) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $response = Http::get('https://maps.googleapis.com/maps/api/place/details/json?placeid='.$request['placeid'].'&key='.$this->map_api_key);
        return $response->json();
    }

    public function geocode_api(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'required',
            'lng' => 'required',
        ]);

        if ($validator->errors()->count()>0) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json?latlng='.$request->lat.','.$request->lng.'&key='.$this->map_api_key);
        return $response->json();
    }

    public function landing_page(){
        $key =['react_header_banner','banner_section_full','banner_section_half' ,'footer_logo','app_section_image',
        'react_feature','app_download_button' ,'discount_banner','landing_page_links','delivery_service_section','hero_section','download_app_section','landing_page_text'];
        $settings =  array_column(BusinessSetting::whereIn('key', $key)->get()->toArray(), 'value', 'key');
        return  response()->json(
            [
                'react_header_banner'=>(isset($settings['react_header_banner']) )  ? $settings['react_header_banner'] : null ,
                'app_section_image'=> (isset($settings['app_section_image'])) ? $settings['app_section_image']  : null,
                'footer_logo'=> (isset($settings['footer_logo'])) ? $settings['footer_logo'] : null,
                'banner_section_full'=> (isset($settings['banner_section_full']) )  ? json_decode($settings['banner_section_full'], true) : null ,
                'banner_section_half'=>(isset($settings['banner_section_half']) )  ? json_decode($settings['banner_section_half'], true) : [],
                'react_feature'=> (isset($settings['react_feature'])) ? json_decode($settings['react_feature'], true) : [],
                'app_download_button'=> (isset($settings['app_download_button'])) ? json_decode($settings['app_download_button'], true) : [],
                'discount_banner'=> (isset($settings['discount_banner'])) ? json_decode($settings['discount_banner'], true) : null,
                'landing_page_links'=> (isset($settings['landing_page_links'])) ? json_decode($settings['landing_page_links'], true) : null,
                'hero_section'=> (isset($settings['hero_section'])) ? json_decode($settings['hero_section'], true) : null,
                'delivery_service_section'=> (isset($settings['delivery_service_section'])) ? json_decode($settings['delivery_service_section'], true) : null,
                'download_app_section'=> (isset($settings['download_app_section'])) ? json_decode($settings['download_app_section'], true) : null,
                'landing_page_text'=> (isset($settings['landing_page_text'])) ? json_decode($settings['landing_page_text'], true) : null,
        ]);
    }

    public function extra_charge(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'weight' => 'required',
        ]);
        if ($validator->errors()->count() > 0) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $weight = $request->weight ?? 1;
        $data = DmVehicle::active()
        ->where(function ($query) use ($weight) {
            $query->where('minimum_weight', '<=', $weight)->where('maximum_weight', '>=', $weight)
            ->orWhere(function ($query) use ($weight) {
                $query->where('minimum_weight', '>=', $weight);
            });
        })->orderBy('minimum_weight')->first();

            $extra_charges = (float) (isset($data) ? $data->extra_charges  : 0);
        return response()->json($extra_charges,200);
    }

    public function get_vehicles(Request $request){
        $data = DMVehicle::active()->get(['id','type']);
        return response()->json($data, 200);
    }

    public function react_landing_page()
    {
        $datas =  DataSetting::with('translations')->where('type','react_landing_page')->get();
        $data = [];
        foreach ($datas as $key => $value) {
            if(count($value->translations)>0){
                $cred = [
                    $value->key => $value->translations[0]['value'],
                ];
                array_push($data,$cred);
            }else{
                $cred = [
                    $value->key => $value->value,
                ];
                array_push($data,$cred);
            }
            if (isset($value->storage)) {

                $cred = [
                    $value->key.'_storage' => $value?->storage[0]?->value ?? 'public',
                ];
                array_push($data, $cred);
            }
        }
        $settings = [];
        foreach($data as $single_data){
            foreach($single_data as $key=>$single_value){
                $settings[$key] = $single_value;
            }
        }

        $reviews = ReactTestimonial::get();

        $awsUrl = config('filesystems.disks.s3.url');
        $awsBucket = config('filesystems.disks.s3.bucket');
        $awsBaseURL = rtrim($awsUrl, '/').'/'.ltrim($awsBucket.'/');

        $promotional_banners = [];
        foreach (json_decode($settings['promotion_banner'], true) as $value){
            $promotional_banners[] = Helpers::get_full_url('promotional_banner',$value['img'],$value['storage']??'public');
        }

        return  response()->json(
            [
                'base_urls' => [
                    'header_icon_url' => asset('storage/app/public/header_icon'),
                    'header_banner_url' => asset('storage/app/public/header_banner'),
                    'testimonial_image_url' => asset('storage/app/public/reviewer_image'),
                    'promotional_banner_url' => asset('storage/app/public/promotional_banner'),
                    'business_image_url' => asset('storage/app/public/business_image'),
                ],
                's3_base_urls' => [
                    'header_icon_url' => $awsBaseURL.'header_icon',
                    'header_banner_url' => $awsBaseURL.'header_banner',
                    'testimonial_image_url' => $awsBaseURL.'reviewer_image',
                    'promotional_banner_url' => $awsBaseURL.'promotional_banner',
                    'business_image_url' => $awsBaseURL.'business_image',
                ],

                'header_title'=>(isset($settings['header_title']) )  ? $settings['header_title'] : null ,
                'header_sub_title'=>(isset($settings['header_sub_title']) )  ? $settings['header_sub_title'] : null ,
                'header_tag_line'=>(isset($settings['header_tag_line']) )  ? $settings['header_tag_line'] : null ,
                'header_icon'=>(isset($settings['header_icon']) )  ? $settings['header_icon'] : null ,
                'header_icon_full_url'=>Helpers::get_full_url('header_icon',(isset($settings['header_icon']) )  ? $settings['header_icon'] : null,isset($settings['header_icon_storage'])   ? $settings['header_icon_storage'] : 'public') ,
                'header_banner'=>(isset($settings['header_banner']) )  ? $settings['header_banner'] : null ,
                'header_banner_full_url'=>Helpers::get_full_url('header_banner',(isset($settings['header_banner']) )  ? $settings['header_banner'] : null,isset($settings['header_banner_storage'])   ? $settings['header_banner_storage'] : 'public') ,
                'company_title'=>(isset($settings['company_title']) )  ? $settings['company_title'] : null ,
                'company_sub_title'=>(isset($settings['company_sub_title']) )  ? $settings['company_sub_title'] : null ,
                'company_description'=>(isset($settings['company_description']) )  ? $settings['company_description'] : null ,
                'company_button_name'=>(isset($settings['company_button_name']) )  ? $settings['company_button_name'] : null ,
                'company_button_url'=>(isset($settings['company_button_url']) )  ? $settings['company_button_url'] : null ,
                'download_user_app_title'=>(isset($settings['download_user_app_title']) )  ? $settings['download_user_app_title'] : null ,
                'download_user_app_sub_title'=>(isset($settings['download_user_app_sub_title']) )  ? $settings['download_user_app_sub_title'] : null ,
                'earning_title'=>(isset($settings['earning_title']) )  ? $settings['earning_title'] : null ,
                'earning_sub_title'=>(isset($settings['earning_sub_title']) )  ? $settings['earning_sub_title'] : null ,
                'earning_seller_title'=>(isset($settings['earning_seller_title']) )  ? $settings['earning_seller_title'] : null ,
                'earning_seller_sub_title'=>(isset($settings['earning_seller_sub_title']) )  ? $settings['earning_seller_sub_title'] : null ,
                'earning_seller_button_name'=>(isset($settings['earning_seller_button_name']) )  ? $settings['earning_seller_button_name'] : null ,
                'earning_seller_button_url'=>(isset($settings['earning_seller_button_url']) )  ? $settings['earning_seller_button_url'] : null ,
                'earning_dm_title'=>(isset($settings['earning_dm_title']) )  ? $settings['earning_dm_title'] : null ,
                'earning_dm_sub_title'=>(isset($settings['earning_dm_sub_title']) )  ? $settings['earning_dm_sub_title'] : null ,
                'earning_dm_button_name'=>(isset($settings['earning_dm_button_name']) )  ? $settings['earning_dm_button_name'] : null ,
                'earning_dm_button_url'=>(isset($settings['earning_dm_button_url']) )  ? $settings['earning_dm_button_url'] : null ,
                'business_title'=>(isset($settings['business_title']) )  ? $settings['business_title'] : null ,
                'business_sub_title'=>(isset($settings['business_sub_title']) )  ? $settings['business_sub_title'] : null ,
                'business_image'=>(isset($settings['business_image']) )  ? $settings['business_image'] : null ,
                'business_image_full_url'=>Helpers::get_full_url('business_image',isset($settings['business_image'])   ? $settings['business_image'] : null,isset($settings['business_image_storage'])  ? $settings['business_image_storage'] : 'public') ,
                'testimonial_title'=>(isset($settings['testimonial_title']) )  ? $settings['testimonial_title'] : null ,
                'testimonial_list'=>(isset($reviews) )  ? $reviews : null ,
                'fixed_newsletter_title'=>(isset($settings['fixed_newsletter_title']) )  ? $settings['fixed_newsletter_title'] : null ,
                'fixed_newsletter_sub_title'=>(isset($settings['fixed_newsletter_sub_title']) )  ? $settings['fixed_newsletter_sub_title'] : null ,
                'fixed_footer_description'=>(isset($settings['fixed_footer_description']) )  ? $settings['fixed_footer_description'] : null ,
                'fixed_promotional_banner'=>(isset($settings['fixed_promotional_banner']) )  ? $settings['fixed_promotional_banner'] : null ,
                'fixed_promotional_banner_storage'=>Helpers::get_full_url('promotional_banner',(isset($settings['fixed_promotional_banner']) )  ? $settings['fixed_promotional_banner'] : null,(isset($settings['fixed_promotional_banner_storage']) )  ? $settings['fixed_promotional_banner_storage'] : 'public') ,



                'promotion_banners'=> (isset($settings['promotion_banner']) )  ? json_decode($settings['promotion_banner'], true) : null ,
                'promotion_banners_full_url'=> $promotional_banners ,
                'download_user_app_links'=> (isset($settings['download_user_app_links']) )  ? json_decode($settings['download_user_app_links'], true) : null ,
                'download_business_app_links'=> (isset($settings['download_business_app_links']) )  ? json_decode($settings['download_business_app_links'], true) : null ,
                // 'dm_app_earning_links'=> (isset($settings['dm_app_earning_links']) )  ? json_decode($settings['dm_app_earning_links'], true) : null ,
                // 'download_user_app_links'=> (isset($settings['download_app_links']) )  ? json_decode($settings['download_app_links'], true) : null ,
        ]);
    }

    public function flutter_landing_page()
    {
        $datas =  DataSetting::with('translations')->where('type','flutter_landing_page')->get();
        $data = [];
        foreach ($datas as $key => $value) {
            if(count($value->translations)>0){
                $cred = [
                    $value->key => $value->translations[0]['value'],
                ];
                array_push($data,$cred);
            }else{
                $cred = [
                        $value->key => $value->value,
                    ];
                array_push($data,$cred);
            }
            if (isset($value->storage)) {

                $cred = [
                    $value->key.'_storage' => $value?->storage[0]?->value ?? 'public',
                ];
                array_push($data, $cred);
            }
        }
        $settings = [];
        foreach($data as $single_data){
                foreach($single_data as $key=>$single_value){
                        $settings[$key] = $single_value;
                    }
                }

        $criterias = FlutterSpecialCriteria::where('status',1)->get();

        $awsUrl = config('filesystems.disks.s3.url');
        $awsBucket = config('filesystems.disks.s3.bucket');
        $awsBaseURL = rtrim($awsUrl, '/').'/'.ltrim($awsBucket.'/');

        return  response()->json(
            [
                'base_urls' => [
                    'fixed_header_image' => asset('storage/app/public/fixed_header_image'),
                    'special_criteria_image' => asset('storage/app/public/special_criteria'),
                    'download_user_app_image' => asset('storage/app/public/download_user_app_image'),
                ],

                's3_base_urls' => [
                    'fixed_header_image' => $awsBaseURL.'fixed_header_image',
                    'special_criteria_image' => $awsBaseURL.'special_criteria',
                    'download_user_app_image' => $awsBaseURL.'download_user_app_image',
                ],

                'fixed_header_title'=>(isset($settings['fixed_header_title']) )  ? $settings['fixed_header_title'] : null ,
                'fixed_header_sub_title'=>(isset($settings['fixed_header_sub_title']) )  ? $settings['fixed_header_sub_title'] : null ,
                'fixed_header_image'=>(isset($settings['fixed_header_image']) )  ? $settings['fixed_header_image'] : null ,
                'fixed_header_image_full_url'=>Helpers::get_full_url('fixed_header_image',(isset($settings['fixed_header_image']) )  ? $settings['fixed_header_image'] : null,(isset($settings['fixed_header_image_storage']) )  ? $settings['fixed_header_image_storage'] : 'public') ,
                'fixed_module_title'=>(isset($settings['fixed_module_title']) )  ? $settings['fixed_module_title'] : null ,
                'fixed_module_sub_title'=>(isset($settings['fixed_module_sub_title']) )  ? $settings['fixed_module_sub_title'] : null ,
                'fixed_location_title'=>(isset($settings['fixed_location_title']) )  ? $settings['fixed_location_title'] : null ,
                'join_seller_title'=>(isset($settings['join_seller_title']) )  ? $settings['join_seller_title'] : null ,
                'join_seller_sub_title'=>(isset($settings['join_seller_sub_title']) )  ? $settings['join_seller_sub_title'] : null ,
                'join_seller_button_name'=>(isset($settings['join_seller_button_name']) )  ? $settings['join_seller_button_name'] : null ,
                'join_seller_button_url'=>(isset($settings['join_seller_button_url']) )  ? $settings['join_seller_button_url'] : null ,
                'join_delivery_man_title'=>(isset($settings['join_delivery_man_title']) )  ? $settings['join_delivery_man_title'] : null ,
                'join_delivery_man_sub_title'=>(isset($settings['join_delivery_man_sub_title']) )  ? $settings['join_delivery_man_sub_title'] : null ,
                'join_delivery_man_button_name'=>(isset($settings['join_delivery_man_button_name']) )  ? $settings['join_delivery_man_button_name'] : null ,
                'join_delivery_man_button_url'=>(isset($settings['join_delivery_man_button_url']) )  ? $settings['join_delivery_man_button_url'] : null ,
                'download_user_app_title'=>(isset($settings['download_user_app_title']) )  ? $settings['download_user_app_title'] : null ,
                'download_user_app_sub_title'=>(isset($settings['download_user_app_sub_title']) )  ? $settings['download_user_app_sub_title'] : null ,
                'download_user_app_image'=>(isset($settings['download_user_app_image']) )  ? $settings['download_user_app_image'] : null ,
                'download_user_app_image_full_url'=>Helpers::get_full_url('download_user_app_image',(isset($settings['download_user_app_image']) )  ? $settings['download_user_app_image'] : null,(isset($settings['download_user_app_image_storage']) )  ? $settings['download_user_app_image_storage'] : 'public') ,

                'special_criterias'=>(isset($criterias) )  ? $criterias : null ,



                'download_user_app_links'=> (isset($settings['download_user_app_links']) )  ? json_decode($settings['download_user_app_links'], true) : null ,
        ]);
    }

    private function getPaymentMethods()
    {
        // Check if the addon_settings table exists
        if (!Schema::hasTable('addon_settings')) {
            return [];
        }

        $methods = Setting::where('is_active',1)->where('settings_type', 'payment_config')->get();
        $env = env('APP_ENV') == 'live' ? 'live' : 'test';
        $credentials = $env . '_values';

        $data = [];
        foreach ($methods as $method) {
            $credentialsData = $method->$credentials;
            $additional_data = json_decode($method->additional_data);
            if ($credentialsData['status'] == 1) {
                $data[] = [
                    'gateway' => $method->key_name,
                    'gateway_title' => $additional_data?->gateway_title,
                    'gateway_image' => $additional_data?->gateway_image,
                    'gateway_image_full_url' => Helpers::get_full_url('payment_modules/gateway_image',$additional_data?->gateway_image,$additional_data?->storage ?? 'public')
                ];
            }
        }
        return $data;
    }

    private function getDefaultPaymentMethods()
    {
        // Check if the addon_settings table exists
        if (!Schema::hasTable('addon_settings')) {
            return [];
        }

        $methods = Setting::where('is_active',1)->whereIn('settings_type', ['payment_config'])->whereIn('key_name', ['ssl_commerz','paypal','stripe','razor_pay','senang_pay','paytabs','paystack','paymob_accept','paytm','flutterwave','liqpay','bkash','mercadopago'])->get();

        $env = env('APP_ENV') == 'live' ? 'live' : 'test';
        $credentials = $env . '_values';

        $data = [];
        foreach ($methods as $method) {
            $credentialsData = $method->$credentials;
            $additional_data = json_decode($method->additional_data);
            if ($credentialsData['status'] == 1) {
                $data[] = [
                    'gateway' => $method->key_name,
                    'gateway_title' => $additional_data?->gateway_title,
                    'gateway_image' => $additional_data?->gateway_image,
                    'gateway_image_full_url' => Helpers::get_full_url('payment_modules/gateway_image',$additional_data?->gateway_image,$additional_data?->storage ?? 'public')
                ];
            }
        }
        return $data;
    }

    public function offline_payment_method_list(Request $request)
    {
        $data = OfflinePaymentMethod::where('status', 1)->get();
        $data = $data->count() > 0 ? $data: null;
        return response()->json($data, 200);
    }

}
