@extends('layouts.vendor.app')

@section('title',translate('messages.store_wallet'))

@push('css_or_js')

@endpush

@section('content')
@php($offline_payments = \App\Models\OfflinePaymentMethod::where('status', 1)->latest()->paginate(config('default_pagination')))
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h2 class="page-header-title text-capitalize">
                        <div class="card-header-icon d-inline-flex mr-2 img">
                            <img src="{{asset('/public/assets/admin/img/image_90.png')}}" alt="public">
                        </div>
                        <span>
                            {{translate('messages.store_wallet')}}
                        </span>
                    </h2>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <?php
        $wallet = \App\Models\StoreWallet::where('vendor_id',\App\CentralLogics\Helpers::get_vendor_id())->first();
        if(isset($wallet)==false){
            \Illuminate\Support\Facades\DB::table('store_wallets')->insert([
                'vendor_id'=>\App\CentralLogics\Helpers::get_vendor_id(),
                'created_at'=>now(),
                'updated_at'=>now()
            ]);
            $wallet = \App\Models\StoreWallet::where('vendor_id',\App\CentralLogics\Helpers::get_vendor_id())->first();
        }
        ?>
        @include('vendor-views.wallet.partials._balance_data',['wallet'=>$wallet])
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="datatable"
                       class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                       data-hs-datatables-options='{
                                    "order": [],
                                    "orderCellsTop": true,
                                    "paging":false
                                }' >
                    <thead class="thead-light">
                    <tr>
                        <th>{{ translate('messages.sl') }}</th>
                        <th>{{translate('messages.amount')}}</th>
                        <th>{{translate('messages.Payment_Time')}}</th>
                        <th>{{translate('messages.Payment_method')}}</th>
                        <th>{{translate('messages.status')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($account_transaction as $k=>$wr)

                        <tr>
                            <td scope="row">{{$k+$account_transaction->firstItem()}}</td>
                            <td> {{ \App\CentralLogics\Helpers::format_currency($wr['amount'])}}</td>

                            <td>
                                <span class="d-block">{{ \App\CentralLogics\Helpers::time_date_format($wr['created_at'])}}</span>
                            </td>
                            <td>
                                @if($wr->method)
                                    {{ translate($wr->method) }}
                                @else
                                    {{ translate('Default_method') }}
                                @endif
                            </td>
                            <td>
                                <label class="badge badge-soft-success">{{translate('messages.approved')}}</label>
                            </td>

                        </tr>
                    @endforeach
                    </tbody>
                </table>
                @if(count($account_transaction) === 0)
                    <div class="empty--data">
                        <img src="{{asset('/public/assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
                        <h5>
                            {{translate('no_data_found')}}
                        </h5>
                    </div>
                @endif
            </div>
        </div>
        <div class="card-footer pt-0 border-0">
            {{$account_transaction->links()}}
        </div>
    </div>

    <div class="modal fade" id="payment_model" tabindex="-1"  role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">{{translate('messages.Pay')}}  </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>

                </div>
           {{-- <div class="row">
            <div class="col-md-6">
                <a class="btn btn--primary d-flex gap-1 align-items-center text-nowrap"  href="javascript:" data-toggle="modal" data-target="#payment_model">{{translate('messages.Pay_Now')}}
                    <span class="form-label-secondary  d-flex" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('Adjust_the_payable_&_withdrawable_balance_with_your_wallet_(Cash_in_Hand)_or_click_‘Pay_Now’.')}}"> <i class="tio-info-outined"> </i> </span> </span></a>
               </div>
               <div class="col-md-6">
                <a class="btn btn--primary d-flex gap-1 align-items-center text-nowrap"  href="javascript:" data-toggle="modal" data-target="#payment_model">{{translate('messages.Pay_Now')}}
                    <span class="form-label-secondary  d-flex" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('Adjust_the_payable_&_withdrawable_balance_with_your_wallet_(Cash_in_Hand)_or_click_‘Pay_Now’.')}}"> <i class="tio-info-outined"> </i> </span> </span></a>
               </div>
           </div> --}}
                <form action="{{ route('vendor.wallet.make_payment') }}" method="get" class="needs-validation">
                    <div class="modal-body">
                        @csrf
                        <label for="" class="d-flex align-items-start gap-3 mb-1">
                            {{ translate('messages.payment_method') }}
                        </label>
                        <select name="payment_method" class="form-control" id="payment_method">
                            <option value="0" disabled>{{ translate('messages.select_payment_type') }}</option>
                            <option value="offline">{{ translate('messages.Pay_Via_offline') }}</option>
                            <option value="online">{{ translate('messages.Pay_Via_Online') }}</option>
                        </select>
                        <input type="hidden" value="{{ \App\CentralLogics\Helpers::get_store_id() }}" name="store_id"/>
                        <input type="hidden" value="{{ abs($wallet->collected_cash) }}" name="amount"/>
                        {{-- <h5 class="mb-5 "><input type="radio" required name="payment_method" value="online_payment"> {{ translate('Pay_Via_Online') }} &nbsp; <small>({{ translate('Faster_&_secure_way_to_pay_bill') }})</small></h5> --}}
                        <div class="row g-3 mt-3 online_payment d-none" id="online_payment">
                            @forelse ($data as $item)
                                <div class="col-sm-6">
                                    <div class="d-flex gap-3 align-items-center">
                                        <input type="radio" required id="{{$item['gateway'] }}" name="payment_gateway" value="{{$item['gateway'] }}">
                                        <label for="{{$item['gateway'] }}" class="d-flex align-items-center gap-3 mb-0">
                                            <img height="24" src="{{ asset('storage/app/public/payment_modules/gateway_image/'. $item['gateway_image']) }}" alt="">
                                            {{ $item['gateway_title'] }}
                                        </label>
                                    </div>
                                </div>
                            @empty
                                <h1>{{ translate('no_payment_gateway_found') }}</h1>
                            @endforelse
                        </div>
                        {{-- <h5 class="mt-5 "><input type="radio" required name="payment_method" value="offline_payment">{{ translate('Pay_Via_offline') }} &nbsp; <small>({{ translate('Faster_&_secure_way_to_pay_bill') }})</small></h5> --}}
                        <div class="row g-3 mt-3 offline_payment d-none" id="offline_payment">
                            @forelse ($offline_payments as $item)
                                <div class="col-sm-6">
                                    <div class="d-flex gap-3 align-items-center">
                                        <input type="radio" required id="{{$item['method_name'] }}" name="payment_gateway" value="{{$item['method_name'] }}">
                                        <label for="{{$item['method_name'] }}" class="d-flex align-items-center gap-3 mb-0">
                                            
                                            {{ $item['method_name'] }}
                                        </label>
                                    </div>
                                </div>
                            @empty
                                <h1>{{ translate('no_payment_gateway_found') }}</h1>
                            @endforelse
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button id="reset_btn" type="reset" data-dismiss="modal" class="btn btn-secondary" >{{ translate('Close') }} </button>
                        <button type="submit" class="btn btn-primary">{{ translate('Proceed') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </div>


    <div class="modal fade" id="Adjust_wallet" tabindex="-1"  role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">{{translate('messages.Adjust_Wallet')}}  </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>

                </div>
                <form action="{{ route('vendor.wallet.make_wallet_adjustment') }}" method="POST" class="needs-validation">
                    <div class="modal-body">
                        @csrf
                        <h5 class="mb-5 ">{{ translate('This_will_adjust_the_collected_cash_on_your_earning') }} </h5>
                    </div>

                    <div class="modal-footer">
                        <button id="reset_btn" type="reset" data-dismiss="modal" class="btn btn-secondary" >{{ translate('Close') }} </button>
                        <button type="submit" class="btn btn-primary">{{ translate('Proceed') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('script_2')
    <script src="{{asset('public/assets/admin')}}/js/view-pages/vendor/wallet-method.js"></script>

    <script>
        "use strict";
        $('#withdraw_method').on('change', function () {
    $('#submit_button').attr("disabled","true");
    let method_id = this.value;

    // Set header if need any otherwise remove setup part
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        url: "{{route('vendor.wallet.method-list')}}" + "?method_id=" + method_id,
        data: {},
        processData: false,
        contentType: false,
        type: 'get',
        success: function (response) {
            $('#submit_button').removeAttr('disabled');
            let method_fields = response.content.method_fields;
            $("#method-filed__div").html("");
            method_fields.forEach((element, index) => {
                $("#method-filed__div").append(`
                    <div class="form-group mt-2">
                        <label for="wr_num" class="fz-16 text-capitalize c1 mb-2">${element.input_name.replaceAll('_', ' ')}</label>
                        <input type="${element.input_type == 'phone' ? 'number' : element.input_type  }" class="form-control" name="${element.input_name}" placeholder="${element.placeholder}" ${element.is_required === 1 ? 'required' : ''}>
                    </div>
                `);
            })

        },
        error: function () {

        }
    });
});

$('.payment-warning').on('click',function (event ){
            event.preventDefault();
            toastr.info(
                "{{ translate('messages.Currently,_there_are_no_payment_options_available._Please_contact_admin_regarding_any_payment_process_or_queries.') }}", {
                    CloseButton: true,
                    ProgressBar: true
                });
        });
    </script>
@endpush
