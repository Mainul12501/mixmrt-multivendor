@extends('layouts.vendor.app')

@section('title', translate('messages.parcel_report'))

@push('css_or_js')
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- End Page Header -->

        <div class="card mb-20">
            <div class="card-body">
                <h4 class="">{{ translate('Search Data') }}</h4>
                <form method="get">
                    <div class="row g-3">
                        <div class="col-sm-6 col-md-3">
                            <select class="form-control set-filter" name="filter"
                                    data-url="{{ url()->full() }}" data-filter="filter">
                                <option value="all_time" {{ isset($filter) && $filter == 'all_time' ? 'selected' : '' }}>
                                    {{ translate('messages.All Time') }}</option>
                                <option value="this_year" {{ isset($filter) && $filter == 'this_year' ? 'selected' : '' }}>
                                    {{ translate('messages.This Year') }}</option>
                                <option value="previous_year"
                                    {{ isset($filter) && $filter == 'previous_year' ? 'selected' : '' }}>
                                    {{ translate('messages.Previous Year') }}</option>
                                <option value="this_month"
                                    {{ isset($filter) && $filter == 'this_month' ? 'selected' : '' }}>
                                    {{ translate('messages.This Month') }}</option>
                                <option value="this_week" {{ isset($filter) && $filter == 'this_week' ? 'selected' : '' }}>
                                    {{ translate('messages.This Week') }}</option>
                                <option value="custom" {{ isset($filter) && $filter == 'custom' ? 'selected' : '' }}>
                                    {{ translate('messages.Custom') }}</option>
                            </select>
                        </div>
                        @if (isset($filter) && $filter == 'custom')
                            <div class="col-sm-6 col-md-3">
                                <input type="date" name="from" id="from_date" class="form-control"
                                    placeholder="{{ translate('Start Date') }}"
                                    value={{ $from ? $from  : '' }} required>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <input type="date" name="to" id="to_date" class="form-control"
                                    placeholder="{{ translate('End Date') }}"
                                    value={{ $to ? $to  : '' }}  required>
                            </div>
                        @endif
                        <div class="col-sm-6 col-md-3 ml-auto">
                            <button type="submit"
                                class="btn btn-primary btn-block h--45px">{{ translate('Filter') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- End Stats -->
        <!-- Card -->
        <div class="card mt-3">
            <!-- Header -->
            <div class="card-header border-0 py-2">
                <div class="search--button-wrapper">
                    <h3 class="card-title">
                        {{ translate('messages.Transaction_lists') }} <span
                            class="badge badge-soft-secondary" id="countItems">{{ $order_transactions->total() }}</span>
                    </h3>
                    <form  class="search-form">
                        <!-- Search -->
                        <div class="input--group input-group input-group-merge input-group-flush">
                            <input name="search" value="{{ request()->search ?? null }}"   type="search" class="form-control" placeholder="{{ translate('Search by Order ID') }}">
                            <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>
                        </div>
                        <!-- End Search -->
                    </form>
                    <!-- Static Export Button -->
                    <div class="hs-unfold ml-3">
                        <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle btn export-btn font--sm"
                            href="javascript:;"
                            data-hs-unfold-options="{
                                &quot;target&quot;: &quot;#usersExportDropdown&quot;,
                                &quot;type&quot;: &quot;css-animation&quot;
                            }"
                            data-hs-unfold-target="#usersExportDropdown" data-hs-unfold-invoker="">
                            <i class="tio-download-to mr-1"></i> {{ translate('export') }}
                        </a>

                        <div id="usersExportDropdown"
                            class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right hs-unfold-content-initialized hs-unfold-css-animation animated hs-unfold-reverse-y hs-unfold-hidden">

                            <span class="dropdown-header">{{ translate('download_options') }}</span>
                            <a id="export-excel" class="dropdown-item" href="{{route('vendor.report.parcel-export', ['type'=>'excel',request()->getQueryString()])}}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="{{ asset('public/assets/admin') }}/svg/components/excel.svg"
                                    alt="Image Description">
                                {{ translate('messages.excel') }}
                            </a>
                            <a id="export-csv" class="dropdown-item" href="{{route('vendor.report.parcel-export', ['type'=>'csv',request()->getQueryString()])}}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="{{ asset('public/assets/admin') }}/svg/components/placeholder-csv-format.svg"
                                    alt="Image Description">
                                .{{ translate('messages.csv') }}
                            </a>
                        </div>
                    </div>
                    <!-- Static Export Button -->
                </div>
            </div>
            <!-- End Header -->

            <!-- Body -->
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-borderless middle-align __txt-14px">
                        <thead class="thead-light white--space-false">
                            <tr>
                                <th >{{translate('sl')}}</th>
                                <th class="text-center" >{{translate('messages.order_id')}}</th>
                                <th class="text-center" >{{ translate('Customer Name') }}</th>
                                <th class="text-center" >{{ translate('delivery_charge') }}</th>
                                <th class="text-center" >{{ translate('additional_charge') }}</th>
                                <th class="text-center" >{{ translate('admin_commission') }}</th>
                                <th class="text-center" >{{ translate('company_net_income') }}</th>
                                <th class="text-center" >{{ translate('amount_received_by') }}</th>
                                <th class="text-center" >{{ translate('payment_method') }}</th>
                                <th class="text-center" >{{ translate('payment_status') }}</th>
                            </tr>
                        </thead>
                        <tbody id="set-rows">
                            @foreach ($order_transactions as $key => $ot)
                            <tr>
                                <td scope="row">{{$key+$order_transactions->firstItem()}}</td>
                                <td class="text-center" >
                                        @if (isset($ot['order_id']))
                                        <a href="{{route('vendor.order.details',['id'=>$ot['order_id']])}}">{{$ot['order_id']}}</a>
                                        @else
                                        <label class="badge badge-danger">{{translate('messages.invalid_order_data')}}</label>
                                        @endif
                                </td>
                                <td class="text-center">
                                    @if (isset($ot->order->customer))
                                    {{ $ot->order->customer->f_name.' '.$ot->order->customer->l_name }}
                                    @elseif($ot->order->is_guest)
                                        @php($customer_details = json_decode($ot->order['delivery_address'],true))
                                        {{$customer_details['contact_person_name']}}
                                    @else
                                    <label class="badge badge-danger">{{translate('messages.invalid_customer_data')}}</label>

                                    @endif
                                </td>
                                <td class="text-center" >
                                    {{ \App\CentralLogics\Helpers::format_currency($ot->delivery_charge) }}</td>
                                    <td class="text-center">
                                        {{ \App\CentralLogics\Helpers::format_currency(($ot->additional_charge)) }}
                                    </td>
                                <td class="text-right pr-xl-5">
                                    {{ \App\CentralLogics\Helpers::format_currency(($ot->admin_commission - $ot->order['flash_admin_discount_amount'])) }}
                                </td>
                                <td class="text-center">
                                    {{ \App\CentralLogics\Helpers::format_currency($ot->company_amount) }}
                                </td>
                                @if ($ot->received_by == 'admin')
                                <td class="text-center">{{ translate('messages.admin') }}</td>
                            @elseif ($ot->received_by == 'deliveryman')
                                <td class="text-center">
                                    <div>{{ translate('messages.delivery_man') }}</div>
                                    <div class="text-center mw--85px">
                                        @if (isset($ot->delivery_man) && $ot->delivery_man->earning == 1)
                                        <span class="badge badge-soft-primary">
                                            {{translate('messages.freelance')}}
                                        </span>
                                        @elseif (isset($ot->delivery_man) && $ot->delivery_man->earning == 0 && $ot->delivery_man->type == 'restaurant_wise')
                                        <span class="badge badge-soft-warning">
                                            {{translate('messages.restaurant')}}
                                        </span>
                                        @elseif (isset($ot->delivery_man) && $ot->delivery_man->earning == 0 && $ot->delivery_man->type == 'zone_wise')
                                        <span class="badge badge-soft-success">
                                            {{translate('messages.admin')}}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            @elseif ($ot->received_by == 'store')
                                <td class="text-center">{{ translate('messages.store') }}</td>
                            @else <td class="text-center">{{$ot->received_by}}</td>

                            @endif
                            <td class="text-center">
                                {{ translate(str_replace('_', ' ', $ot->order['payment_method'])) }}
                            </td>
                            <td class="text-center white-space-nowrap">
                                @if ($ot->status)
                                <span class="badge badge-soft-danger">
                                    {{translate('messages.refunded')}}
                                  </span>
                                @else
                                <span class="badge badge-soft-success">
                                    {{translate('messages.completed')}}
                                  </span>
                                @endif
                            </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- End Table -->


                @if (count($order_transactions) !== 0)
                    <hr>
                    <div class="page-area">
                        {!! $order_transactions->withQueryString()->links() !!}
                    </div>
                @endif
                @if (count($order_transactions) === 0)
                    <div class="empty--data">
                        <img src="{{ asset('/public/assets/admin/svg/illustrations/sorry.svg') }}" alt="public">
                        <h5>
                            {{ translate('no_data_found') }}
                        </h5>
                    </div>
                @endif
            </div>            <!-- End Body -->
        </div>
        <!-- End Card -->
    </div>
@endsection

@push('script')
@endpush

@push('script_2')
    <script src="{{asset('public/assets/admin')}}/js/view-pages/vendor/report.js"></script>
@endpush

