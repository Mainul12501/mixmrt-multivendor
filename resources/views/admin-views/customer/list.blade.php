@extends('layouts.admin.app')

@section('title', translate('Customer List'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title mr-3">
                <span class="page-header-icon">
                    <img src="{{asset('/public/assets/admin/img/people.png')}}" class="w--26" alt="">
                </span>
                <span>
                     {{ translate('messages.customers') }} <span class="badge badge-soft-dark ml-2" id="count">{{ $customers->total() }}</span>
                </span>
            </h1>
        </div>
        <!-- End Page Header -->

        <!-- Card -->
        <div class="card">
            <!-- Header -->
            <div class="card-header border-0  py-2">
                <div class="search--button-wrapper justify-content-end">


                    <div class="col-sm-auto min--240">
                        <select name="zone_id" class="form-control js-select2-custom set-filter"
                        data-filter="zone_id"
                                data-url="{{ url()->full() }}">
                            <option value="all">{{ translate('messages.All_Zones') }}</option>
                            @foreach(\App\Models\Zone::orderBy('name')->get() as $z)
                                <option
                                    value="{{$z['id']}}" {{ request()->get('zone_id')  == $z['id']?'selected':''}}>
                                    {{$z['name']}}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-sm-auto min--240">
                        <select name="order_wise" class="form-control js-select2-custom set-filter"
                        data-filter="order_wise"
                                data-url="{{ url()->full() }}">
                            <option  {{ request()->get('order_wise')  == 'top'?'selected':''}}  value="top">{{ translate('messages.Total_orders') }} ({{ translate('messages.High_to_Low') }})</option>
                            <option {{ request()->get('order_wise')  == 'least'?'selected':''}}  value="least">{{ translate('messages.Total_orders') }} ({{ translate('messages.Low_to_High') }})</option>
                            <option {{ request()->get('order_wise')  == 'latest'?'selected':''}}  value="latest">{{ translate('messages.New_Customers') }}</option>
                        </select>
                    </div>

                    <div class="col-sm-auto min--240">
                        <select name="filter" class="form-control js-select2-custom set-filter"
                        data-filter="filter"
                                data-url="{{ url()->full() }}">
                            <option  {{ request()->get('filter')  == 'all'?'selected':''}} value="all">{{ translate('messages.All_Customers') }}</option>
                            <option  {{ request()->get('filter')  == 'active'?'selected':''}} value="active">{{ translate('messages.Active_Customers') }}</option>
                            <option  {{ request()->get('filter')  == 'blocked'?'selected':''}} value="blocked">{{ translate('messages.Inactive_Customers') }}</option>
                        </select>
                    </div>
                    <form class="search-form">
                        <!-- Search -->
                        <div class="input-group input--group">
                            <input id="datatableSearch_" type="search" name="search" class="form-control min-height-40"
                                value="{{ request()->get('search') }}" placeholder="{{ translate('ex:_name_email_or_phone') }}"
                                aria-label="Search" >
                            <button type="submit" class="btn btn--secondary min-height-40"><i class="tio-search"></i></button>

                        </div>
                        <!-- End Search -->
                    </form>
                    @if(request()->get('search'))
                    <button type="reset" class="btn btn--primary ml-2 location-reload-to-base" data-url="{{url()->full()}}">{{translate('messages.reset')}}</button>
                    @endif

                    <!-- Unfold -->
                    <div class="hs-unfold mr-2">
                        <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle min-height-40" href="javascript:;"
                            data-hs-unfold-options='{
                                    "target": "#usersExportDropdown",
                                    "type": "css-animation"
                                }'>
                            <i class="tio-download-to mr-1"></i> {{ translate('messages.export') }}
                        </a>

                        <div id="usersExportDropdown"
                            class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">
                            <span class="dropdown-header">{{ translate('messages.options') }}</span>
                            <a id="export-copy" class="dropdown-item" href="javascript:;">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="{{ asset('public/assets/admin') }}/svg/illustrations/copy.svg"
                                    alt="Image Description">
                                {{ translate('messages.copy') }}
                            </a>
                            <a id="export-print" class="dropdown-item" href="javascript:;">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="{{ asset('public/assets/admin') }}/svg/illustrations/print.svg"
                                    alt="Image Description">
                                {{ translate('messages.print') }}
                            </a>
                            <div class="dropdown-divider"></div>
                            <span class="dropdown-header">{{ translate('messages.download_options') }}</span>
                            <a id="export-excel" class="dropdown-item" href="{{route('admin.customer.export', ['type'=>'excel',request()->getQueryString()])}}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="{{ asset('public/assets/admin') }}/svg/components/excel.svg"
                                    alt="Image Description">
                                {{ translate('messages.excel') }}
                            </a>
                            <a id="export-csv" class="dropdown-item" href="{{route('admin.customer.export', ['type'=>'csv',request()->getQueryString()])}}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="{{ asset('public/assets/admin') }}/svg/components/placeholder-csv-format.svg"
                                    alt="Image Description">
                                .{{ translate('messages.csv') }}
                            </a>
                        </div>
                    </div>
                    <!-- End Unfold -->

                    <!-- Unfold -->
                    <div class="hs-unfold">
                        <a class="js-hs-unfold-invoker btn btn-sm btn-white min-height-40" href="javascript:;"
                            data-hs-unfold-options='{
                                    "target": "#showHideDropdown",
                                    "type": "css-animation"
                                }'>
                            <i class="tio-table mr-1"></i> {{ translate('messages.columns') }} <span
                                class="badge badge-soft-dark rounded-circle ml-1"></span>
                        </a>

                        <div id="showHideDropdown"
                            class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-right dropdown-card min--240">
                            <div class="card card-sm">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="mr-2">{{ translate('messages.name') }}</span>

                                        <!-- Checkbox Switch -->
                                        <label class="toggle-switch toggle-switch-sm" for="toggleColumn_name">
                                            <input type="checkbox" class="toggle-switch-input"
                                                id="toggleColumn_name" checked>
                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                        <!-- End Checkbox Switch -->
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="mr-2">{{ translate('messages.contact_information') }}</span>

                                        <!-- Checkbox Switch -->
                                        <label class="toggle-switch toggle-switch-sm" for="toggleColumn_email">
                                            <input type="checkbox" class="toggle-switch-input"
                                                id="toggleColumn_email" checked>
                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                        <!-- End Checkbox Switch -->
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="mr-2">{{ translate('messages.total_order') }}</span>

                                        <!-- Checkbox Switch -->
                                        <label class="toggle-switch toggle-switch-sm"
                                            for="toggleColumn_total_order">
                                            <input type="checkbox" class="toggle-switch-input"
                                                id="toggleColumn_total_order" checked>
                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                        <!-- End Checkbox Switch -->
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="mr-2">{{ translate('messages.active/Inactive') }}</span>

                                        <!-- Checkbox Switch -->
                                        <label class="toggle-switch toggle-switch-sm" for="toggleColumn_status">
                                            <input type="checkbox" class="toggle-switch-input"
                                                id="toggleColumn_status" checked>
                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                        <!-- End Checkbox Switch -->
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="mr-2">{{ translate('messages.actions') }}</span>

                                        <!-- Checkbox Switch -->
                                        <label class="toggle-switch toggle-switch-sm" for="toggleColumn_actions">
                                            <input type="checkbox" class="toggle-switch-input"
                                                id="toggleColumn_actions" checked>
                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                        <!-- End Checkbox Switch -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Unfold -->
                </div>
                <!-- End Row -->
            </div>
            <!-- End Header -->

            <div class="card-body p-0">
                <!-- Table -->
                <div class="table-responsive datatable-custom">
                    <table id="datatable"
                        class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table" data-hs-datatables-options='{
                            "columnDefs": [{
                                "targets": [0],
                                "orderable": false
                            }],
                            "order": [],
                            "info": {
                            "totalQty": "#datatableWithPaginationInfoTotalQty"
                            },
                            "search": "#datatableSearch",
                            "entries": "#datatableEntries",
                            "pageLength": 25,
                            "isResponsive": false,
                            "isShowPaging": false,
                            "paging":false
                        }'>
                        <thead class="thead-light">
                            <tr>
                                <th class="border-0">
                                    {{ translate('sl') }}
                                </th>
                                <th class="table-column-pl-0 border-0">{{ translate('messages.name') }}</th>
                                <th class="border-0">{{ translate('messages.contact_information') }}</th>
                                <th class="border-0">{{ translate('messages.total_order') }}</th>
                                <th class="border-0">{{ translate('messages.total_order_amount') }}</th>
                                <th class="border-0">{{ translate('messages.Joining_date') }}</th>
                                <th class="border-0">{{ translate('messages.active') }}/{{ translate('messages.inactive') }}</th>
                                <th class="border-0">{{ translate('messages.actions') }}</th>
                            </tr>
                        </thead>

                        <tbody id="set-rows">
                            @foreach ($customers as $key => $customer)

                                <tr class="">
                                    <td class="">
                                        {{ $key + $customers->firstItem() }}
                                    </td>
                                    <td class="table-column-pl-0">
                                        <a href="{{ route('admin.users.customer.view', [$customer['id']]) }}" class="text--hover">
                                            {{ $customer['f_name'] . ' ' . $customer['l_name'] }}
                                        </a>
                                    </td>
                                    <td>
                                        <div>
                                            <a href="mailto:{{ $customer['email'] }}">
                                                {{ $customer['email'] }}
                                            </a>
                                        </div>
                                        <div>
                                            <a href="tel:{{ $customer['phone'] }}">
                                                {{ $customer['phone'] }}
                                            </a>
                                        </div>
                                    </td>
                                    <td>
                                        <label class="badge">
                                            {{ $customer->orders_count }}
                                        </label>
                                    </td>
                                    <td>
                                        <label class="badge">
                                            {{  \App\CentralLogics\Helpers::format_currency( $customer->orders()->sum('order_amount'))}}
                                        </label>
                                    </td>
                                    <td>
                                        <label class="badge">
                                            {{  \App\CentralLogics\Helpers::date_format( $customer->created_at)}}
                                        </label>
                                    </td>
                                    <td>
                                        <label class="toggle-switch toggle-switch-sm ml-xl-4" for="stocksCheckbox{{ $customer->id }}">
                                            <input type="checkbox" data-url="{{ route('admin.users.customer.status', [$customer->id, $customer->status ? 0 : 1]) }}" data-message="{{ $customer->status? translate('messages.you_want_to_block_this_customer'): translate('messages.you_want_to_unblock_this_customer') }}"
                                                class="toggle-switch-input status_change_alert" id="stocksCheckbox{{ $customer->id }}"
                                                {{ $customer->status ? 'checked' : '' }}>
                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </td>
                                    <td>
                                        <a class="btn action-btn btn--warning btn-outline-warning"
                                            href="{{ route('admin.users.customer.view', [$customer['id']]) }}"
                                            title="{{ translate('messages.view_customer') }}"><i
                                                class="tio-visible-outlined"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- End Table -->
            </div>

            @if(count($customers) !== 0)
            <hr>
            @endif
            <div class="page-area">
                {!! $customers->withQueryString()->links() !!}
            </div>
            @if(count($customers) === 0)
            <div class="empty--data">
                <img src="{{asset('/public/assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
                <h5>
                    {{translate('no_data_found')}}
                </h5>
            </div>
            @endif

        </div>
        <!-- End Card -->
    </div>
@endsection

@push('script_2')
    <script src="{{asset('public/assets/admin')}}/js/view-pages/customer-list.js"></script>
    <script>
        "use strict";

        $('.status_change_alert').on('click', function (event) {
            let url = $(this).data('url');
            let message = $(this).data('message');
            status_change_alert(url, message, event)
        })

        function status_change_alert(url, message, e) {
            e.preventDefault();
            Swal.fire({
                title: '{{ translate('messages.Are you sure?') }}',
                text: message,
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#FC6A57',
                cancelButtonText: '{{ translate('messages.no') }}',
                confirmButtonText: '{{ translate('messages.Yes') }}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    location.href = url;
                }
            })
        }

    </script>
@endpush
