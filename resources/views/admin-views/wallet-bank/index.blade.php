@extends('layouts.admin.app')

@section('title',translate('messages.Wallet to Bank List'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="page-header">

            <h1 class="page-header-title"><i class="tio-filter-list"></i> {{ $reqPage == 'pending' ? 'New Pending' : '' }}{{ $reqPage == 'approved' ? 'Approved' : '' }}{{ $reqPage == 'rejected' ? 'Rejected' : '' }} requests</h1>
            <div class="page-header-select-wrapper">

{{--                <div class="select-item">--}}
{{--                    <select name="zone_id" class="form-control js-select2-custom set-filter select2-hidden-accessible" data-url="http://127.0.0.1:8000/admin/store/pending-requests" data-filter="zone_id" tabindex="-1" aria-hidden="true" data-select2-id="7">--}}
{{--                        <option value="" selected="" data-select2-id="9">All Zones</option>--}}
{{--                        <option value="1" data-select2-id="10">--}}
{{--                            Demo Zone--}}
{{--                        </option>--}}
{{--                        <option value="2" data-select2-id="11">--}}
{{--                            Lusaka--}}
{{--                        </option>--}}
{{--                        <option value="3" data-select2-id="12">--}}
{{--                            Zambia--}}
{{--                        </option>--}}
{{--                    </select><span class="select2 select2-container select2-container--default" dir="ltr" data-select2-id="8" style="width: 100%;"><span class="selection"><span class="select2-selection custom-select" role="combobox" aria-haspopup="true" aria-expanded="false" tabindex="0" aria-disabled="false" aria-labelledby="select2-zone_id-rf-container"><span class="select2-selection__rendered" id="select2-zone_id-rf-container" role="textbox" aria-readonly="true" title="All Zones"><span>All Zones</span></span><span class="select2-selection__arrow" role="presentation"><b role="presentation"></b></span></span></span><span class="dropdown-wrapper" aria-hidden="true"></span></span>--}}
{{--                </div>--}}
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="js-nav-scroller hs-nav-scroller-horizontal mt-2">
                        <!-- Nav -->
                        <ul class="nav nav-tabs mb-3 border-0 nav--tabs">
                            <li class="nav-item">
                                <a class="nav-link {{ $reqPage == 'pending' ? 'active' : '' }}" href="{{ route('admin.users.customer.wallet.show-wallet-transfer-list', ['status' => 'pending']) }}" aria-disabled="true">Pending Requests</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $reqPage == 'approved' ? 'active' : '' }}" href="{{ route('admin.users.customer.wallet.show-wallet-transfer-list', ['status' => 'approved']) }}" aria-disabled="true">Approved Requests</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $reqPage == 'rejected' ? 'active' : '' }}" href="{{ route('admin.users.customer.wallet.show-wallet-transfer-list', ['status' => 'rejected']) }}" aria-disabled="true">Rejected stores</a>
                            </li>
                        </ul>
                        <!-- End Nav -->
                    </div>
                </div>
            </div>

        </div>

        <div class="py-3">
            <div class="table-responsive">
                <table class="table " id="dataTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User Name</th>
                            <th>Requested Amount</th>
                            <th>Wallet Balance</th>
                            <th>Bank Info</th>
                            <th>Requested At</th>
                            <th>Notes</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transferRequests as $transferRequest)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $transferRequest->user->f_name.' '. $transferRequest->user->f_name ?? '' }}</td>
                                <td>{{ $transferRequest->request_balance ?? 0 }}</td>
                                <td>{{ $transferRequest->user->wallet_balance ?? 0 }}</td>
                                <td>
                                    <p>
                                        Bank Name: {{ $transferRequest->bank_name ?? 'bank Name' }} <br/>
                                        Account Number: {{ $transferRequest->bank_account_number ?? 'Bank Account Number' }} <br/>
                                        Routing Number: {{ $transferRequest->bank_routing_number ?? 'Bank Routing Number' }} <br/>
                                    </p>
                                </td>
                                <td>{{ $transferRequest->created_at->format('d M Y') }}</td>
                                <td>{{ $transferRequest->notes ?? '' }}</td>
                                <td>{{ $transferRequest->payment_status }}</td>
                                <td>
                                    <div class="btn--container">
                                        @if($reqPage == 'rejected' || $reqPage == 'pending')
                                            <a class="btn action-btn btn--primary btn-outline-primary float-right mr-2 request_alert" data-toggle="tooltip" data-placement="top" data-original-title="Approve" data-url="{{ route('admin.users.customer.wallet.change-wallet-to-bank-req-status', ['walletToBank' => $transferRequest->id, 'status' => 'approved']) }}" data-message="You want to approve this Request" href="javascript:"><i class="tio-done font-weight-bold"></i></a>
                                        @endif
                                        @if($reqPage == 'pending')
                                            <a class="btn action-btn btn--danger btn-outline-danger float-right request_alert" data-toggle="tooltip" data-placement="top" data-original-title="Deny" data-url="{{ route('admin.users.customer.wallet.change-wallet-to-bank-req-status', ['walletToBank' => $transferRequest->id, 'status' => 'rejected']) }}" data-message="You want to deny this Request" href="javascript:"><i class="tio-clear font-weight-bold"></i></a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <link rel="stylesheet" href="//cdn.datatables.net/2.1.8/css/dataTables.dataTables.min.css">
    <script src="//cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
    <script>
        $(function () {
            // $('#dataTable').dataTable();
            let table = new DataTable('#dataTable');
        })
    </script>
    <script>
        $('.request_alert').on('click', function (event) {
            let url = $(this).data('url');
            let message = $(this).data('message');
            request_alert(url, message)
        })

        function request_alert(url, message) {
            Swal.fire({
                title: 'Are you sure ?',
                text: message,
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#FC6A57',
                cancelButtonText: 'No',
                confirmButtonText: 'Yes',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    location.href = url;
                }
            })
        }
    </script>
@endpush
