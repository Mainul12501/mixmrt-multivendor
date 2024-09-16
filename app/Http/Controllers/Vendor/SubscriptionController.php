<?php

namespace App\Http\Controllers\Vendor;

use App\Models\Store;
use App\Models\StoreWallet;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use Illuminate\Support\Carbon;
use App\Models\BusinessSetting;
use App\Mail\SubscriptionCancel;
use App\Models\StoreSubscription;
use Illuminate\Support\Facades\DB;
use App\Models\SubscriptionPackage;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Schema;
use App\Models\SubscriptionTransaction;
use Illuminate\Support\Facades\Session;
use App\Exports\SubscriptionTransactionsExport;
use App\Models\SubscriptionBillingAndRefundHistory;
use Illuminate\Support\Facades\View;

class SubscriptionController extends Controller
{
    public function subscriberDetail(){
        $store= Store::where('id',Helpers::get_store_id())->with([
            'store_sub_update_application.package','vendor','store_sub_update_application.last_transcations'
        ])->withcount(['items','store_all_sub_trans'])
        ->first();
        $packages = SubscriptionPackage::where('status',1)->latest()->get();
        $admin_commission=BusinessSetting::where('key', 'admin_commission')->first()?->value ;
        $business_name=BusinessSetting::where('key', 'business_name')->first()?->value ;
        try {
            $index=  $store->store_business_model == 'commission' ? 0 : 1+ array_search($store?->store_sub_update_application?->package_id??1 ,array_column($packages->toArray() ,'id') );
        } catch (\Throwable $th) {
            $index= 2;
        }
        return view('vendor-views.subscription.subscriber.vendor-subscription',compact('store','packages','business_name','admin_commission','index'));
    }

    public function cancelSubscription(Request $request, $id){
    StoreSubscription::where(['store_id' => Helpers::get_store_id(), 'id'=>$request->subscription_id])->update([
            'is_canceled' => 1,
            'canceled_by' => 'store',
        ]);

        try {
            $store=Store::where('id',Helpers::get_store_id())->select(['id','name','email'])->first();
            if (config('mail.status') && Helpers::get_mail_status('subscription_cancel_mail_status_store') == '1') {
                Mail::to($store->email)->send(new SubscriptionCancel($store->name));
            }
        } catch (\Exception $ex) {
            info($ex->getMessage());
        }

        return response()->json(200);

    }
    public function switchToCommission($id){

        StoreSubscription::where(['store_id' => Helpers::get_store_id()])->update([
            'status' => 0,
        ]);
        Store::where('id',Helpers::get_store_id())->update([
            'store_business_model' => 'commission',
        ]);
        return response()->json(200);

    }
    public function packageView($id,$store_id){
        $store_subscription= StoreSubscription::where('store_id', $store_id)->with(['package'])->latest()->first();
        $package = SubscriptionPackage::where('status',1)->where('id',$id)->first();

        $store= Store::Where('id',$store_id)->first();
        $pending_bill= SubscriptionBillingAndRefundHistory::where(['store_id'=>$store->id,
        'transaction_type'=>'pending_bill', 'is_success' =>0])?->sum('amount') ?? 0;

        $balance = BusinessSetting::where('key', 'wallet_status')->first()?->value == 1 ? StoreWallet::where('vendor_id',$store->vendor_id)->first()?->balance ?? 0 : 0;
        $payment_methods = Helpers::getDefaultPaymentMethods();
        $disable_item_count=null;
        if(data_get(Helpers::subscriptionConditionsCheck(store_id:$store->id,package_id:$package->id) , 'disable_item_count') > 0 && ( !$store_subscription || $package->id != $store_subscription->package_id)){
            $disable_item_count=data_get(Helpers::subscriptionConditionsCheck(store_id:$store->id,package_id:$package->id) , 'disable_item_count');
        }
        $store_business_model=$store->store_business_model;
        $admin_commission=BusinessSetting::where('key', "admin_commission")->first()?->value ?? 0 ;

        $cash_backs=[];
        if($store->store_business_model == 'subscription' &&  $store_subscription->status == 1 && $store_subscription->is_canceled == 0 && $store_subscription->is_trial == 0  && $store_subscription->package_id !=  $package->id){
            $cash_backs= Helpers::calculateSubscriptionRefundAmount(store:$store, return_data:true);
        }

        return response()->json([
            'disable_item_count'=> $disable_item_count,
            'view' => view('vendor-views.subscription.subscriber.partials._package_selected', compact('store_subscription','package','store_id','balance','payment_methods','pending_bill','store_business_model','admin_commission','cash_backs'))->render()
        ]);

    }
    public function packageBuy(Request $request){


        $request->validate([
            'package_id' => 'required',
            'store_id' => 'required',
            'payment_gateway' => 'required'
        ]);
        $store= Store::Where('id',$request->store_id)->first(['id','vendor_id']);
        $package = SubscriptionPackage::withoutGlobalScope('translate')->find($request->package_id);
        $pending_bill= SubscriptionBillingAndRefundHistory::where(['store_id'=>$store->id,
        'transaction_type'=>'pending_bill', 'is_success' =>0])?->sum('amount') ?? 0;

        if(!in_array($request->payment_gateway,['wallet'])){
            $url= route('vendor.subscriptionackage.subscriberDetail',$store->id);
            return redirect()->away(Helpers::subscriptionPayment(store_id:$store->id,package_id:$package->id,payment_gateway:$request->payment_gateway,payment_platform:'web',url:$url,pending_bill:$pending_bill,type: $request?->type));
        }

        if($request->payment_gateway == 'wallet'){
        $wallet= StoreWallet::firstOrNew(['vendor_id'=> $store->vendor_id]);
        $balance = BusinessSetting::where('key', 'wallet_status')->first()?->value == 1 ? $wallet?->balance ?? 0 : 0;

            if($balance >= ($package?->price + $pending_bill)){
                $reference= 'wallet_payment_by_vendor';
                $plan_data=   Helpers::subscription_plan_chosen(store_id:$store->id,package_id:$package->id,payment_method:$reference,discount:0,pending_bill:$pending_bill,reference:$reference,type: $request?->type);
                if($plan_data != false){
                    $wallet->total_withdrawn= $wallet?->total_withdrawn + $package->price +$pending_bill;
                    $wallet?->save();
                }
            }
            else{
                Toastr::error( translate('messages.Insufficient_balance_in_wallet'));
                return to_route('vendor.subscriptionackage.subscriberDetail',$store->id);

            }
        }

        $plan_data != false ?  Toastr::success( translate('Successfully_Subscribed.')) : Toastr::error( translate('Something_went_wrong!.'));
        return to_route('vendor.subscriptionackage.subscriberDetail',$store->id);

    }



