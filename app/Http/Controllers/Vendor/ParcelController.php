<?php

namespace App\Http\Controllers\Vendor;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use App\Models\DeliveryMan;
use App\Models\Order;
use App\Models\ParcelDeliveryInstruction;
use App\Models\Translation;
use App\Scopes\ZoneScope;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Config;
use App\Models\DeliveryCompany;
use App\Models\OrderPayment;
use App\CentralLogics\OrderLogic;
use Illuminate\Support\Facades\DB;

class ParcelController extends Controller
{
    public function orders(Request $request,$status)
    {
        if(session()->has('order_filter'))
        {
            $request = json_decode(session('order_filter'));
        }

        $key = isset($request->search)?explode(' ', $request->search):null;
        // $status=$request->status;

        Order::where(['checked' => 0,'order_type'=>'parcel'])->update(['checked' => 1]);
        $orders = Order::with(['customer', 'store'])
        // ->where('zone_id',Helpers::get_store_data()->zone_id)
        ->when(isset($key),function($query)use($key){
            return $query->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('id', 'like', "%{$value}%")
                    ->orWhere('order_status', 'like', "%{$value}%")
                    ->orWhere('transaction_reference', 'like', "%{$value}%");
                }
            });
        })
        ->when(isset($request->zone), function($query)use($request){
            return $query->where('zone_id',$request->zone);
        })
        // ->when($status == 'scheduled', function($query){
        //     return $query->whereRaw('created_at <> schedule_at');
        // })
        // ->when($status == 'searching_for_deliverymen', function($query){
        //     return $query->SearchingForDeliveryman();
        // })
        ->when($status == 'pending', function($query){
            return $query->Pending();
        })
        ->when($status == 'accepted', function($query){
            return $query->where('parcel_company_id',Helpers::get_store_id());
        })
        ->when($status == 'item_on_the_way', function($query){
            return $query->where('parcel_company_id',Helpers::get_store_id())->ItemOnTheWay();
        })
        ->when($status == 'delivered', function($query){
            return $query->where('parcel_company_id',Helpers::get_store_id())->Delivered();
        })
        ->when($status == 'canceled', function($query){
            return $query->where('parcel_company_id',Helpers::get_store_id())->Canceled();
        })
        ->when($status == 'failed', function($query){
            return $query->where('parcel_company_id',Helpers::get_store_id())->failed();
        })
        ->when($status == 'processing', function($query){
            return $query->where('parcel_company_id',Helpers::get_store_id())->Preparing();
        })
        ->when($status == 'on_going', function($query){
            return $query->where('parcel_company_id',Helpers::get_store_id())->Ongoing();
        })
        ->when($status == 'all', function($query){
            return $query->where('parcel_company_id',Helpers::get_store_id())
            ->orWhere(function($query){
                return $query->where('order_status','pending');
            })
            ->orWhere(function($query){
                return $query->where('order_status','confirmed')->whereNull('parcel_company_id');
            })
            ->orWhere(function($query){
                return $query->where('order_status','confirmed')->whereNotNull('parcel_company_id')->where('parcel_company_id',Helpers::get_store_id());
            });
        })
        // ->when(isset($request->orderStatus) && $status == 'all', function($query)use($request){
        //     return $query->whereIn('order_status',$request->orderStatus);
        // })
        // ->when(isset($request->scheduled) && $status == 'all', function($query){
        //     return $query->scheduled();
        // })
        // ->when(isset($request->order_type), function($query)use($request){
        //     return $query->where('order_type', $request->order_type);
        // })
        ->when(isset($request->from_date)&&isset($request->to_date)&&$request->from_date!=null&&$request->to_date!=null, function($query)use($request){
            return $query->whereBetween('created_at', [$request->from_date." 00:00:00",$request->to_date." 23:59:59"]);
        })
        ->ParcelOrder()
        ->where('zone_id',Helpers::get_store_data()->zone_id)
        ->orderBy('schedule_at', 'desc')
        ->paginate(config('default_pagination'));
        $orderstatus = isset($request->orderStatus)?$request->orderStatus:[];
        $scheduled =isset($request->scheduled)?$request->scheduled:0;
        $vendor_ids =isset($request->vendor)?$request->vendor:[];
        $zone_ids =isset($request->zone)?$request->zone:[];
        $from_date =isset($request->from_date)?$request->from_date:null;
        $to_date =isset($request->to_date)?$request->to_date:null;
        $order_type =isset($request->order_type)?$request->order_type:null;
        $total = $orders->total();
        $parcel_route = 1;

        return view('vendor-views.order.list', compact('orders', 'status', 'orderstatus', 'scheduled', 'vendor_ids', 'zone_ids', 'from_date', 'to_date', 'total', 'order_type','parcel_route'));
    }

    public function order_details(Request $request, $id)
    {
        $order = Order::with(['parcel_company','customer'=>function($query){
            return $query->withCount('orders');
        },'delivery_man'=>function($query){
            return $query->withCount('orders');
        }])->where(['id' => $id])->ParcelOrder()->where('zone_id',Helpers::get_store_data()->zone_id)->first();
        if(isset($order) && $order?->parcel_company && $order?->parcel_company?->id != Helpers::get_store_id()){
            Toastr::info(translate('messages.no_more_orders'));
            return redirect()->to(route('vendor.parcel.orders',['all']));
        }
        if (isset($order)) {
            $deliveryMen = DeliveryMan::where('type','company_wise')->where('zone_id', Helpers::get_store_data()->zone_id)
                ->where(function($query)use($order){
                            $query->where('vehicle_id',$order->dm_vehicle_id);
                    })->available()->active()->get();
            $category = $request->query('category_id', 0);
            $categories = [];
            $products = [];
            $editing=false;
            $deliveryMen=Helpers::deliverymen_list_formatting($deliveryMen);
            $keyword = null;
            return view('vendor-views.order.order-view', compact('order', 'deliveryMen','categories', 'products','category', 'keyword', 'editing'));
        } else {
            Toastr::info(translate('messages.no_more_orders'));
            return back();
        }
    }

    public function settings()
    {
        $instructions = ParcelDeliveryInstruction::orderBy('id', 'desc')
            ->paginate(config('default_pagination'));
        return view('admin-views.parcel.settings', compact('instructions'));
    }

    public function update_settings(Request $request)
    {
        $request->validate([
            'parcel_per_km_shipping_charge'=>'required|numeric|min:0',
            'parcel_minimum_shipping_charge'=>'required|numeric|min:0',
            'parcel_commission_dm'=>'required|numeric|min:0',
        ],[
            'parcel_commission_dm.required'=>translate('validation.required',['attribute'=>translate('messages.deliveryman_commission')]),
            'parcel_commission_dm.numeric'=>translate('validation.numeric',['attribute'=>translate('messages.deliveryman_commission')]),
            'parcel_commission_dm.min'=>translate('validation.min',['attribute'=>translate('messages.deliveryman_commission')]),

            'parcel_per_km_shipping_charge.required'=>translate('validation.required',['attribute'=>translate('messages.per_km_shipping_charge')]),
            'parcel_per_km_shipping_charge.numeric'=>translate('validation.numeric',['attribute'=>translate('messages.per_km_shipping_charge')]),
            'parcel_per_km_shipping_charge.min'=>translate('validation.min',['attribute'=>translate('messages.per_km_shipping_charge')]),

            'parcel_minimum_shipping_charge.required'=>translate('validation.required',['attribute'=>translate('messages.minimum_shipping_charge')]),
            'parcel_minimum_shipping_charge.numeric'=>translate('validation.numeric',['attribute'=>translate('messages.minimum_shipping_charge')]),
            'parcel_minimum_shipping_charge.min'=>translate('validation.min',['attribute'=>translate('messages.minimum_shipping_charge')]),
        ]);
        BusinessSetting::updateOrinsert(['key'=>'parcel_per_km_shipping_charge'],['value'=>$request->parcel_per_km_shipping_charge]);
        BusinessSetting::updateOrinsert(['key'=>'parcel_minimum_shipping_charge'],['value'=>$request->parcel_minimum_shipping_charge]);
        BusinessSetting::updateOrinsert(['key'=>'parcel_commission_dm'],['value'=>$request->parcel_commission_dm]);

        Toastr::success(translate('messages.parcel_settings_updated'));
        return back();
    }

    public function dispatch_list($status, Request $request)
    {
        $module_id = $request->query('module_id', null);

        if (session()->has('order_filter')) {
            $request = json_decode(session('order_filter'));
            $zone_ids = isset($request->zone) ? $request->zone : 0;
        }

        Order::where(['checked' => 0])->update(['checked' => 1]);

        $orders = Order::with(['customer', 'store'])
            ->when(isset($module_id), function ($query) use ($module_id) {
                return $query->module($module_id);
            })
            ->when(isset($request->zone), function ($query) use ($request) {
                return $query->whereHas('store', function ($query) use ($request) {
                    return $query->whereIn('zone_id', $request->zone);
                });
            })
            ->when($status == 'searching_for_deliverymen', function ($query) {
                return $query->SearchingForDeliveryman();
            })
            ->when($status == 'on_going', function ($query) {
                return $query->Ongoing();
            })
            ->when(isset($request->vendor), function ($query) use ($request) {
                return $query->whereHas('store', function ($query) use ($request) {
                    return $query->whereIn('id', $request->vendor);
                });
            })
            ->when(isset($request->from_date) && isset($request->to_date) && $request->from_date != null && $request->to_date != null, function ($query) use ($request) {
                return $query->whereBetween('created_at', [$request->from_date . " 00:00:00", $request->to_date . " 23:59:59"]);
            })
            ->ParcelOrder()
            ->module(Config::get('module.current_module_id'))
            ->OrderScheduledIn(30)
            ->orderBy('schedule_at', 'desc')
            ->paginate(config('default_pagination'));

        $orderstatus = isset($request->orderStatus) ? $request->orderStatus : [];
        $scheduled = isset($request->scheduled) ? $request->scheduled : 0;
        $vendor_ids = isset($request->vendor) ? $request->vendor : [];
        $zone_ids = isset($request->zone) ? $request->zone : [];
        $from_date = isset($request->from_date) ? $request->from_date : null;
        $to_date = isset($request->to_date) ? $request->to_date : null;
        $total = $orders->total();

        return view('admin-views.order.distaptch_list', compact('orders', 'status', 'orderstatus', 'scheduled', 'vendor_ids', 'zone_ids', 'from_date', 'to_date', 'total'));
    }
    public function parcel_dispatch_list($module,$status, Request $request)
    {
        $module_id = $request->query('module_id', null);

        if (session()->has('order_filter')) {
            $request = json_decode(session('order_filter'));
            $zone_ids = isset($request->zone) ? $request->zone : 0;
        }

        Order::where(['checked' => 0])->update(['checked' => 1]);

        $orders = Order::with(['customer', 'store'])
            ->whereHas('module', function($query) use($module){
                $query->where('id', $module);
            })
            ->when(isset($module_id), function ($query) use ($module_id) {
                return $query->module($module_id);
            })
            ->when(isset($request->zone), function ($query) use ($request) {
                return $query->whereHas('store', function ($query) use ($request) {
                    return $query->whereIn('zone_id', $request->zone);
                });
            })
            ->when($status == 'searching_for_deliverymen', function ($query) {
                return $query->SearchingForDeliveryman();
            })
            ->when($status == 'on_going', function ($query) {
                return $query->Ongoing();
            })
            ->when(isset($request->vendor), function ($query) use ($request) {
                return $query->whereHas('store', function ($query) use ($request) {
                    return $query->whereIn('id', $request->vendor);
                });
            })
            ->when(isset($request->from_date) && isset($request->to_date) && $request->from_date != null && $request->to_date != null, function ($query) use ($request) {
                return $query->whereBetween('created_at', [$request->from_date . " 00:00:00", $request->to_date . " 23:59:59"]);
            })
            ->ParcelOrder()
            ->OrderScheduledIn(30)
            ->orderBy('schedule_at', 'desc')
            ->paginate(config('default_pagination'));

        $orderstatus = isset($request->orderStatus) ? $request->orderStatus : [];
        $scheduled = isset($request->scheduled) ? $request->scheduled : 0;
        $vendor_ids = isset($request->vendor) ? $request->vendor : [];
        $zone_ids = isset($request->zone) ? $request->zone : [];
        $from_date = isset($request->from_date) ? $request->from_date : null;
        $to_date = isset($request->to_date) ? $request->to_date : null;
        $total = $orders->total();

        return view('admin-views.order.distaptch_list', compact('orders','module', 'status', 'orderstatus', 'scheduled', 'vendor_ids', 'zone_ids', 'from_date', 'to_date', 'total'));
    }

    public function instruction(Request $request)
    {
        $request->validate([
            'instruction' => 'required|max:191',
            'instruction.0' => 'required',
        ],[
            'instruction.0.required'=>translate('default_instruction_is_required'),
        ]);

        $instruction = new ParcelDeliveryInstruction();
        $instruction->instruction = $request->instruction[array_search('default', $request->lang)];
        $instruction->save();
        $data = [];
        $default_lang = str_replace('_', '-', app()->getLocale());
        foreach ($request->lang as $index => $key) {
            if($default_lang == $key && !($request->instruction[$index])){
                if ($key != 'default') {
                    array_push($data, array(
                        'translationable_type' => 'App\Models\ParcelDeliveryInstruction',
                        'translationable_id' => $instruction->id,
                        'locale' => $key,
                        'key' => 'instruction',
                        'value' => $instruction->instruction,
                    ));
                }
            }else{
                if ($request->instruction[$index] && $key != 'default') {
                    array_push($data, array(
                        'translationable_type' => 'App\Models\ParcelDeliveryInstruction',
                        'translationable_id' => $instruction->id,
                        'locale' => $key,
                        'key' => 'instruction',
                        'value' => $request->instruction[$index],
                    ));
                }
            }
        }
        Translation::insert($data);
        Toastr::success(translate('Delivery Instruction Added Successfully'));
        return back();
    }
    public function instruction_edit(Request $request)
    {
        $request->validate([
            'instruction' => 'required|max:191',
            'instruction.0' => 'required',
        ],[
            'instruction.0.required'=>translate('default_instruction_is_required'),
        ]);
        $instruction = ParcelDeliveryInstruction::findOrFail($request->instruction_id);
        $instruction->instruction = $request->instruction[array_search('default', $request->lang1)];
        $instruction->save();

        $default_lang = str_replace('_', '-', app()->getLocale());
        foreach ($request->lang1 as $index => $key) {
            if($default_lang == $key && !($request->instruction[$index])){
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\ParcelDeliveryInstruction',
                            'translationable_id' => $instruction->id,
                            'locale' => $key,
                            'key' => 'instruction'
                        ],
                        ['value' => $instruction->instruction]
                    );
                }
            }else{
                if ($request->instruction[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\ParcelDeliveryInstruction',
                            'translationable_id' => $instruction->id,
                            'locale' => $key,
                            'key' => 'instruction'
                        ],
                        ['value' => $request->instruction[$index]]
                    );
                }
            }
        }


        Toastr::success(translate('Delivery Instruction Updated Successfully'));
        return back();
    }
    public function instruction_delete(Request $request)
    {
        $instruction = ParcelDeliveryInstruction::findOrFail($request->id);
        $instruction?->translations()?->delete();
        $instruction->delete();
        Toastr::success(translate('Delivery Instruction Deleted Successfully'));
        return back();
    }
    public function instruction_status(Request $request)
    {
        $instruction = ParcelDeliveryInstruction::findOrFail($request->id);
        $instruction->status = $request->status;
        $instruction->save();
        Toastr::success(translate('messages.status_updated'));
        return back();
    }
    public function third_party_company(Request $request){

        $request->validate([
        "order_id"=>"required",
        "company_name"=>"required",
        "tracking_url"=>"required|url",
        "serial_number"=>"required",

        ]);

        $delivery_company = DeliveryCompany::updateOrCreate(
            ['order_id' => $request->order_id],
            [
            'company_name' => $request->company_name,
            'tracking_url' => $request->tracking_url,
            'serial_number' => $request->serial_number
            ]
        );
        $order=Order::find($request->order_id);
        $order->third_party=1;
        $order->company_id=$delivery_company->id;
        $order->save();

        if ($delivery_company->wasRecentlyCreated) {
            Toastr::success(translate('Assign_to_third_party_company'));
        }
        else
        {
            Toastr::success(translate('Update_to_third_party_company'));
        }

        return back();
    }
    public function status(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'id' => 'required',
            'order_status' => 'required|in:confirmed,processing,handover,delivered,canceled,picked_up',
            'reason' =>'required_if:order_status,canceled',
        ],[
            'id.required' => 'Order id is required!'
        ]);
        $order = Order::where(['id' => $request->id])->first();
        if($order->confirmed && $order->parcel_company_id != Helpers::get_store_id()){
            Toastr::warning(translate('messages.already_comfirmed_by_another_company'));
            return redirect()->route('vendor.parcel.orders', ['all']);

        }
        if($request['order_status'] !='confirmed' && !$order->confirmed){
            Toastr::warning(translate('messages.to_update_order_confirmed_first'));
            return back();
        }
        if($order->delivered != null)
        {
            Toastr::warning(translate('messages.cannot_change_status_after_delivered'));
            return back();
        }

        if($request['order_status']=='canceled' && !config('canceled_by_store'))
        {
            Toastr::warning(translate('messages.you_can_not_cancel_a_order'));
            return back();
        }

        if($request['order_status']=='canceled' && $order->confirmed)
        {
            Toastr::warning(translate('messages.you_can_not_cancel_after_confirm'));
            return back();
        }



        if($request['order_status']=='delivered' && $order->order_type != 'take_away' && !Helpers::get_store_data()->self_parcel_delivery)
        {
            Toastr::warning(translate('messages.you_can_not_delivered_delivery_order'));
            return back();
        }

        // if($request['order_status'] =="confirmed")
        // {
        //     if(!Helpers::get_store_data()->self_delivery_system && config('order_confirmation_model') == 'deliveryman' && $order->order_type != 'take_away')
        //     {
        //         Toastr::warning(translate('messages.order_confirmation_warning'));
        //         return back();
        //     }
        // }

        if ($request->order_status == 'delivered') {

            if ($order->transaction  == null) {
                $unpaid_payment = OrderPayment::where('payment_status','unpaid')->where('order_id',$order->id)->first()?->payment_method;
                $unpaid_pay_method = 'digital_payment';
                if($unpaid_payment){
                    $unpaid_pay_method = $unpaid_payment;
                }
                $ol = OrderLogic::create_transaction($order, 'company', null);
                if (!$ol) {
                    Toastr::warning(translate('messages.faield_to_create_order_transaction'));
                    return back();
                }
            }

            $order->payment_status = 'paid';
            $order->details->each(function ($item, $key) {
                if ($item->item) {
                    $item->item->increment('order_count');
                }
            });
            $order?->customer?->increment('order_count');
            if ($order->store) {
                $order->store->increment('order_count');
            }
            if ($order->parcel_category) {
                $order->parcel_category->increment('orders_count');
            }

            OrderLogic::update_unpaid_order_payment(order_id:$order->id, payment_method:$order->payment_method);

        }
        if($request->order_status == 'canceled' || $request->order_status == 'delivered')
        {
            if($order->delivery_man)
            {
                $dm = $order->delivery_man;
                $dm->current_orders = $dm->current_orders>1?$dm->current_orders-1:0;
                $dm->save();
            }
            if($request->order_status == 'canceled'){

                $order->cancellation_reason = $request->reason;
                $order->canceled_by = 'store';
            }

        }

        if($request->order_status == 'delivered')
        {
            // $order->store->increment('order_count');
            if($order->delivery_man)
            {
                $order->delivery_man->increment('order_count');
            }

        }

        $order->order_status = $request->order_status;
        if($request->order_status == 'processing') {
            $order->processing_time = ($request?->processing_time) ? $request->processing_time : explode('-', $order['store']['delivery_time'])[0];
        }
        $order[$request['order_status']] = now();
        if($request['order_status'] =='confirmed'){
            $order->store_id = Helpers::get_store_id();
            $order->parcel_company_id = Helpers::get_store_id();

        }
       $order->save();
        if(!Helpers::send_order_notification($order))
        {
            Toastr::warning(translate('messages.push_notification_faild'));
        }

        Toastr::success(translate('messages.order_status_updated'));
        return back();
    }

    public function generate_invoice($id)
    {
        $order = Order::where(['id' => $id, 'parcel_company_id' => Helpers::get_store_id()])->first();
        if(!isset($order)){

        Toastr::warning(translate('messages.to_print_invoice_confirmed_the_order_first'));
        return back();
        }
        return view('vendor-views.order.invoice', compact('order'));
    }

    public function print_invoice($id)
    {
        $order = Order::where(['id' => $id, 'parcel_company_id' => Helpers::get_store_id()])->first();
        if(!isset($order)){
        Toastr::warning(translate('messages.to_print_invoice_confirmed_the_order_first'));
        return back();
        }
        return view('admin-views.order.invoice-print', compact('order'))->render();
    }
    public function add_delivery_man($order_id, $delivery_man_id)
    {
        if ($delivery_man_id == 0) {
            return response()->json(['message'=> translate('messages.deliveryman_not_found')  ], 400);
        }
        $order = Order::find($order_id);

        $deliveryman = DeliveryMan::where('id', $delivery_man_id)->available()->active()->first();
        if ($order->delivery_man_id == $delivery_man_id) {
            return response()->json(['message'=> translate('messages.order_already_assign_to_this_deliveryman')  ], 400);
        }
        if ($deliveryman) {
            if ($deliveryman->current_orders >= config('dm_maximum_orders')) {
                return response()->json(['message'=> translate('messages.dm_maximum_order_exceed_warning')  ], 400);
            }

            $payments = $order->payments()->where('payment_method','cash_on_delivery')->exists();
            $cash_in_hand = $deliveryman?->wallet?->collected_cash ?? 0;
            $dm_max_cash=BusinessSetting::where('key','dm_max_cash_in_hand')->first();
            $value=  $dm_max_cash?->value ?? 0;

            if(($order->payment_method == "cash_on_delivery" || $payments) && (($cash_in_hand+$order->order_amount) >= $value)){
                return response()->json(['message'=> \App\CentralLogics\Helpers::format_currency($value) ." ".translate('max_cash_in_hand_exceeds')  ], 400);
            }

            if ($order->delivery_man) {
                $dm = $order->delivery_man;
                $dm->current_orders = $dm->current_orders > 1 ? $dm->current_orders - 1 : 0;
                $dm->save();

                $data = [
                    'title' => translate('messages.order_push_title'),
                    'description' => translate('messages.you_are_unassigned_from_a_order'),
                    'order_id' => '',
                    'image' => '',
                    'type' => 'assign'
                ];
                Helpers::send_push_notif_to_device($dm->fcm_token, $data);

                DB::table('user_notifications')->insert([
                    'data' => json_encode($data),
                    'delivery_man_id' => $dm->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            $order->delivery_man_id = $delivery_man_id;
            // $order->order_status = in_array($order->order_status, ['pending', 'confirmed']) ? 'accepted' : $order->order_status;
            // $order->accepted = now();
            // $order->store_id=Helpers::get_store_id();
            $order->save();

            $deliveryman->current_orders = $deliveryman->current_orders + 1;
            $deliveryman->save();
            $deliveryman->increment('assigned_order_count');

            $fcm_token= $order->is_guest == 0 ? $order?->customer?->cm_firebase_token : $order?->guest?->fcm_token;
            $value = Helpers::order_status_update_message('accepted',$order->module->module_type,$order->customer?
            $order?->customer?->current_language_key:'en');
            $value = Helpers::text_variable_data_format(value:$value,store_name:$order->store?->name,order_id:$order->id,user_name:"{$order?->customer?->f_name} {$order?->customer?->l_name}",delivery_man_name:"{$order->delivery_man?->f_name} {$order->delivery_man?->l_name}");
            try {
                if ($value) {
                    $data = [
                        'title' => translate('messages.order_push_title'),
                        'description' => $value,
                        'order_id' => $order['id'],
                        'image' => '',
                        'type' => 'order_status'
                    ];

                    if($fcm_token){
                        Helpers::send_push_notif_to_device($fcm_token, $data);
                        DB::table('user_notifications')->insert([
                            'data' => json_encode($data),
                            'user_id' => $order?->customer?->id ,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }
                $data = [
                    'title' => translate('messages.order_push_title'),
                    'description' => translate('messages.you_are_assigned_to_a_order'),
                    'order_id' => $order['id'],
                    'image' => '',
                    'type' => 'assign'
                ];
                Helpers::send_push_notif_to_device($deliveryman->fcm_token, $data);
                DB::table('user_notifications')->insert([
                    'data' => json_encode($data),
                    'delivery_man_id' => $deliveryman->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } catch (\Exception $e) {
                info($e->getMessage());
                Toastr::warning(translate('messages.push_notification_faild'));
            }
            return response()->json([], 200);
        }
        return response()->json(['message' => 'Deliveryman not available!'], 400);
    }
}
