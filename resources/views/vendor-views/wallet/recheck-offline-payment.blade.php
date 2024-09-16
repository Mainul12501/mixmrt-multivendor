@extends('layouts.vendor.app')

@section('title',translate('Offline payment'))

@push('css_or_js')

@endpush

@section('content')

@php($store_data = \App\CentralLogics\Helpers::get_store_data())
<div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex flex-wrap justify-content-between">
                <h1 class="page-header-title text-break">
                    <span class="page-header-icon">
                        <img src="{{asset('public/assets/admin/img/items.png')}}" class="w--22" alt="">
                    </span>
                    <span>{{ translate('messages.offline_payment') }}</span>
                </h1>
            </div>
        </div>
      
        <!-- Description Card Start -->
        <div class="card mb-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-borderless table-thead-bordered">
                        <thead class="thead-light">
                            <tr>
                                @foreach($data?->method_fields as $d)
                                <th class="px-4 border-0"><h4 class="m-0 text-capitalize">{{translate($d['input_name'])}}</h4></th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                @foreach($data?->method_fields as $d)
                                <td class="px-4 max-w--220px">
                                    <div class="">
                                      <strong> {{ $d['input_data'] }}</strong>
                                    </div>
                                </td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- Description Card End -->
{{-- {{ dd($data->method_informations) }} --}}
        <!-- Card -->
        <div class="card">
            <div class="card-header py-2 border-0">
                <div class="search--button-wrapper">
                <h5 class="card-title">{{translate('messages.offline_payment_information')}}<span class="badge badge-soft-dark ml-2" id="itemCount">{{ \App\CentralLogics\Helpers::format_currency(abs($offline_payment['amount'])) }}</span></h5>
                </div>
            </div>
            
            <div class="card mb-3">
                <div class="card-body">
                    <form action="{{route('vendor.wallet.offline_payment_edit',['id' => $offline_payment->id])}}" method="post">
                        @csrf
                        <div class="row">
                            <input type="hidden" name="method_id" value="{{ $data->id }}">
                            @foreach($data?->method_informations as $method) 
                           @foreach (json_decode($offline_payment->payment_info,true) as $key_name => $offline_pay)
                           @if($method['customer_input'] == $key_name )
                           <div class="col-lg-6 col-sm-6">
                            <div class="form-group">
                                <label class="input-label" for="{{ $method['customer_input'] }}">{{translate($method['customer_input'])}}</label>
                                <input id="{{ $method['customer_input'] }}" type="text" name="{{ $method['customer_input'] }}" class="form-control" value="{{$method['customer_input'] == $key_name ? $offline_pay : '' }}"
                                    placeholder="{{$method['customer_placeholder']}}" {{ $method['is_required'] ? 'required' : '' }} maxlength="100">
                            </div>
                        </div>
                        @endif
                           @endforeach
                            @endforeach
                            
                            
                        </div>
                        <div class="btn--container justify-content-end">
                            <button type="submit" class="btn btn--primary">{{translate('messages.update')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
      
    </div>
@endsection