    public function subscriberTransactions($id,Request $request){
        $filter= $request['filter'];
        $plan_type= $request['plan_type'];
        $from =$request['start_date'] ?? Carbon::now()->format('Y-m-d');
        $to =$request['end_date'] ?? Carbon::now()->format('Y-m-d');
        $store= Store::where('id',Helpers::get_store_id())->with([
            'store_sub_update_application.package'
        ])
        ->first();

        $key = explode(' ', $request['search']);
        $transactions= SubscriptionTransaction::where('store_id',Helpers::get_store_id())
        ->when(isset($key), function($query) use($key){
            $query->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->Where('id', 'like', "%{$value}%");
                }
            });
        })
        ->when($filter == 'this_year' , function($query){
            $query->whereYear('created_at', Carbon::now()->year );
        })
        ->when($filter == 'this_month' , function($query){
            $query->whereMonth('created_at', Carbon::now()->month );
        })
        ->when($filter == 'this_week' , function($query){
            $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()] );
        })
        ->when($filter == 'custom' , function($query) use($from,$to) {
            $query->whereBetween('created_at', [$from . " 00:00:00", $to . " 23:59:59"]);
        })

        ->when( in_array( $plan_type,['renew','new_plan','first_purchased','free_trial'])  , function($query) use($plan_type){
            $query->where('plan_type', $plan_type );
        })

        ->latest()->paginate(config('default_pagination'));
            $subscription_deadline_warning_days = BusinessSetting::where('key','subscription_deadline_warning_days')->first()?->value ?? 7;
        return view('vendor-views.subscription.subscriber.transaction',compact('store','transactions','id','filter','subscription_deadline_warning_days'));

    }
    public function invoice($id){
        $BusinessData= ['admin_commission' ,'business_name','address','phone','logo','email_address'];
        $transaction= SubscriptionTransaction::with(['store.vendor','package:id,package_name,price'])->find($id);
        $BusinessData=BusinessSetting::whereIn('key', $BusinessData)->pluck('value' ,'key') ;
        $logo=BusinessSetting::where('key', "logo")->first() ;

        $mpdf_view = View::make('subscription-invoice', compact('transaction','BusinessData','logo'));
        Helpers::gen_mpdf(view: $mpdf_view,file_prefix: 'Subscription',file_postfix: $id);
        return back();
    }

    public function subscriberTransactionExport(Request $request){


        $filter= $request['filter'];
        $plan_type= $request['plan_type'];
        $from =$request['start_date'] ?? Carbon::now()->format('Y-m-d');
        $to =$request['end_date'] ?? Carbon::now()->format('Y-m-d');
        $store= Store::where('id',Helpers::get_store_id())->first();

        $key = explode(' ', $request['search']);
        $transactions= SubscriptionTransaction::where('store_id',$store->id)
        ->when(isset($key), function($query) use($key){
            $query->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->Where('id', 'like', "%{$value}%");
                }
            });
        })
        ->when($filter == 'this_year' , function($query){
            $query->whereYear('created_at', Carbon::now()->year );
        })
        ->when($filter == 'this_month' , function($query){
            $query->whereMonth('created_at', Carbon::now()->month );
        })
        ->when($filter == 'this_week' , function($query){
            $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()] );
        })
        ->when($filter == 'custom' , function($query) use($from,$to) {
            $query->whereBetween('created_at', [$from . " 00:00:00", $to . " 23:59:59"]);
        })

        ->when( in_array( $plan_type,['renew','new_plan','first_purchased','free_trial'])  , function($query) use($plan_type){
            $query->where('plan_type', $plan_type );
        })

        ->latest()->get();

        $data = [
            'data'=>$transactions,
            'plan_type'=>$request['plan_type'] ?? 'all',
            'filter'=>$request['filter'] ?? 'all',
            'search'=>$request['search'],
            'start_date'=>$request['start_date'],
            'end_date'=>$request['end_date'],
            'store'=>$store->name,
        ];
        if ($request->export_type == 'excel') {
            return Excel::download(new SubscriptionTransactionsExport($data), 'SubscriptionTransactionsExport.xlsx');
        }
        return Excel::download(new SubscriptionTransactionsExport($data), 'SubscriptionTransactionsExport.csv');
    }

    public function addToSession(Request $request)
    {
        Session::put($request->value, true);
        return response()->json(['success' => true]);
    }

    public function subscriberWalletTransactions(Request $request){
        $store= Store::where('id',Helpers::get_store_id())->first();
        $transactions= SubscriptionBillingAndRefundHistory::where('store_id', $store->id)->with('package')
        ->where('transaction_type','refund')
        ->latest()->paginate(config('default_pagination'));

        return view('vendor-views.subscription.subscriber.wallet-transaction',compact('transactions','store'));

    }
}
