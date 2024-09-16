<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\OfflinePaymentMethod;
use App\Models\OfflinePayments;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class OfflinePaymentMethodController extends Controller
{

    protected OfflinePaymentMethod $OfflinePaymentMethod;

    public function __construct(OfflinePaymentMethod $OfflinePaymentMethod)
    {
        $this->OfflinePaymentMethod = $OfflinePaymentMethod;
    }

    public function index(Request $request)
    {
        if (request()->has('status') && (request('status') == 'active' || request('status') == 'inactive')) {
            $methods = OfflinePaymentMethod::when(request('status') == 'active', function ($query) {
                return $query->where('status', 1);
            })->when(request('status') == 'inactive', function ($query) {
                return $query->where('status', 0);
            })->paginate(10);
        } else if (request()->has('search')) {
            $methods = OfflinePaymentMethod::where(function ($query) {
                $query->orWhere('method_name', 'like', "%" . request('search') . "%");
            })->paginate(10);
        } else {
            $methods = OfflinePaymentMethod::paginate(10);
        }

        return view('admin-views.business-settings.offline-payment.index', compact('methods'));
    }


    public function create()
    {
        return view('admin-views.business-settings.offline-payment.new');
    }


    public function store(Request $request)
    {
        $request->validate([
            'method_name' => 'required|unique:offline_payment_methods',
            'input_name' => 'required|array',
            'input_data' => 'required|array',
            'customer_input' => 'required|array',
        ], [
            'input_name.required' => translate('Payment_information_details_required'),
            'input_data.required' => translate('Payment_information_details_required'),
            'customer_input.required' => translate('Customer_input_information_required')
        ]);

        $method_fields = [];
        if ($request->has('input_name')) {
            foreach ($request->input_name as $key => $field_name) {
                $method_fields[] = [
                    'input_name' => strtolower(str_replace("'", '', preg_replace('/[^a-zA-Z0-9\']/', '_', $request->input_name[$key]))),
                    'input_data' => $request->input_data[$key],
                ];
            }
        }

        $method_informations = [];
        if ($request->has('customer_input')) {
            foreach ($request->customer_input as $key => $field_name) {
                $method_informations[] = [
                    'customer_input' => strtolower(str_replace("'", '', preg_replace('/[^a-zA-Z0-9\']/', '_', $request->customer_input[$key]))),
                    'customer_placeholder' => $request->customer_placeholder[$key],
                    'is_required' => isset($request['is_required']) && isset($request['is_required'][$key]) ? 1 : 0,
                ];
            }
        }

        $this->OfflinePaymentMethod->insert([
            'method_name' => $request->method_name,
            'method_fields' => json_encode($method_fields),
            'method_informations' => json_encode($method_informations),
            'status' => 1,
            'created_at' => Carbon::now(),
        ]);

        Toastr::success(translate('offline_payment_method_added_successfully'));
        return to_route('admin.business-settings.offline');
    }


    public function edit($id)
    {
        $data = $this->OfflinePaymentMethod->where('id', $id)->first();

        if ($data) {
            return view('admin-views.business-settings.offline-payment.edit', compact('data'));
        } else {
            Toastr::error(translate('offline_payment_method_not_found'));
            return to_route('admin.business-settings.offline');
        }
    }


    public function update(Request $request)
    {
        $request->validate([
            'method_name' => 'required|unique:offline_payment_methods,method_name,' . $request->id,
            'input_name' => 'required|array',
            'input_data' => 'required|array',
            'customer_input' => 'required|array',
        ], [
            'input_name.required' => translate('Payment_information_details_required'),
            'input_data.required' => translate('Payment_information_details_required'),
            'customer_input.required' => translate('Customer_input_information_required')
        ]);

        $method_fields = [];
        if ($request->has('input_name')) {
            foreach ($request->input_name as $key => $field_name) {
                $method_fields[] = [
                    'input_name' => strtolower(str_replace(' ', "_", $request->input_name[$key])),
                    'input_data' => $request->input_data[$key],
                ];
            }
        }

        $method_informations = [];
        if ($request->has('customer_input')) {
            foreach ($request->customer_input as $key => $field_name) {
                $method_informations[] = [
                    'customer_input' => strtolower(str_replace(' ', "_", $request->customer_input[$key])),
                    'customer_placeholder' => $request->customer_placeholder[$key],
                    'is_required' => isset($request['is_required']) && isset($request['is_required'][$key]) ? 1 : 0,
                ];
            }
        }

        $this->OfflinePaymentMethod->where('id', $request->id)->update([
            'method_name' => $request->method_name,
            'method_fields' => json_encode($method_fields),
            'method_informations' => json_encode($method_informations),
            'created_at' => Carbon::now(),
        ]);

        Toastr::success(translate('offline_payment_method_update_successfully'));
        return to_route('admin.business-settings.offline');
    }


    public function delete(Request $request)
    {
        $this->OfflinePaymentMethod->where('id', $request->id)->delete();
        Toastr::success(translate('offline_payment_method_delete_successfully'));
        return to_route('admin.business-settings.offline');
    }

    public function status($id)
    {
        $data = $this->OfflinePaymentMethod->where('id', $id)->first();
        $message = '';

        if (isset($data)) {
            $data->update([
                'status' => $data->status == 1 ? 0 : 1,
            ]);
            $message = translate("status_updated_successfully");
        } else {
            $message = translate("status_update_failed");
        }

        Toastr::success(translate($message));
        return to_route('admin.business-settings.offline');
    }
    public function store_offline_verification_list(Request $request, $status)
    {
        $key = explode(' ', $request['search']);
        $offline_payments = OfflinePayments::with(['store'])
        ->whereNotNull('store_id')
            ->where('type', 'store')
            ->when(isset($key), function ($query) use ($key) {
                return $query->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        // $q->orWhere('id', 'like', "%{$value}%")
                        //     ->orWhere('status', 'like', "%{$value}%")
                        //     ->orWhere('type', 'like', "%{$value}%");
                        $q->whereHas('store', function($query) use ($value) {
                                $query->where('name', 'like', "%{$value}%");
                        });
                    }
                });
            })
            ->when($status == 'pending', function ($query) {
                return $query->where('status', 'pending');
            })
            ->when($status == 'denied', function ($query) {
                return $query->where('status', 'denied');
            })
            ->when($status == 'verified', function ($query) {
                return $query->where('status', 'verified');
            })
            // ->module(Config::get('module.current_module_id'))
            ->latest()
            ->paginate(config('default_pagination'));

        return view('admin-views.offline-payment.store_offline_verification_list', compact('offline_payments', 'status'));
    }
    public function company_offline_verification_list(Request $request, $status)
    {
        $key = explode(' ', $request['search']);
        $offline_payments = OfflinePayments::with(['store'])
        ->whereNotNull('store_id')
            ->where('type', 'company')
            ->when(isset($key), function ($query) use ($key) {
                return $query->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        // $q->orWhere('id', 'like', "%{$value}%")
                        //     ->orWhere('status', 'like', "%{$value}%")
                        //     ->orWhere('type', 'like', "%{$value}%");
                        $q->whereHas('store', function($query) use ($value) {
                                $query->where('name', 'like', "%{$value}%");
                        });
                    }
                });
            })
            ->when($status == 'pending', function ($query) {
                return $query->where('status', 'pending');
            })
            ->when($status == 'denied', function ($query) {
                return $query->where('status', 'denied');
            })
            ->when($status == 'verified', function ($query) {
                return $query->where('status', 'verified');
            })
            // ->module(Config::get('module.current_module_id'))
            ->latest()
            ->paginate(config('default_pagination'));

        return view('admin-views.offline-payment.company_offline_verification_list', compact('offline_payments', 'status'));
    }
    public function offline_payment_verification(Request $request)
    {
        $order =  OfflinePayments::findOrFail($request->id);
        if ($request->verify == 'yes') {

            if($order?->store && $order?->store->vendor->wallet){
                $amount = $order?->store->vendor->wallet->collected_cash;
                if($order->amount > $amount){
                    Toastr::warning(translate('messages.amount_mismatched'));
                    return back();
                }
            }
            $order->status = 'verified';
            if(!Helpers::collect_cash_verify($order, 'store_collect_cash_payments', $order->store->vendor->id)){
                Toastr::warning(translate('messages.offline_payment_issue'));
                return back();
            }
            $order->save();
            $payment_method_name = json_decode($order->payment_info, true)['method_name'];

            $data = [
                'title' => translate('messages.Your_Offline_payment_is_approved'),
                'description' => translate('messages.Offline_payment_is_approved'),
                'order_id' => $order->id,
                'image' => '',
                'type' => 'offline_payment',
            ];

            if ($order?->store && $order->store?->vendor) {

                Helpers::send_push_notif_to_device($order->store->vendor->firebase_token, $data);
                DB::table('user_notifications')->insert([
                    'data' => json_encode($data),
                    'vendor_id' => $order->store->vendor_id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }


            $order->payment_method = $payment_method_name;
        } else {
            $order->status = 'denied';
            $order->note = $request->note ?? null;
            $order->save();
            $data = [
                'title' => translate('messages.Your_Offline_payment_was_rejected'),
                'description' => $request->note ?? null,
                'order_id' => $order->id,
                'image' => '',
                'type' => 'offline_payment',
            ];
            if ($order?->store && $order->store?->vendor) {

                Helpers::send_push_notif_to_device($order->store->vendor->firebase_token, $data);
                DB::table('user_notifications')->insert([
                    'data' => json_encode($data),
                    'vendor_id' => $order->store->vendor_id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        Toastr::success(translate('Payment_status_updated'));
        return back();
    }
    public function delivery_man_offline_verification_list(Request $request, $status)
    {
        $key = explode(' ', $request['search']);
        $offline_payments = OfflinePayments::with(['delivery_man'])
             ->whereNotNull('delivery_man_id')
            ->where('type', 'deliveryman')
            ->when(isset($key), function ($query) use ($key) {
                return $query->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        // $q->orWhere('id', 'like', "%{$value}%")
                        //     ->orWhere('status', 'like', "%{$value}%")
                        //     ->orWhere('type', 'like', "%{$value}%");
                        $q->whereHas('delivery_man', function($query) use ($value) {
                                $query->where('f_name', 'like', "%{$value}%")
                                      ->orWhere('l_name', 'like', "%{$value}%");
                        });

                    }
                });
            })
            ->when($status == 'pending', function ($query) {
                return $query->where('status', 'pending');
            })
            ->when($status == 'denied', function ($query) {
                return $query->where('status', 'denied');
            })
            ->when($status == 'verified', function ($query) {
                return $query->where('status', 'verified');
            })
            ->latest()
            ->paginate(config('default_pagination'));

        return view('admin-views.offline-payment.deliveryman_offline_verification_list', compact('offline_payments', 'status'));
    }
    public function delivery_man_offline_payment_verification(Request $request)
    {
        $order =  OfflinePayments::findOrFail($request->id);
    
        if ($request->verify == 'yes') {

            $order->status = 'verified';
            if($order->status="denied"){
            if(!Helpers::collect_cash_verify($order, 'deliveryman_collect_cash_payments', $order->delivery_man->id)){
                Toastr::warning(translate('messages.offline_payment_issue'));
                return back();
            }
           }
            $order->save();
            $payment_method_name = json_decode($order->payment_info, true)['method_name'];

            $data = [
                'title' => translate('messages.Your_Offline_payment_is_approved'),
                'description' => translate('messages.Offline_payment_is_approved'),
                'order_id' => $order->id,
                'image' => '',
                'type' => 'offline_payment',
            ];

            if ($order?->delivery_man) {

                Helpers::send_push_notif_to_device($order->delivery_man?->fcm_token, $data);
                DB::table('user_notifications')->insert([
                    'data' => json_encode($data),
                    'delivery_man_id' => $order?->delivery_man?->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }


            $order->payment_method = $payment_method_name;
        } else {
            $order->status = 'denied';
            $order->note = $request->note ?? null;
            if(!Helpers::return_cash_verify($order, 'deliveryman_return_cash_payments', $order->delivery_man->id)){
                Toastr::warning(translate('messages.offline_payment_issue'));
                return back();
            }
            $order->save();
            $data = [
                'title' => translate('messages.Your_Offline_payment_was_rejected'),
                'description' => $request->note ?? null,
                'order_id' => $order->id,
                'image' => '',
                'type' => 'offline_payment',
            ];
            if ($order?->delivery_man) {

                Helpers::send_push_notif_to_device($order->delivery_man?->fcm_token, $data);
                DB::table('user_notifications')->insert([
                    'data' => json_encode($data),
                    'delivery_man_id' => $order?->delivery_man?->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        Toastr::success(translate('Payment_status_updated'));
        return back();
    }
}
