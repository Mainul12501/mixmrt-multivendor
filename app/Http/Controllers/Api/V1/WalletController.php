<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use App\Models\User;
use App\Models\WalletBonus;
use App\Models\WalletPayment;
use App\Models\WalletToBank;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Library\Payer;
use App\Traits\Payment;
use App\Library\Receiver;
use App\Library\Payment as PaymentInfo;

class WalletController extends Controller
{
    public function transactions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required',
            'offset' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $paginator = WalletTransaction::where('user_id', $request->user()->id)
        ->when($request['type'] && $request['type']=='order', function($query){
            $query->whereIn('transaction_type', ['order_place', 'order_refund','partial_payment']);
        })
        ->when($request['type'] && $request['type']=='loyalty_point', function($query){
            $query->whereIn('transaction_type', ['loyalty_point']);
        })
        ->when($request['type'] && $request['type']=='add_fund', function($query){
            $query->whereIn('transaction_type', ['add_fund']);
        })
        ->when($request['type'] && $request['type']=='referrer', function($query){
            $query->whereIn('transaction_type', ['referrer']);
        })
        ->when($request['type'] && $request['type']=='CashBack', function($query){
            $query->whereIn('transaction_type', ['CashBack']);
        })
        ->latest()->paginate($request->limit, ['*'], 'page', $request->offset);

        $data = [
            'total_size' => $paginator->total(),
            'limit' => $request->limit,
            'offset' => $request->offset,
            'data' => $paginator->items()
        ];
        return response()->json($data, 200);
    }

    public function add_fund(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $digital_payment = Helpers::get_business_settings('digital_payment');
        if($digital_payment['status'] == 0){
            return response()->json(['errors' => ['message' => 'digital_payment_is_disable']], 403);
        }

        $customer = User::find($request->user()->id);

        $wallet = new WalletPayment();
        $wallet->user_id = $customer->id;
        $wallet->amount = $request->amount;
        $wallet->payment_status = 'pending';
        $wallet->payment_method = $request->payment_method;
        $wallet->save();

        $wallet_amount = $request->amount;

        if (!isset($customer)) {
            return response()->json(['errors' => ['message' => 'Customer not found']], 403);
        }

        if (!isset($wallet_amount)) {
            return response()->json(['errors' => ['message' => 'Amount not found']], 403);
        }

        if (!$request->has('payment_method')) {
            return response()->json(['errors' => ['message' => 'Payment not found']], 403);
        }

        $payer = new Payer(
            $customer->f_name . ' ' . $customer->l_name ,
            $customer->email,
            $customer->phone,
            ''
        );

        $currency=BusinessSetting::where(['key'=>'currency'])->first()->value;
        $store_logo= BusinessSetting::where(['key' => 'logo'])->first();
        $additional_data = [
            'business_name' => BusinessSetting::where(['key'=>'business_name'])->first()?->value,
            'business_logo' => \App\CentralLogics\Helpers::get_image_helper($store_logo,'value', asset('storage/app/public/business/').'/' . $store_logo->value, asset('public/assets/admin/img/160x160/img2.jpg') ,'business/' )
        ];
        $payment_info = new PaymentInfo(
            success_hook: 'wallet_success',
            failure_hook: 'wallet_failed',
            currency_code: $currency,
            payment_method: $request->payment_method,
            payment_platform: $request->payment_platform,
            payer_id: $customer->id,
            receiver_id: '100',
            additional_data: $additional_data,
            payment_amount: $wallet_amount,
            external_redirect_link: $request->has('callback')?$request['callback']:session('callback'),
            attribute: 'wallet_payments',
            attribute_id: $wallet->id
        );

        $receiver_info = new Receiver('receiver_name','example.png');

        $redirect_link = Payment::generate_link($payer, $payment_info, $receiver_info);

        $data = [
            'redirect_link' => $redirect_link,
        ];
        return response()->json($data, 200);

    }

    public function get_bonus()
    {
        $bonuses = WalletBonus::Active()->Running()->latest()->get();
        return response()->json($bonuses??[],200);
    }

    public function reqToTransfer(Request $request)
    {
        try {
            $loggedUser = $request->user();
            if ($loggedUser->wallet_balance < $request->request_balance)
            {
                return response()->json(['errors' => ['message' => 'Too much Amount.']], 403);
            }
            $walletToBank = new WalletToBank();
            $walletToBank->user_id  = $loggedUser->id;
            $walletToBank->request_balance  = $request->request_balance;
            $walletToBank->bank_name  = $request->bank_name;
            $walletToBank->bank_account_number  = $request->bank_account_number;
            $walletToBank->bank_routing_number  = $request->bank_routing_number;
            $walletToBank->notes  = $request->notes;
            DB::beginTransaction();
            $walletToBank->save();
            DB::commit();
            return response()->json(['success' => ['message' => 'Request sent successfully.']]);
        } catch (\Exception $exception)
        {
            return response()->json($exception->getMessage());
        }
        return response()->json();
    }
}
