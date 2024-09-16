@extends('Gateways::payment.layouts.master')

@push('script')
    <link rel="stylesheet" href="{{asset('Modules/Gateways/public/assets/modules/css/senang-pay.css')}}">
    <link rel="stylesheet" href="{{asset('Modules/Gateways/public/assets/modules/css/common.css')}}">
@endpush

@section('content')

    @if(isset($config))
        <h1 class="text-center">{{translate('Please do not refresh this page')}}...</h1>

        <div class="col-md-6 mb-4 cursor-pointer">
            <div class="card">
                <div class="card-body h-70px">
                    @php($secretkey = $config->secret_key)
                    @php($data = new \stdClass())
                    @php($data->merchantId = $config->merchant_id)
                    @php($data->amount =  number_format($payment_data->payment_amount, 2)  )
                    @php($data->attribute = $payment_data->attribute)
                    @php($data->attribute_id = $payment_data->attribute_id)
                    @php($data->name = $payer->name??'')
                    @php($data->email = $payer->email ??'')
                    @php($data->phone = $payer->phone ??'')
                    @php($mode = $config->mode ?? 'test')
                    @php($data->hashed_string = md5($secretkey . urldecode($data->attribute.$data->amount.$data->attribute_id) ))

                    <form id="form" method="post"
                          action="https://{{ $mode =='live'?'app.senangpay.my':'sandbox.senangpay.my'}}/payment/{{$config->merchant_id}}">
                        <input type="hidden" name="amount" value="{{$data->amount}}">
                        <input type="hidden" name="name" value="{{$data->name}}">
                        <input type="hidden" name="email" value="{{$data->email}}">
                        <input type="hidden" name="phone" value="{{$data->phone}}">
                        <input type="hidden" name="hash" value="{{$data->hashed_string}}">
                        <input type="hidden" name="detail" value="{{$data->attribute}}">
                        <input type="hidden" name="order_id" value="{{$data->attribute_id}}">
                    </form>

                </div>
            </div>
        </div>
    @endif

    @push('script_2')
        <script src="{{asset('Modules/Gateways/public/assets/modules/js/senang-pay.js')}}"></script>
    @endpush
@endsection
