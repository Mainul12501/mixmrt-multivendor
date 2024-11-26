@extends('layouts.admin.app')

@section('title',translate('messages.Disbursement Pending Requests'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title"><i class="tio-filter-list"></i> {{translate('messages.Disbursement Pending Requests')}}</h1>
            <div class="page-header-select-wrapper">

{{--                @if(!isset(auth('admin')->user()->zone_id))--}}
{{--                <div class="select-item">--}}
{{--                    <select name="zone_id" class="form-control js-select2-custom set-filter" data-url="{{url()->full()}}" data-filter="zone_id">--}}
{{--                        <option value="" {{!request('zone_id')?'selected':''}}>{{ translate('messages.All_Zones') }}</option>--}}
{{--                        @foreach(\App\Models\Zone::orderBy('name')->get() as $z)--}}
{{--                            <option--}}
{{--                                    value="{{$z['id']}}" {{isset($zone) && $zone->id == $z['id']?'selected':''}}>--}}
{{--                                {{$z['name']}}--}}
{{--                            </option>--}}
{{--                        @endforeach--}}
{{--                    </select>--}}
{{--                </div>--}}
{{--                @endif--}}
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="js-nav-scroller hs-nav-scroller-horizontal mt-2">
                        <!-- Nav -->
                        <ul class="nav nav-tabs mb-3 border-0 nav--tabs">
                            <li class="nav-item">
                                <a class="nav-link " href="{{ route('admin.store.pending-requests') }}"   aria-disabled="true">{{translate('messages.pending_stores')}}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.store.deny-requests') }}"  aria-disabled="true">{{translate('messages.denied_stores')}}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" href="{{ route('admin.store.pending-method-requests') }}"  aria-disabled="true">{{translate('messages.Disbursement Requests')}}</a>
                            </li>
                        </ul>
                        <!-- End Nav -->
                    </div>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Card -->
        <div class="card">
            <!-- Header -->
{{--            <div class="card-header py-2">--}}
{{--                <div class="search--button-wrapper">--}}
{{--                    <h5 class="card-title">{{translate('messages.stores_list')}} <span class="badge badge-soft-dark ml-2" id="itemCount">{{$stores->total()}}</span></h5>--}}
{{--                    <form action="javascript:" id="search-form" class="search-form">--}}
{{--                    <!-- Search -->--}}
{{--                        @csrf--}}
{{--                        <div class="input-group input--group">--}}
{{--                            <input id="datatableSearch_" type="search" name="search" class="form-control"--}}
{{--                                    placeholder="{{translate('ex_:_Search_Store_Name')}}" value="{{isset($search_by) ? $search_by : ''}}" aria-label="{{translate('messages.search')}}" required>--}}
{{--                            <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>--}}
{{--                        </div>--}}
{{--                    </form>--}}
{{--                    <!-- End Search -->--}}
{{--                </div>--}}
{{--            </div>--}}
            <!-- End Header -->

            <!-- Table -->
            <div class="table-responsive datatable-custom">
                <table id="columnSearchDatatable"
                        class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                        data-hs-datatables-options='{
                            "order": [],
                            "orderCellsTop": true,
                            "paging":false

                        }'>
                    <thead class="thead-light">
                    <tr>
                        <th class="border-0">{{translate('sl')}}</th>
                        <th class="border-0">{{translate('messages.Store Name')}}</th>
                        <th class="border-0">{{translate('messages.Module')}}</th>
                        <th class="border-0">{{translate('messages.Owner Information')}}</th>
                        <th class="border-0">{{translate('messages.Requested Method')}}</th>
                        <th class="border-0">{{translate('messages.zone')}}</th>
                        <th class="text-uppercase border-0">{{translate('messages.status')}}</th>
                    </tr>
                    </thead>

                    <tbody id="set-rows">
                    @foreach($disbursementWithdrawlMethods as $key=>$disbursementWithdrawlMethod)
                        @if(!empty($disbursementWithdrawlMethod->store))
                        <tr>
                            <td>{{$key+$disbursementWithdrawlMethods->firstItem()}}</td>
                            <td>
                                <div>

                                    <a href="{{route('admin.store.view', $disbursementWithdrawlMethod->store_id)}}" class="table-rest-info" alt="view store">
                                        @if(!empty($disbursementWithdrawlMethod->store))
                                            <img class="img--60 circle onerror-image" data-onerror-image="{{asset('public/assets/admin/img/160x160/img1.jpg')}}"
                                            src="{{ \App\CentralLogics\Helpers::get_image_helper(
                                                $disbursementWithdrawlMethod->store,'logo',
                                                asset('storage/app/public/store').'/'.$disbursementWithdrawlMethod->store['logo'] ?? '',
                                                asset('public/assets/admin/img/160x160/img1.jpg'),
                                                'store/'
                                            ) }}" >
                                        @endif
                                        <div class="info"><div class="text--title">
                                            {{Str::limit($disbursementWithdrawlMethod->store_name,20,'...')}}
                                            </div>
                                            <div class="font-light">
                                                {{translate('messages.id')}}:{{$disbursementWithdrawlMethod->store_id ?? 0}}
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </td>
                            <td>
                                <span class="d-block font-size-sm text-body">
                                    @if(!empty($disbursementWithdrawlMethod->store))
                                        {{  Str::limit($disbursementWithdrawlMethod->store->module->module_name,20,'...')}}
                                    @endif
                                </span>
                            </td>
                            <td>
                                <span class="d-block font-size-sm text-body">
                                    @if(!empty($disbursementWithdrawlMethod->store))
                                    {{Str::limit($disbursementWithdrawlMethod->store->vendor->f_name.' '.$disbursementWithdrawlMethod->store->vendor->l_name,20,'...')}}
                                    @endif
                                </span>
                                <div>
                                    @if(!empty($disbursementWithdrawlMethod->store))
                                    {{$disbursementWithdrawlMethod->store['phone'] ?? ''}}
                                    @endif
                                </div>
                            </td>
                            <td>{{ $disbursementWithdrawlMethod->method_name ?? 'Method Name Here' }}</td>
                            <td>
                                @if(!empty($disbursementWithdrawlMethod->store))
                                {{$disbursementWithdrawlMethod->store->zone?$disbursementWithdrawlMethod->store->zone->name:translate('messages.zone_deleted')}}
                                @endif
                            </td>
                            <td>{{ $disbursementWithdrawlMethod->pending_status == 1 ? 'Pending' : 'Accepted' }}</td>

                        </tr>
                        @endif
                    @endforeach
                    </tbody>
                </table>

            </div>
            <!-- End Table -->
                @if(count($disbursementWithdrawlMethods) !== 0)
                <hr>
                @endif
                <div class="page-area">
                    {!! $disbursementWithdrawlMethods->withQueryString()->links() !!}
                </div>
                @if(count($disbursementWithdrawlMethods) === 0)
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
    <script>
        "use strict";
        // $('.status_change_alert').on('click', function (event) {
        //     let url = $(this).data('url');
        //     let message = $(this).data('message');
        //     status_change_alert(url, message, event)
        // })
        function status_change_alert(url, message, e) {
            e.preventDefault();
            Swal.fire({
                title: '{{ translate('Are you sure?') }}' ,
                text: message,
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#FC6A57',
                cancelButtonText: '{{translate('messages.no')}}',
                confirmButtonText: '{{translate('messages.yes')}}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    location.href=url;
                }
            })
        }
        $(document).on('ready', function () {
            // INITIALIZATION OF DATATABLES
            // =======================================================
            let datatable = $.HSCore.components.HSDatatables.init($('#columnSearchDatatable'));

            $('#column1_search').on('keyup', function () {
                datatable
                    .columns(1)
                    .search(this.value)
                    .draw();
            });

            $('#column2_search').on('keyup', function () {
                datatable
                    .columns(2)
                    .search(this.value)
                    .draw();
            });

            $('#column3_search').on('keyup', function () {
                datatable
                    .columns(3)
                    .search(this.value)
                    .draw();
            });

            $('#column4_search').on('keyup', function () {
                datatable
                    .columns(4)
                    .search(this.value)
                    .draw();
            });


            // INITIALIZATION OF SELECT2
            // =======================================================
            $('.js-select2-custom').each(function () {
                let select2 = $.HSCore.components.HSSelect2.init($(this));
            });
        });

        $('.request_alert').on('click', function (event) {
            let url = $(this).data('url');
            let message = $(this).data('message');
            request_alert(url, message)
        })

        function request_alert(url, message) {
            Swal.fire({
                title: '{{translate('messages.are_you_sure')}}',
                text: message,
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#FC6A57',
                cancelButtonText: '{{translate('messages.no')}}',
                confirmButtonText: '{{translate('messages.yes')}}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    location.href = url;
                }
            })
        }

        $('#search-form').on('submit', function () {
            let formData = new FormData(this);
            set_filter('{!! url()->full() !!}',formData.get('search'),'search_by')
        });
    </script>
@endpush
