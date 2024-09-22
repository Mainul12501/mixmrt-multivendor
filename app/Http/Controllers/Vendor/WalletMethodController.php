<?php

namespace App\Http\Controllers\Vendor;


use App\Models\DisbursementWithdrawalMethod;
use App\CentralLogics\Helpers;
use App\Models\Store;
use App\Models\WithdrawalMethod;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class WalletMethodController extends Controller
{
    public function index(Request $request)
    {
        $key = explode(' ', $request['search']);
        $withdrawal_methods = WithdrawalMethod::ofStatus(1)->get();
        $vendor_withdrawal_methods = DisbursementWithdrawalMethod::where('store_id', Helpers::get_store_id())
            ->when( isset($key) , function($query) use($key){
                $query->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('method_name', 'like', "%{$value}%");
                    }
                });
            }
            )
            ->latest()->paginate(config('default_pagination'));

        return view('vendor-views.wallet-method.index', compact('withdrawal_methods','vendor_withdrawal_methods'));
    }

    public function store(Request $request)
    {
        $method = WithdrawalMethod::find($request['withdraw_method']);
        $fields = array_column($method->method_fields, 'input_name');
        $values = $request->all();

        $method_data = [];
        foreach ($fields as $field) {
            if(key_exists($field, $values)) {
                $method_data[$field] = $values[$field];
            }
        }

        $data = [
            'store_id' => Helpers::get_store_id(),
            'withdrawal_method_id' => $method['id'],
            'method_name' => $method['method_name'],
            'method_fields' => json_encode($method_data),
            'is_default' => 0,
            'created_at' => now(),
            'updated_at' => now()
        ];

        DB::table('disbursement_withdrawal_methods')->insert($data);
        Toastr::success(translate('Disbursement_method_stored.'));
        return redirect()->back();
    }

//    mainul start
    public function storeFromProfile(Request $request)
    {
        $method = WithdrawalMethod::find($request['withdraw_method']);
        $fields = array_column($method->method_fields, 'input_name');
        $values = $request->all();

        $method_data = [];
        foreach ($fields as $field) {
            if(key_exists($field, $values)) {
                $method_data[$field] = $values[$field];
            }
        }
        $existDisbursementWithdrawalMethod = DisbursementWithdrawalMethod::where(['store_id' => Helpers::get_store_id(), 'is_default' => 1])->first();
        $defaultStatus = 1;
        $pendingStatus = 0;
        if (!empty($existDisbursementWithdrawalMethod)) {
            $defaultStatus = 0;
            $pendingStatus = 1;
        }
        $data = [
            'store_id' => Helpers::get_store_id(),
            'store_name' => Store::find(Helpers::get_store_id())->name,
            'withdrawal_method_id' => $method['id'],
            'method_name' => $method['method_name'],
            'method_fields' => json_encode($method_data),
            'is_default' => $defaultStatus,
            'pending_status' => $pendingStatus,
            'created_at' => now(),
            'updated_at' => now()
        ];
        $totalWMC = DisbursementWithdrawalMethod::where(['store_id' => Helpers::get_store_id()])->get()->count() ?? 0;
        if ( $totalWMC>= 2)
        {
            DisbursementWithdrawalMethod::where(['store_id' => Helpers::get_store_id(), 'pending_status' => 1])->first()->update($data);
            Toastr::success(translate('Disbursement_method_request_sent.'));
        } else {
            DB::table('disbursement_withdrawal_methods')->insert($data);
            Toastr::success(translate('Disbursement_method_stored.'));
        }

        return redirect()->back();
    }
//    mainul ends
    public function default(Request $request)
    {
        $method = DisbursementWithdrawalMethod::find($request->id);
        $method->is_default = $request->default;
        $method->save();
        DisbursementWithdrawalMethod::whereNot('id', $request->id)->where('store_id',Helpers::get_store_id())->update(['is_default' => 0]);
        Toastr::success(translate('messages.default_method_updated'));
        return back();
    }

    public function delete(Request $request)
    {
        $method = DisbursementWithdrawalMethod::find($request->id);
        $method->delete();
        Toastr::success(translate('messages.method_deleted_successfully'));
        return back();
    }
    public function accept(Request $request)
    {
        $method = DisbursementWithdrawalMethod::find($request->id);
        DisbursementWithdrawalMethod::where(['store_id'=>$method->store_id, 'is_default' => 1])->first()->delete();
        $method->pending_status = 0;
        $method->is_default = 1;
        $method->save();
        Toastr::success(translate('messages.Request accepted Successfully'));
        return back();
    }

}
