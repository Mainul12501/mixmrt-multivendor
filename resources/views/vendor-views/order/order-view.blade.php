@extends('layouts.vendor.app')

@section('title', translate('messages.Order Details'))


@section('content')
    <?php

    $tax_included =0;
    if (count($order->details) > 0) {
        $campaign_order = $order->details[0]->campaign ? true : false;
    }
    $reasons=\App\Models\OrderCancelReason::where('status', 1)->where('user_type' ,'vendor' )->get();
    $parcel_order = $order->order_type == 'parcel' ? true : false;
    // $max_processing_time = explode('-', $order['store']['delivery_time'])[0];
    $max_processing_time = $order->store?explode('-', $order->store['delivery_time'])[0]:0;
    $deliverman_tips = 0;
    ?>
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">
                        <span class="page-header-icon">
                            <img src="{{ asset('/public/assets/admin/img/shopping-basket.png') }}" class="w--20"
                                alt="">
                        </span>
                        <span>
                            {{ translate('order_details') }} <span
                                class="badge badge-soft-dark rounded-circle ml-1">{{ $order->details->count() }}</span>
                        </span>
                    </h1>
                </div>

                <div class="col-sm-auto">
                    <a class="btn btn-icon btn-sm btn-soft-secondary rounded-circle mr-1"
                        href="{{ route('vendor.order.details', [$order['id'] - 1]) }}" data-toggle="tooltip"
                        data-placement="top" title="Previous order">
                        <i class="tio-chevron-left"></i>
                    </a>
                    <a class="btn btn-icon btn-sm btn-soft-secondary rounded-circle"
                        href="{{ route('vendor.order.details', [$order['id'] + 1]) }}" data-toggle="tooltip"
                        data-placement="top" title="Next order">
                        <i class="tio-chevron-right"></i>
                    </a>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="row" id="printableArea">
            <div class="col-lg-8 mb-3 mb-lg-0">
                <!-- Card -->
                <div class="card mb-3 mb-lg-5">
                    <!-- Header -->
                    <div class="card-header border-0 align-items-start flex-wrap">
                        <div class="order-invoice-left d-flex d-sm-flex justify-content-between">
                            <div>
                                <h1 class="page-header-title">
                                    {{ translate('messages.order') }} #{{ $order['id'] }}

                                    @if ($order->edited)
                                        <span class="badge badge-soft-danger ml-sm-3">
                                            {{ translate('messages.edited') }}
                                        </span>
                                    @endif
                                </h1>
                                <span class="mt-2 d-block">
                                    <i class="tio-date-range"></i>
                                    {{ date('d M Y ' . config('timeformat'), strtotime($order['created_at'])) }}
                                </span>
                                @if ($order->schedule_at && $order->scheduled)
                                    <h6 class="text-capitalize">
                                        {{ translate('messages.scheduled_at') }}
                                        : <label
                                            class="fz--10 badge badge-soft-warning">{{ date('d M Y ' . config('timeformat'), strtotime($order['schedule_at'])) }}</label>
                                    </h6>
                                @endif
                                @if($order['cancellation_reason'])
                                <h6>
                                    <span class="text-danger">{{ translate('messages.order_cancellation_reason') }} :</span>
                                    {{ $order['cancellation_reason'] }}
                                </h6>
                                @endif
                                @if ($order['unavailable_item_note'])
                                    <h6 class="w-100 badge-soft-warning">
                                        <span class="text-dark">
                                            {{ translate('messages.order_unavailable_item_note') }} :
                                        </span>
                                        {{ $order['unavailable_item_note'] }}
                                    </h6>
                                @endif
                                @if ($order['delivery_instruction'])
                                    <h6 class="w-100 badge-soft-warning">
                                        <span class="text-dark">
                                            {{ translate('messages.order_delivery_instruction') }} :
                                        </span>
                                        {{ $order['delivery_instruction'] }}
                                    </h6>
                                @endif
                                @if ($order['order_note'])
                                    <h6>
                                        {{ translate('messages.order_note') }} :
                                        {{ $order['order_note'] }}
                                    </h6>
                                @endif
                            </div>
                            <div class="d-sm-none">
                                @if(\App\CentralLogics\Helpers::get_store_data()->store_type == 'company')
                                <a class="btn btn--primary print--btn font-regular"
                                    href={{ route('vendor.parcel.generate-invoice', [$order['id']]) }}>
                                    <i class="tio-print mr-sm-1"></i> <span>{{ translate('messages.print_invoice') }}</span>
                                </a>
                                @else
                                <a class="btn btn--primary print--btn font-regular"
                                href={{ route('vendor.order.generate-invoice', [$order['id']]) }}>
                                <i class="tio-print mr-sm-1"></i> <span>{{ translate('messages.print_invoice') }}</span>
                                </a>
                                @endif
                            </div>
                        </div>


                        <div class="order-invoice-right mt-3 mt-sm-0">
                            <div class="btn--container ml-auto align-items-center justify-content-end">
                               @if(\App\CentralLogics\Helpers::get_store_data()->store_type == 'company')
                               <a class="btn btn--primary print--btn font-regular d-none d-sm-block"
                               href={{ route('vendor.parcel.generate-invoice', [$order['id']]) }}>
                               <i class="tio-print mr-sm-1"></i> <span>{{ translate('messages.print_invoice') }}</span>
                           </a>
                                @else
                                <a class="btn btn--primary print--btn font-regular d-none d-sm-block"
                                href={{ route('vendor.order.generate-invoice', [$order['id']]) }}>
                                <i class="tio-print mr-sm-1"></i> <span>{{ translate('messages.print_invoice') }}</span>
                            </a>
                               @endif
                            </div>
                            <div class="text-right mt-3 order-invoice-right-contents text-capitalize">
                                <h6>
                                    {{ translate('messages.payment_status') }} :
                                    @if ($order['payment_status'] == 'paid')
                                        <span class="badge badge-soft-success ml-sm-3">
                                            {{ translate('messages.paid') }}
                                        </span>
                                        @elseif ($order['payment_status'] == 'partially_paid')

                                        @if ($order->payments()->where('payment_status','unpaid')->exists())
                                        <span class="text-danger">{{ translate('messages.partially_paid') }}</span>
                                        @else
                                        <span class="text-success">{{ translate('messages.paid') }}</span>
                                        @endif
                                    @else
                                        <span class="badge badge-soft-danger ml-sm-3">
                                            {{ translate('messages.unpaid') }}
                                        </span>
                                    @endif
                                </h6>
                                @if ($order->store && $order->store->module->module_type == 'food')
                                <h6>
                                    <span>{{ translate('cutlery') }}</span> <span>:</span>
                                    @if ($order['cutlery'] == '1')
                                        <span class="badge badge-soft-success ml-sm-3">
                                            {{ translate('messages.yes') }}
                                        </span>
                                    @else
                                        <span class="badge badge-soft-danger ml-sm-3">
                                            {{ translate('messages.no') }}
                                        </span>
                                    @endif

                                </h6>
                                @endif
                                <h6 class="text-capitalize">
                                    {{ translate('messages.payment_method') }} :
                                    {{ translate(str_replace('_', ' ', $order['payment_method'])) }}
                                </h6>
                                @if ($order['transaction_reference'])
                                    <h6 class="">
                                        {{ translate('messages.reference_code') }} :
                                        <button class="btn btn-outline-primary btn-sm" data-toggle="modal"
                                            data-target=".bd-example-modal-sm">
                                            {{ translate('messages.add') }}
                                        </button>
                                    </h6>
                                @endif
                                <h6 class="text-capitalize">{{ translate('messages.order_type') }}
                                    : <label
                                        class="fz--10 badge m-0 badge-soft-primary">{{ translate(str_replace('_', ' ', $order['order_type'])) }}</label>
                                </h6>
                                <h6>
                                    {{ translate('messages.order_status') }} :
                                    @if ($order['order_status'] == 'pending')
                                        <span class="badge badge-soft-info ml-2 ml-sm-3 text-capitalize">
                                            {{ translate('messages.pending') }}
                                        </span>
                                    @elseif($order['order_status'] == 'confirmed')
                                        <span class="badge badge-soft-info ml-2 ml-sm-3 text-capitalize">
                                            {{ translate('messages.confirmed') }}
                                        </span>
                                    @elseif($order['order_status'] == 'processing')
                                        <span class="badge badge-soft-warning ml-2 ml-sm-3 text-capitalize">
                                            {{ translate('messages.processing') }}
                                        </span>
                                    @elseif($order['order_status'] == 'picked_up')
                                        <span class="badge badge-soft-warning ml-2 ml-sm-3 text-capitalize">
                                            {{ translate('messages.out_for_delivery') }}
                                        </span>
                                    @elseif($order['order_status'] == 'delivered')
                                        <span class="badge badge-soft-success ml-2 ml-sm-3 text-capitalize">
                                            {{ translate('messages.delivered') }}
                                        </span>
                                    @elseif($order['order_status'] == 'failed')
                                        <span class="badge badge-soft-danger ml-2 ml-sm-3 text-capitalize">
                                            {{ translate('messages.payment_failed') }}
                                        </span>
                                    @else
                                        <span class="badge badge-soft-danger ml-2 ml-sm-3 text-capitalize">
                                            {{ str_replace('_', ' ', $order['order_status']) }}
                                        </span>
                                    @endif
                                </h6>
                                @if ($order->order_attachment)
                                    @if ($order->prescription_order)
                                        @php
                                            $order_images = json_decode($order->order_attachment);
                                        @endphp
                                        <h5 class="text-dark">
                                            {{ translate('messages.prescription') }}:
                                        </h5>
                                        <div class="d-flex flex-wrap flex-md-row-reverse __gap-15px" >
                                            @foreach ($order_images as $key => $item)
                                                <div>
                                                    <button class="btn w-100 px-0" data-toggle="modal"
                                                        data-target="#imagemodal{{ $key }}"
                                                        title="{{ translate('messages.order_attachment') }}">
                                                        <div class="gallary-card ml-auto">
                                                            <img src="{{ asset('storage/app/' . 'public/order/' . $item) }}"
                                                                alt="{{ translate('messages.prescription') }}"
                                                                class="initial--22 object-cover">
                                                        </div>
                                                    </button>
                                                </div>
                                                <div class="modal fade" id="imagemodal{{ $key }}" tabindex="-1"
                                                    role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h4 class="modal-title" id="myModalLabel">
                                                                    {{ translate('messages.prescription') }}</h4>
                                                                <button type="button" class="close"
                                                                    data-dismiss="modal"><span
                                                                        aria-hidden="true">&times;</span><span
                                                                        class="sr-only">{{ translate('messages.cancel') }}</span></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <img src="{{ asset('storage/app/' . 'public/order/' . $item) }}"
                                                                    class="initial--22 w-100" alt="image">
                                                            </div>
                                                            <div class="modal-footer">
                                                                <a class="btn btn-primary"
                                                                    href="{{ route('admin.file-manager.download', base64_encode('public/order/' . $item)) }}"><i
                                                                        class="tio-download"></i>
                                                                    {{ translate('messages.download') }}
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                    <h5 class="text-dark">
                                        {{ translate('messages.prescription') }}:
                                    </h5>
                                    <button class="btn w-100 px-0" data-toggle="modal" data-target="#imagemodal"
                                        title="{{ translate('messages.order_attachment') }}">
                                        <div class="gallary-card ml-auto">
                                            <img src="{{ asset('storage/app/' . 'public/order/' . $order->order_attachment) }}"
                                                alt="{{ translate('messages.prescription') }}"
                                                class="initial--22 object-cover">
                                        </div>
                                    </button>
                                    <div class="modal fade" id="imagemodal" tabindex="-1" role="dialog"
                                        aria-labelledby="myModalLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title" id="myModalLabel">
                                                        {{ translate('messages.prescription') }}</h4>
                                                    <button type="button" class="close" data-dismiss="modal"><span
                                                            aria-hidden="true">&times;</span><span
                                                            class="sr-only">{{ translate('messages.cancel') }}</span></button>
                                                </div>
                                                <div class="modal-body">
                                                    <img src="{{ asset('storage/app/' . 'public/order/' . $order->order_attachment) }}"
                                                        class="initial--22 w-100" alt="image">
                                                </div>
                                                <div class="modal-footer">
                                                    <a class="btn btn-primary"
                                                        href="{{ route('admin.file-manager.download', base64_encode('public/order/' . $order->order_attachment)) }}"><i
                                                            class="tio-download"></i> {{ translate('messages.download') }}
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                    <!-- End Header -->

                    <!-- Body -->
                    @if ($order->order_type == 'parcel')
                    <?php
                    $coupon = null;
                    $total_addon_price = 0;
                    $product_price = 0;
                    $store_discount_amount = 0;
                    $admin_flash_discount_amount = $order['flash_admin_discount_amount'];
                    $store_flash_discount_amount = $order['flash_store_discount_amount'];
                    $del_c = $order['delivery_charge'];
                    $additional_charge = $order['additional_charge'];
                    $total_tax_amount = 0;
                    $total_addon_price = 0;
                    $coupon_discount_amount = 0;
                    $deliverman_tips = $order['dm_tips'];
                    ?>

                    <div class="mx-3">
                        <div class="media align-items-center cart--media pb-2">
                            <div class="avatar avatar-xl mr-3"
                                title="{{ $order->parcel_category ? $order->parcel_category->name : translate('messages.parcel_category_not_found') }}">
                                <img class="img-fluid onerror-image"
                                src="{{\App\CentralLogics\Helpers::onerror_image_helper($order->parcel_category ? $order->parcel_category->image : '', asset('storage/app/public/parcel_category').'/'.($order->parcel_category ? $order->parcel_category->image : ''), asset('public/assets/admin/img/160x160/img2.jpg'), 'parcel_category/') }}"
                                    data-onerror-image="{{ asset('public/assets/admin/img/160x160/img2.jpg') }}">
                            </div>

                            <div class="media-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3 mb-md-0">
                                        <strong>
                                            {{ Str::limit($order->parcel_category ? $order->parcel_category->name : translate('messages.parcel_category_not_found'), 25, '...') }}</strong><br>
                                        <div class="font-size-sm text-body">
                                            <span>{{ $order->parcel_category ? $order->parcel_category->description : translate('messages.parcel_category_not_found') }}</span>
                                        </div>
                                    </div>

                                    <div class="col col-md-2 align-self-center">
                                        <h6>{{ translate('messages.distance') }}</h6>
                                        <span>{{ $order->distance }} {{ translate('km') }}</span>
                                    </div>
                                    <div class="col col-md-1 align-self-center">

                                    </div>

                                    <div class="col col-md-3 align-self-center text-right">
                                        <h6>{{ translate('messages.delivery_charge') }}</h6>
                                        <span>{{ \App\CentralLogics\Helpers::format_currency($del_c) }}</span>
                                    </div>

                                </div>

                            </div>
                        </div>

                        <hr class="my-2">
                    </div>
                    @else
                    {{-- <div class="card-body px-0"> --}}
                        <?php
                        $total_addon_price = 0;
                        $product_price = 0;
                        $store_discount_amount = 0;
                        $admin_flash_discount_amount = $order['flash_admin_discount_amount'];
                        $store_flash_discount_amount = $order['flash_store_discount_amount'];

                        if ($order->prescription_order == 1) {
                            $product_price = $order['order_amount'] - $order['delivery_charge'] - $order['total_tax_amount'] - $order['dm_tips'] - $order['additional_charge'] + $order['store_discount_amount'];
                            if($order->tax_status == 'included'){
                                $product_price += $order['total_tax_amount'];
                            }
                        }

                        $total_addon_price = 0;
                        ?>
                        <div class="table-responsive">
                            <table
                                class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table dataTable no-footer mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="border-0">{{ translate('messages.#') }}</th>
                                        <th class="border-0">{{ translate('messages.item_details') }}</th>
                                        @if ($order->store && $order->store->module->module_type == 'food')
                                            <th class="border-0">{{ translate('messages.addons') }}</th>
                                        @endif
                                        <th class="text-right  border-0">{{ translate('messages.price') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($order->details as $key => $detail)
                                        @if (isset($detail->item_id))
                                            @php($detail->item = json_decode($detail->item_details, true))
                                            <!-- Media -->
                                            <tr>
                                                <td>
                                                    <div>
                                                        {{ $key + 1 }}
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="media media--sm">
                                                        <a class="avatar avatar-xl mr-3"
                                                            href="{{ route('vendor.item.view', $detail->item['id']) }}">
                                                            <img class="img-fluid rounded onerror-image"
                                                            src="{{\App\CentralLogics\Helpers::onerror_image_helper($detail->item['image'], asset('storage/app/public/product/').'/'.$detail->item['image'], asset('public/assets/admin/img/160x160/img2.jpg'), 'product/') }}"
                                                                 data-onerror-image="{{ asset('public/assets/admin/img/160x160/img2.jpg') }}"
                                                                alt="Image Description">
                                                        </a>
                                                        <div class="media-body">
                                                            <div>
                                                                <strong
                                                                    class="line--limit-1">{{ Str::limit($detail->item['name'], 25, '...') }}</strong>
                                                                <h6>
                                                                    {{ $detail['quantity'] }} x
                                                                    {{ \App\CentralLogics\Helpers::format_currency($detail['price']) }}
                                                                </h6>
                                                                @if ($order->store && $order->store->module->module_type == 'food')
                                                                    @if (isset($detail['variation']) ? json_decode($detail['variation'], true) : [])
                                                                        @foreach (json_decode($detail['variation'], true) as $variation)
                                                                            @if (isset($variation['name']) && isset($variation['values']))
                                                                                <span class="d-block text-capitalize">
                                                                                    <strong>
                                                                                        {{ $variation['name'] }} -
                                                                                    </strong>
                                                                                </span>
                                                                                @foreach ($variation['values'] as $value)
                                                                                    <span class="d-block text-capitalize">
                                                                                        &nbsp; &nbsp;
                                                                                        {{ $value['label'] }} :
                                                                                        <strong>{{ \App\CentralLogics\Helpers::format_currency($value['optionPrice']) }}</strong>
                                                                                    </span>
                                                                                @endforeach
                                                                            @else
                                                                                @if (isset(json_decode($detail['variation'], true)[0]))
                                                                                    <strong><u>
                                                                                            {{ translate('messages.Variation') }}
                                                                                            : </u></strong>
                                                                                    @foreach (json_decode($detail['variation'], true)[0] as $key1 => $variation)
                                                                                        <div
                                                                                            class="font-size-sm text-body">
                                                                                            <span>{{ $key1 }}
                                                                                                : </span>
                                                                                            <span
                                                                                                class="font-weight-bold">{{ $variation }}</span>
                                                                                        </div>
                                                                                    @endforeach
                                                                                @endif
                                                                                {{-- @break --}}
                                                                            @endif
                                                                        @endforeach
                                                                    @endif
                                                                @else
                                                                    @if (count(json_decode($detail['variation'], true)) > 0)
                                                                        <strong><u>{{ translate('messages.variation') }} :
                                                                            </u></strong>
                                                                        @foreach (json_decode($detail['variation'], true)[0] as $key1 => $variation)
                                                                            @if ($key1 != 'stock' || ($order->store && config('module.' . $order->store->module->module_type)['stock']))
                                                                                <div class="font-size-sm text-body">
                                                                                    <span>{{ $key1 }} : </span>
                                                                                    <span
                                                                                        class="font-weight-bold">{{ Str::limit($variation, 20, '...') }}</span>
                                                                                </div>
                                                                            @endif
                                                                        @endforeach
                                                                    @endif
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                @if ($order->store->module->module_type == 'food')
                                                    <td>
                                                        <div>
                                                            @foreach (json_decode($detail['add_ons'], true) as $key2 => $addon)
                                                                @if ($key2 == 0)
                                                                    <strong><u>{{ translate('messages.addons') }} :
                                                                        </u></strong>
                                                                @endif
                                                                <div class="font-size-sm text-body">
                                                                    <span>{{ Str::limit($addon['name'], 25, '...') }} :
                                                                    </span>
                                                                    <span class="font-weight-bold">
                                                                        {{ $addon['quantity'] }} x
                                                                        {{ \App\CentralLogics\Helpers::format_currency($addon['price']) }}
                                                                    </span>
                                                                </div>
                                                                @php($total_addon_price += $addon['price'] * $addon['quantity'])
                                                            @endforeach
                                                        </div>
                                                    </td>
                                                @endif
                                                <td>
                                                    <div class="text-right">
                                                        @php($amount = $detail['price'] * $detail['quantity'])
                                                        <h5>{{ \App\CentralLogics\Helpers::format_currency($amount) }}</h5>
                                                    </div>
                                                </td>
                                            </tr>
                                            @php($product_price += $amount)
                                            @php($store_discount_amount += $detail['discount_on_item'] * $detail['quantity'])
                                            <!-- End Media -->
                                        @elseif(isset($detail->item_campaign_id))
                                            @php($detail->campaign = json_decode($detail->item_details, true))
                                            <!-- Media -->
                                            <tr>
                                                <td>
                                                    <div>
                                                        {{ $key + 1 }}
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="media media--sm">
                                                        <div class="avatar avatar-xl mr-3">
                                                            <img class="img-fluid onerror-image"
                                                            src="{{\App\CentralLogics\Helpers::onerror_image_helper($detail->campaign['image'], asset('storage/app/public/campaign/').'/'.$detail->campaign['image'], asset('public/assets/admin/img/160x160/img2.jpg'), 'campaign/') }}"

                                                                 data-onerror-image="{{ asset('public/assets/admin/img/160x160/img2.jpg') }}"
                                                                alt="Image Description">
                                                        </div>
                                                        <div class="media-body">
                                                            <div>
                                                                <strong
                                                                    class="line--limit-1">{{ Str::limit($detail->campaign['name'], 25, '...') }}</strong>

                                                                <h6>
                                                                    {{ $detail['quantity'] }} x
                                                                    {{ \App\CentralLogics\Helpers::format_currency($detail['price']) }}
                                                                </h6>

                                                                @if (count(json_decode($detail['variation'], true)) > 0)
                                                                    <strong><u>{{ translate('messages.variation') }} :
                                                                        </u></strong>
                                                                    @foreach (json_decode($detail['variation'], true)[0] as $key1 => $variation)
                                                                        @if ($key1 != 'stock')
                                                                            <div class="font-size-sm text-body">
                                                                                <span>{{ $key1 }} : </span>
                                                                                <span
                                                                                    class="font-weight-bold">{{ Str::limit($variation, 25, '...') }}</span>
                                                                            </div>
                                                                        @endif
                                                                    @endforeach
                                                                @endif

                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                @if ($order->store->module->module_type == 'food')
                                                    <td>
                                                        @foreach (json_decode($detail['add_ons'], true) as $key2 => $addon)
                                                            @if ($key2 == 0)
                                                                <strong><u>{{ translate('messages.addons') }} :
                                                                    </u></strong>
                                                            @endif
                                                            <div class="font-size-sm text-body">
                                                                <span>{{ Str::limit($addon['name'], 20, '...') }} : </span>
                                                                <span class="font-weight-bold">
                                                                    {{ $addon['quantity'] }} x
                                                                    {{ \App\CentralLogics\Helpers::format_currency($addon['price']) }}
                                                                </span>
                                                            </div>
                                                            @php($total_addon_price += $addon['price'] * $addon['quantity'])
                                                        @endforeach
                                                    </td>
                                                @endif
                                                <td>
                                                    <div class="text-right">
                                                        @php($amount = $detail['price'] * $detail['quantity'])
                                                        <h5>{{ \App\CentralLogics\Helpers::format_currency($amount) }}</h5>
                                                    </div>
                                                </td>
                                            </tr>
                                            @php($product_price += $amount)
                                            @php($store_discount_amount += $detail['discount_on_item'] * $detail['quantity'])
                                            <!-- End Media -->
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mx-3">
                            <hr>
                        </div>
                        <?php

                        $coupon_discount_amount = $order['coupon_discount_amount'];

                        $total_price = $product_price + $total_addon_price - $store_discount_amount - $coupon_discount_amount - $admin_flash_discount_amount -$store_flash_discount_amount;

                        $total_tax_amount = $order['total_tax_amount'];
                        if($order->tax_status == 'included'){
                                $total_tax_amount=0;
                            }
                        $tax_included = \App\Models\BusinessSetting::where(['key'=>'tax_included'])->first() ?  \App\Models\BusinessSetting::where(['key'=>'tax_included'])->first()->value : 0;

                        $store_discount_amount = $order['store_discount_amount'];

                        ?>
                        @endif
                        <div class="row justify-content-md-end mb-3 mx-0 mt-4">
                            <div class="col-md-9 col-lg-8">
                                <dl class="row text-right">
                                    {{-- <dt class="col-6">{{ translate('messages.items_price') }}:</dt>
                                    <dd class="col-6">{{ \App\CentralLogics\Helpers::format_currency($product_price) }}
                                    </dd> --}}
                                    {{-- @if ($order->store && $order->store->module->module_type == 'food')
                                        <dt class="col-6">{{ translate('messages.addon_cost') }}:</dt>

                                        <dd class="col-6">
                                            {{ \App\CentralLogics\Helpers::format_currency($total_addon_price) }}
                                            <hr>
                                        </dd>
                                    @endif --}}

                                    {{-- <dt class="col-6">{{ translate('messages.subtotal') }}
                                        @if ($order->tax_status == 'included' ||  $tax_included ==  1)
                                        ({{ translate('messages.TAX_Included') }})
                                        @endif
                                        :</dt> --}}

                                    {{-- <dd class="col-6">
                                        @if ($order->prescription_order == 1 && in_array($order['order_status'],['pending','confirmed','processing','accepted']))
                                            <button class="btn btn-sm" type="button" data-toggle="modal"
                                                data-target="#edit-order-amount"><i class="tio-edit"></i></button>
                                        @endif
                                        {{ \App\CentralLogics\Helpers::format_currency($product_price + $total_addon_price) }}
                                    </dd> --}}
                                    {{-- <dt class="col-6">{{ translate('messages.discount') }}:</dt> --}}
                                    {{-- <dd class="col-6">
                                        @if ($order->prescription_order == 1 && in_array($order['order_status'],['pending','confirmed','processing','accepted']))
                                            <button class="btn btn-sm" type="button" data-toggle="modal"
                                                data-target="#edit-discount-amount"><i class="tio-edit"></i></button>
                                        @endif
                                        - {{ \App\CentralLogics\Helpers::format_currency($store_discount_amount + $admin_flash_discount_amount +$store_flash_discount_amount) }}
                                    </dd> --}}
                                    {{-- <dt class="col-6">{{ translate('messages.coupon_discount') }}:</dt>
                                    <dd class="col-6">
                                        - {{ \App\CentralLogics\Helpers::format_currency($coupon_discount_amount) }}</dd>
                                        @if ($order->tax_status == 'excluded' || $order->tax_status == null  )
                                        <dt class="col-sm-6">{{ translate('messages.vat/tax') }}:</dt>
                                        <dd class="col-sm-6">
                                            +
                                            {{ \App\CentralLogics\Helpers::format_currency($total_tax_amount) }}
                                        </dd>
                                        @endif --}}


                                        @if (!$parcel_order)
                                        <dt class="col-6">{{ translate('messages.items_price') }}:</dt>
                                        <dd class="col-6">
                                            {{ \App\CentralLogics\Helpers::format_currency($product_price) }}</dd>
                                        @if ($order->store && $order->store->module->module_type == 'food')
                                            <dt class="col-6">{{ translate('messages.addon_cost') }}:</dt>
                                            <dd class="col-6">
                                                {{ \App\CentralLogics\Helpers::format_currency($total_addon_price) }}
                                                <hr>
                                            </dd>
                                        @endif

                                        <dt class="col-6">{{ translate('messages.subtotal') }}
                                            @if ($order->tax_status == 'included' ||  $tax_included ==  1)
                                            ({{ translate('messages.TAX_Included') }})
                                            @endif
                                            :</dt>
                                        <dd class="col-6">
                                            @if ($order->prescription_order == 1 && in_array($order['order_status'],['pending','confirmed','processing','accepted']))
                                            <button class="btn btn-sm" type="button" data-toggle="modal"
                                                data-target="#edit-order-amount"><i class="tio-edit"></i></button>
                                            @endif
                                            {{ \App\CentralLogics\Helpers::format_currency($product_price + $total_addon_price) }}
                                        </dd>
                                        <dt class="col-6">{{ translate('messages.discount') }}:</dt>
                                        <dd class="col-6">
                                            @if ($order->prescription_order == 1 && in_array($order['order_status'],['pending','confirmed','processing','accepted']))
                                            <button class="btn btn-sm" type="button" data-toggle="modal"
                                                data-target="#edit-discount-amount"><i class="tio-edit"></i></button>
                                        @endif

                                            - {{ \App\CentralLogics\Helpers::format_currency($store_discount_amount + $admin_flash_discount_amount + $store_flash_discount_amount) }}
                                        </dd>
                                        <dt class="col-6">{{ translate('messages.coupon_discount') }}:</dt>
                                        <dd class="col-6">
                                            - {{ \App\CentralLogics\Helpers::format_currency($coupon_discount_amount) }}
                                        </dd>
                                        @if ($order->tax_status == 'excluded' || $order->tax_status == null  )
                                        {{-- @php($tax_a=0) --}}
                                        <dt class="col-6">{{ translate('messages.vat/tax') }}:</dt>
                                        <dd class="col-6 text-right">
                                            +
                                            {{ \App\CentralLogics\Helpers::format_currency($total_tax_amount) }}
                                        </dd>
                                        @endif
                                        <dt class="col-6">{{translate('messages.delivery_fee')}} {{"( " . $order->weight . "kg)"}} :</dt>
                                        <dd class="col-6">
                                            @php($del_c = $order['delivery_charge'])
                                            + {{ \App\CentralLogics\Helpers::format_currency($del_c) }}
                                            <hr>
                                        </dd>
                                    @endif


                                    <dt class="col-6">{{ translate('messages.delivery_man_tips') }}</dt>
                                    <dd class="col-6">
                                        + {{ \App\CentralLogics\Helpers::format_currency($order->dm_tips) }}</dd>
                                    {{-- <dt class="col-6">{{ translate('messages.delivery_fee') }}:</dt>
                                    <dd class="col-6">
                                        @php($del_c = $order['delivery_charge'])
                                        + {{ \App\CentralLogics\Helpers::format_currency($del_c) }}
                                        <hr>
                                    </dd> --}}
                                    <dt class="col-6">{{ \App\CentralLogics\Helpers::get_business_data('additional_charge_name')??translate('messages.additional_charge') }}:</dt>
                                    <dd class="col-6">
                                        @php($additional_charge = $order['additional_charge'])
                                        + {{ \App\CentralLogics\Helpers::format_currency($additional_charge) }}
                                    </dd>
                                    @if($parcel_order)
                                    <dt class="col-6">{{ translate('messages.weight') }}</dt>
                                    <dd class="col-6">
                                        {{$order->weight . " kg"}}</dd>
                                    @endif
                                    @if ($order['partially_paid_amount'] > 0)

                                    <dt class="col-6">{{ translate('messages.partially_paid_amount') }}:</dt>
                                    <dd class="col-6">
                                        @php($partially_paid_amount = $order['partially_paid_amount'])
                                            {{ \App\CentralLogics\Helpers::format_currency($partially_paid_amount) }}
                                    </dd>
                                    <dt class="col-6">{{ translate('messages.due_amount') }}:</dt>
                                    @if ($order['payment_method'] == 'partial_payment')

                                    <dd class="col-6">
                                            {{ \App\CentralLogics\Helpers::format_currency($order->order_amount-$partially_paid_amount) }}
                                    </dd>
                                    @else
                                    <dd class="col-6">
                                            {{ \App\CentralLogics\Helpers::format_currency(0) }}
                                    </dd>
                                    @endif
                                    @endif

                                    <dt class="col-6">{{ translate('messages.total') }}:</dt>
                                    <dd class="col-6">
                                        {{ \App\CentralLogics\Helpers::format_currency($product_price + $del_c + $total_tax_amount + $total_addon_price + $additional_charge - $coupon_discount_amount - $store_discount_amount - $admin_flash_discount_amount -$store_flash_discount_amount + $order->dm_tips) }}
                                    </dd>
                                    @if ($order?->payments)
                                        @foreach ($order?->payments as $payment)
                                            @if ($payment->payment_status == 'paid')
                                                @if ( $payment->payment_method == 'cash_on_delivery')

                                                <dt class="col-sm-6">{{ translate('messages.Paid_with_Cash') }} ({{  translate('COD')}}) :</dt>
                                                @else

                                                <dt class="col-sm-6">{{ translate('messages.Paid_by') }} {{  translate($payment->payment_method)}} :</dt>
                                                @endif
                                            @else

                                            <dt class="col-sm-6">{{ translate('Due_Amount') }} ({{  $payment->payment_method == 'cash_on_delivery' ?  translate('messages.COD') : translate($payment->payment_method) }}) :</dt>
                                            @endif
                                        <dd class="col-sm-6">
                                            {{ \App\CentralLogics\Helpers::format_currency($payment->amount) }}
                                        </dd>
                                        @endforeach
                                    @endif
                                </dl>
                                <!-- End Row -->
                            </div>
                        </div>
                        <!-- End Row -->
                    {{-- </div> --}}

                    <!-- End Body -->
                </div>
                <!-- End Card -->
            </div>

            <div class="col-lg-4">
                <!-- Card -->
                @if ($order->order_status != 'refund_requested' &&
                    $order->order_status != 'refunded' &&
                    $order->order_status != 'delivered')
                    <div class="card mb-2">
                        <!-- Header -->
                        <div class="card-header justify-content-center text-center px-0 mx-4">
                            <h5 class="card-header-title text-capitalize">
                                <span>{{ translate('messages.order_setup') }}</span>
                            </h5>
                        </div>
                        <!-- End Header -->

                        <!-- Body -->
            
                        @if($order->confirmed && !$order->parcel_company && \App\CentralLogics\Helpers::get_store_data()->store_type == 'company' || (!$order->confirmed && \App\CentralLogics\Helpers::get_store_data()->store_type == 'company'))
                        @if ($order->store && $order->store->module->module_type == 'food')
                        <a class="btn btn--primary w-100 order-status-change-alert"

                           data-url="{{ route('vendor.order.status', ['id' => $order['id'], 'order_status' => 'confirmed']) }}"
                           data-message="{{ translate('Change status to cooking ?') }}"
                           data-verification="false"
                           data-processing-time="{{ $max_processing_time }}"
                           href="javascript:">{{ translate('messages.confirm_order') }}</a>
                        @else
                        <a class="btn btn--primary w-100 route-alert"
                        data-url="{{ route('vendor.order.status', ['id' => $order['id'], 'order_status' => 'confirmed']) }}"
                        data-message="{{ translate('messages.confirm_order') }}"
                        href="javascript:">{{ translate('messages.confirm_order') }}</a>
                        @endif
                        @endif
                        @if ($order->order_type == 'parcel' && \App\CentralLogics\Helpers::get_store_data()->store_type == 'company' && $order->confirmed)
                         <div class="card-body">
                            <div class="hs-unfold w-100">
                                <div class="dropdown">
                                    <button
                                        class="form-control h--45px dropdown-toggle d-flex justify-content-between align-items-center w-100"
                                        type="button" id="dropdownMenuButton" data-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false">
                                        <?php
                                        $message= match($order['order_status']){
                                                        'pending' => translate('messages.pending'),
                                                        'confirmed' => translate('messages.confirmed'),
                                                        'accepted' => translate('messages.accepted'),
                                                        'processing' => translate('messages.processing'),
                                                        'handover' => translate('messages.handover'),
                                                        'picked_up' => translate('messages.out_for_delivery'),
                                                        'delivered' => translate('messages.delivered'),
                                                        'canceled' => translate('messages.canceled'),
                                                        default => translate('messages.status') ,
                                                    };
                                    ?>
                                    {{ $message }}
                                    </button>
                                    @php($order_delivery_verification = (bool) \App\Models\BusinessSetting::where(['key' => 'order_delivery_verification'])->first()->value)
                                    <div class="dropdown-menu text-capitalize" aria-labelledby="dropdownMenuButton">
                                            <a class="dropdown-item {{ $order['order_status'] == 'pending' ? 'active' : '' }} route-alert"
                                               data-url="{{ route('vendor.parcel.status', ['id' => $order['id'], 'order_status' => 'pending']) }}" data-message="{{ translate('Change status to pending ?') }}"
                                                href="javascript:">{{ translate('messages.pending') }}</a>
                                            <a class="dropdown-item {{ $order['order_status'] == 'confirmed' ? 'active' : '' }} route-alert"
                                               data-url="{{ route('vendor.parcel.status', ['id' => $order['id'], 'order_status' => 'confirmed']) }}" data-message="{{ translate('Change status to confirmed ?') }}"
                                                href="javascript:">{{ translate('messages.confirmed') }}</a>
                                            @if ($order->order_type != 'parcel')
                                                @if ($order->store && $order->store->module->module_type == 'food')
                                                <a class="dropdown-item {{ $order['order_status'] == 'processing' ? 'active' : '' }} order_status_change_alert" data-url="{{ route('vendor.parcel.status', ['id' => $order['id'], 'order_status' => 'processing']) }}" data-message="{{ translate('Change status to cooking ?') }}" data-processing={{ $max_processing_time }}
                                                    href="javascript:">{{ translate('messages.processing') }}</a>
                                                @else
                                                <a class="dropdown-item {{ $order['order_status'] == 'processing' ? 'active' : '' }} route-alert"
                                                   data-url="{{ route('vendor.parcel.status', ['id' => $order['id'], 'order_status' => 'processing']) }}" data-message="{{ translate('Change status to processing ?') }}"
                                                    href="javascript:">{{ translate('messages.processing') }}</a>
                                                @endif
                                                <a class="dropdown-item {{ $order['order_status'] == 'handover' ? 'active' : '' }} route-alert"
                                                   data-url="{{ route('vendor.parcel.status', ['id' => $order['id'], 'order_status' => 'handover']) }}" data-message="{{ translate('Change status to handover ?') }}"
                                                    href="javascript:">{{ translate('messages.handover') }}</a>
                                            @endif
                                            <a class="dropdown-item {{ $order['order_status'] == 'picked_up' ? 'active' : '' }} route-alert"
                                               data-url="{{ route('vendor.parcel.status', ['id' => $order['id'], 'order_status' => 'picked_up']) }}" data-message="{{ translate('Change status to out for delivery ?') }}"
                                                href="javascript:">{{ translate('messages.out_for_delivery') }}</a>
                                            <a class="dropdown-item {{ $order['order_status'] == 'delivered' ? 'active' : '' }} route-alert"
                                               data-url="{{ route('vendor.parcel.status', ['id' => $order['id'], 'order_status' => 'delivered']) }}" data-message="{{ translate('Change status to delivered (payment status will be paid if not)?') }}"
                                                href="javascript:">{{ translate('messages.delivered') }}</a>
                                            <a class="dropdown-item {{ $order['order_status'] == 'canceled' ? 'active' : '' }} route-alert"
                                               data-url="{{ route('vendor.parcel.status', ['id' => $order['id'], 'order_status' => 'canceled']) }}" data-message="{{ translate('Change status to canceled?') }}"
                                                href="javascript:">{{ translate('messages.canceled') }}</a>
                                            {{-- <a class="dropdown-item {{ $order['order_status'] == 'canceled' ? 'active' : '' }} canceled-status">{{ translate('messages.canceled') }}</a> --}}
                                        </div>

                                </div>
                            </div>
                         </div>
                         @endif
                           @if($order->order_type !== 'parcel' && \App\CentralLogics\Helpers::get_store_data()->store_type == 'company' && $order->parcel_company)
                           <div class="card-body">
                            <!-- Order Status Flow Starts -->
                            @php($order_delivery_verification = (bool) \App\Models\BusinessSetting::where(['key' => 'order_delivery_verification'])->first()->value)
                            <div class="mb-4">
                                <div class="row g-1">
                                    <div class="{{ config('canceled_by_store') ? 'col-6' : 'col-12' }}">
                                        <a class="btn btn--primary w-100 fz--13 px-2 {{ $order['order_status'] == 'pending' ? '' : 'd-none' }} route-alert"
                                           data-url="{{ route('vendor.order.status', ['id' => $order['id'], 'order_status' => 'confirmed']) }}"
                                           data-message="{{ translate('messages.confirm_this_order_?') }}"
                                            href="javascript:">{{ translate('messages.confirm_this_order') }}</a>
                                    </div>
                                    @if (config('canceled_by_store'))
                                        <div class="col-6">
                                            <a class="btn btn--danger w-100 fz--13 px-2 cancelled-status {{ $order['order_status'] == 'pending' ? '' : 'd-none' }}"
                                               >{{ translate('Cancel Order') }}</a>
                                        </div>
                                    @endif
                                </div>
                                    @if ($order->store && $order->store->module->module_type == 'food')
                                        <a class="btn btn--primary w-100 order-status-change-alert {{ $order['order_status'] == 'confirmed' || $order['order_status'] == 'accepted' ? '' : 'd-none' }}"

                                           data-url="{{ route('vendor.order.status', ['id' => $order['id'], 'order_status' => 'processing']) }}"
                                           data-message="{{ translate('Change status to cooking ?') }}"
                                           data-verification="false"
                                           data-processing-time="{{ $max_processing_time }}"
                                           href="javascript:">{{ translate('messages.proceed_for_processing') }}</a>
                                    @else
                                    <a class="btn btn--primary w-100 route-alert  {{ $order['order_status'] == 'confirmed' || $order['order_status'] == 'accepted' ? '' : 'd-none' }}"
                                       data-url="{{ route('vendor.order.status', ['id' => $order['id'], 'order_status' => 'processing']) }}"
                                       data-message="{{ translate('messages.proceed_for_processing') }}"
                                    href="javascript:">{{ translate('messages.proceed_for_processing') }}</a>
                                    @endif
                                <a class="btn btn--primary w-100 route-alert {{ $order['order_status'] == 'processing' ? '' : 'd-none' }}"
                                   data-url="{{ route('vendor.order.status', ['id' => $order['id'], 'order_status' => 'handover']) }}"
                                   data-message="{{ translate('messages.make_ready_for_handover') }}"
                                    href="javascript:">{{ translate('messages.make_ready_for_handover') }}</a>
                                 @if($order['order_status'] == 'handover')
                                    <a class="btn  w-100
                                    {{ ($order['order_type'] == 'take_away' || $order->store->self_delivery_system == 1)  ?  'btn--primary order-status-change-alert'  :  (\App\CentralLogics\Helpers::get_store_data()->store_type == 'company' ? 'btn--primary order-status-change-alert' : 'btn--secondary  self-delivery-warning') }} "
                                       data-url="{{ route('vendor.order.status', ['id' => $order['id'], 'order_status' => 'delivered']) }}"
                                       data-message="{{ translate('messages.Change status to delivered (payment status will be paid if not)?') }}"
                                       data-verification="{{ $order_delivery_verification ? 'true' : 'false' }}"
                                        href="javascript:">{{ translate('messages.make_delivered') }}</a>
                                 @endif

                            </div>
                        </div>
                        @elseif($order->order_type !== 'parcel' && \App\CentralLogics\Helpers::get_store_data()->store_type == 'store')
                        <div class="card-body">
                            <!-- Order Status Flow Starts -->
                            @php($order_delivery_verification = (bool) \App\Models\BusinessSetting::where(['key' => 'order_delivery_verification'])->first()->value)
                            <div class="mb-4">
                                <div class="row g-1">
                                    <div class="{{ config('canceled_by_store') ? 'col-6' : 'col-12' }}">
                                        <a class="btn btn--primary w-100 fz--13 px-2 {{ $order['order_status'] == 'pending' ? '' : 'd-none' }} route-alert"
                                           data-url="{{ route('vendor.order.status', ['id' => $order['id'], 'order_status' => 'confirmed']) }}"
                                           data-message="{{ translate('messages.confirm_this_order_?') }}"
                                            href="javascript:">{{ translate('messages.confirm_this_order') }}</a>
                                    </div>
                                    @if (config('canceled_by_store'))
                                        <div class="col-6">
                                            <a class="btn btn--danger w-100 fz--13 px-2 cancelled-status {{ $order['order_status'] == 'pending' ? '' : 'd-none' }}"
                                               >{{ translate('Cancel Order') }}</a>
                                        </div>
                                    @endif
                                </div>
                                    @if ($order->store && $order->store->module->module_type == 'food')
                                        <a class="btn btn--primary w-100 order-status-change-alert {{ $order['order_status'] == 'confirmed' || $order['order_status'] == 'accepted' ? '' : 'd-none' }}"

                                           data-url="{{ route('vendor.order.status', ['id' => $order['id'], 'order_status' => 'processing']) }}"
                                           data-message="{{ translate('Change status to cooking ?') }}"
                                           data-verification="false"
                                           data-processing-time="{{ $max_processing_time }}"
                                           href="javascript:">{{ translate('messages.proceed_for_processing') }}</a>
                                    @else
                                    <a class="btn btn--primary w-100 route-alert  {{ $order['order_status'] == 'confirmed' || $order['order_status'] == 'accepted' ? '' : 'd-none' }}"
                                       data-url="{{ route('vendor.order.status', ['id' => $order['id'], 'order_status' => 'processing']) }}"
                                       data-message="{{ translate('messages.proceed_for_processing') }}"
                                    href="javascript:">{{ translate('messages.proceed_for_processing') }}</a>
                                    @endif
                                <a class="btn btn--primary w-100 route-alert {{ $order['order_status'] == 'processing' ? '' : 'd-none' }}"
                                   data-url="{{ route('vendor.order.status', ['id' => $order['id'], 'order_status' => 'handover']) }}"
                                   data-message="{{ translate('messages.make_ready_for_handover') }}"
                                    href="javascript:">{{ translate('messages.make_ready_for_handover') }}</a>
                                 @if($order['order_status'] == 'handover')
                                    <a class="btn  w-100
                                    {{ ($order['order_type'] == 'take_away' || $order->store->self_delivery_system == 1)  ?  'btn--primary order-status-change-alert'  :  'btn--secondary  self-delivery-warning' }} "
                                       data-url="{{ route('vendor.order.status', ['id' => $order['id'], 'order_status' => 'delivered']) }}"
                                       data-message="{{ translate('messages.Change status to delivered (payment status will be paid if not)?') }}"
                                       data-verification="{{ $order_delivery_verification ? 'true' : 'false' }}"
                                        href="javascript:">{{ translate('messages.make_delivered') }}</a>
                                 @endif

                            </div>
                        </div>
                        @endif

                        <!-- End Body -->
                    </div>
                @endif
                <!-- End Card -->
                @if ($order->order_status == 'canceled')
                <ul class="delivery--information-single mt-3">
                    <li>
                        <span class=" badge badge-soft-danger "> {{ translate('messages.Cancel_Reason') }} :</span>
                        <span class="info">  {{ $order->cancellation_reason }} </span>
                    </li>

                    <li>
                        <span class="name">{{ translate('Cancel_Note') }} </span>
                        <span class="info">  {{ $order->cancellation_note ?? translate('messages.N/A')}} </span>
                    </li>
                    <li>
                        <span class="name">{{ translate('Canceled_By') }} </span>
                        <span class="info">  {{ translate($order->canceled_by) }} </span>
                    </li>
                    @if ($order->payment_status == 'paid' || $order->payment_status == 'partially_paid' )
                            @if ( $order?->payments)
                                @php( $pay_infos =$order->payments()->where('payment_status','paid')->get())
                                @foreach ($pay_infos as $pay_info)
                                    <li>
                                        <span class="name">{{ translate('Amount_paid_by') }} {{ translate($pay_info->payment_method) }} </span>
                                        <span class="info">  {{ \App\CentralLogics\Helpers::format_currency($pay_info->amount)  }} </span>
                                    </li>
                                @endforeach
                            @else
                            <li>
                                <span class="name">{{ translate('Amount_paid_by') }} {{ translate($order->payment_method) }} </span>
                                <span class="info ">  {{ \App\CentralLogics\Helpers::format_currency($order->order_amount)  }} </span>
                            </li>
                            @endif
                    @endif

                    @if ($order->payment_status == 'paid' || $order->payment_status == 'partially_paid')
                        @if ( $order?->payments)
                            @php( $amount =$order->payments()->where('payment_status','paid')->sum('amount'))
                                <li>
                                    <span class="name">{{ translate('Amount_Returned_To_Wallet') }} </span>
                                    <span class="info">  {{ \App\CentralLogics\Helpers::format_currency($amount)  }} </span>
                                </li>
                        @else
                        <li>
                            <span class="name">{{ translate('Amount_Returned_To_Wallet') }} </span>
                            <span class="info">  {{ \App\CentralLogics\Helpers::format_currency($order->order_amount)  }} </span>
                        </li>
                        @endif
                    @endif
                </ul>
                <hr class="w-100">
            @endif

                @if (!in_array($order->order_status, [ 'refunded','delivered', 'canceled']) &&  ( !$order->delivery_man && $order['order_type'] != 'take_away' && (($order->parcel_company && $order?->parcel_company?->self_parcel_delivery) && !$order->delivery_company)))
                <div class="card p-1">
                <div class="w-100 text-center mt-3">
                    <button type="button" class="btn btn--primary w-100" data-toggle="modal"
                        data-target="#myModal" data-lat='21.03' data-lng='105.85'>
                        {{ translate('messages.assign_delivery_man_manually') }}
                    </button>
                </div>
                </div>
                @endif
                @if (!in_array($order->order_status, [ 'refunded','delivered', 'canceled']) &&  ( !$order->delivery_man && $order['order_type'] != 'take_away' && (($order->parcel_company && $order?->parcel_company?->self_parcel_delivery))))

                <div class="card p-1 mt-2">
                <div class="w-100 text-center mt-3">
                    <button type="button" class="btn btn--warning w-100" data-toggle="modal"
                        data-target="#thirdPartyModal">
                        {{ translate('messages.Third_party_company') }}
                    </button>
                </div>
                
                   <!-- Modal -->
                   <div class="modal fade" id="thirdPartyModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title" id="exampleModalLabel">{{translate('messages.Third_party_company')}}</h5>
                          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                          </button>
                        </div>
                        <form action="{{route('vendor.parcel.third-party-company')}}">
                            @csrf
                        <div class="modal-body">
                        <div class="card-body">
                            <div class="row g-3 my-0">
                                <div class="col-md-12">
                                    <div class="form-group mb-0">
                                        <label class="input-label" for="order_id">{{translate('messages.order_id')}}</label>
                                        <input readonly type="number" name="order_id" id="order_id" class="form-control" placeholder="{{translate('messages.order_id')}}" value={{$order->id}}>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group mb-0">
                                        <label class="input-label" for="company_name">{{translate('messages.company_name')}}</label>
                                        <input type="text" name="company_name" id="company_name" class="form-control" placeholder="{{translate('messages.company_name')}} {{ $order->delivery_company?->tracking_url }} " value="{{ $order->delivery_company?->company_name }}">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group mb-0">
                                        <label class="input-label" for="tracking_url">{{translate('messages.tracking_URL')}}</label>
                                        <input type="text" name="tracking_url" id="tracking_url" class="form-control" placeholder="https://www.example.com" value="{{$order->delivery_company?->tracking_url}}">

                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group mb-0">
                                        <label class="input-label" for="serial_number">{{translate('messages.serial_number')}}</label>
                                        <input type="text" name="serial_number" id="serial_number" class="form-control" placeholder="{{translate('messages.serial_number')}}" value="{{ $order->delivery_company?->serial_number }}">
                                    </div>
                                </div>
                            </div>
                        </div>


                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                          <button type="submit" class="btn btn--primary">Submit</button>
                        </div>
                    </form>
                      </div>
                    </div>
                  </div>
            </div>
            @endif
                @if ($order['order_type'] != 'take_away')
                    <!-- Card -->
                    <div class="card mb-2">
                        <!-- Header -->
                        <div class="card-header">
                            <h4 class="card-header-title">
                                <span class="card-header-icon"><i class="tio-user"></i></span>
                                <span>{{ translate('messages.Delivery Man') }}</span>
                            </h4>
                        </div>
                        <!-- End Header -->

                        <!-- Body -->
                        <div class="card-body">
                            @if ($order->delivery_man)
                                <div class="media align-items-center customer--information-single" href="javascript:">
                                    <div class="avatar avatar-circle">
                                        <img class="avatar-img onerror-image"
                                             data-onerror-image="{{ asset('public/assets/admin/img/160x160/img1.jpg') }}"
                                             src="{{\App\CentralLogics\Helpers::onerror_image_helper($order->delivery_man->image, asset('storage/app/public/delivery-man/').'/'.$order->delivery_man->image, asset('public/assets/admin/img/160x160/img1.jpg'), 'delivery-man/') }}"
                                            alt="Image Description">
                                    </div>
                                    <div class="media-body">
                                        <span
                                            class="text-body d-block text-hover-primary mb-1">{{ $order->delivery_man['f_name'] . ' ' . $order->delivery_man['l_name'] }}</span>

                                        <span class="text--title font-semibold d-flex align-items-center">
                                            <i class="tio-shopping-basket-outlined mr-2"></i>
                                            {{ $order->delivery_man->orders_count }}
                                            {{ translate('messages.orders_delivered') }}
                                        </span>

                                        <span class="text--title font-semibold d-flex align-items-center">
                                            <i class="tio-call-talking-quiet mr-2"></i>
                                            {{ $order->delivery_man['phone'] }}
                                        </span>

                                        <span class="text--title font-semibold d-flex align-items-center">
                                            <i class="tio-email-outlined mr-2"></i>
                                            {{ $order->delivery_man['email'] }}
                                        </span>
                                    </div>
                                </div>

                                @if ($order['order_type'] != 'take_away')
                                    <hr>
                                    @php($address = $order->dm_last_location)
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5>{{ translate('messages.last_location') }}</h5>
                                    </div>
                                    @if (isset($address))
                                        <span class="d-block">
                                            <a target="_blank"
                                                href="http://maps.google.com/maps?z=12&t=m&q=loc:{{ $address['latitude'] }}+{{ $address['longitude'] }}">
                                                <i class="tio-map"></i> {{ $address['location'] }}<br>
                                            </a>
                                        </span>
                                    @else
                                        <span class="d-block text-lowercase qcont">
                                            {{ translate('messages.location_not_found') }}
                                        </span>
                                    @endif
                                @endif
                            @else

                            @if($order && $order->parcel_company_id==null)
                                <span class="badge badge-soft-danger py-2 d-block qcont">
                                    {{ translate('messages.deliveryman_not_found') }}
                                </span>
                                @endif
                            @endif

                          <!-- sender info start -->
                            <hr>
                          @if ($order->delivery_company)
                          <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title">
                                <span class="card-header-icon">
                                    <i class="tio-shop"></i>
                                </span>
                                <span>{{ translate($parcel_order ? 'messages.sender' : 'messages.Third_party_company_info') }}</span>
                            </h5>
                        </div>

                            <div class="pt-3">
                              <div class="pt-2" >{{translate("messages.company_name:")}} <span class="text-dark">{{$order->delivery_company?->company_name}}</span>  </div>
                              <div class="pt-2" >{{translate("messages.tracking_URL:")}}
                                <a href="{{$order->delivery_company?->tracking_url}}" class=""><span class="badge badge-soft-success mt-3 mb-3">{{ translate('messages.click') }}</span></a>
                                 </div>
                              <div class="pt-2">{{translate("messages.serial_number:")}}  <span class="text-dark">{{$order->delivery_company?->serial_number}}</span> </div>
                            </div>
                          @endif
                                <!-- sender info end -->
                        </div>
                        <!-- End Body -->
                    </div>
                @endif
                <!-- End Card -->

                <!-- order proof -->
                <div class="card mb-2 mt-2">
                    <div class="card-header border-0 text-center pb-0">
                        <h4 class="m-0">{{ translate('messages.delivery_proof') }} </h4>
                        @if ($order['store']&&  $order['store']['self_delivery_system'])

                        <button class="btn btn-outline-primary btn-sm" data-toggle="modal"
                                            data-target=".order-proof-modal">
                                            {{ translate('messages.add') }}
                                        </button>
                        @endif
                    </div>
                    @php($data = isset($order->order_proof) ? json_decode($order->order_proof, true) : 0)
                    <div class="card-body pt-2">
                        @if ($data)
                        <label class="input-label"
                            for="order_proof">{{ translate('messages.image') }} : </label>
                        <div class="row g-3">
                                @foreach ($data as $key => $img)
                                    <div class="col-3">
                                        <img class="img__aspect-1 rounded border w-100 onerror-image" data-toggle="modal"
                                            data-target="#imagemodal{{ $key }}"
                                             data-onerror-image="{{ asset('public/assets/admin/img/160x160/img2.jpg') }}"
                                             src="{{\App\CentralLogics\Helpers::onerror_image_helper($img, asset('storage/app/public/order').'/'.$img, asset('public/assets/admin/img/160x160/img2.jpg'), 'order/') }}"
                                             alt="image">
                                    </div>
                                    <div class="modal fade" id="imagemodal{{ $key }}" tabindex="-1"
                                        role="dialog" aria-labelledby="order_proof_{{ $key }}"
                                        aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title"
                                                        id="order_proof_{{ $key }}">
                                                        {{ translate('order_proof_image') }}</h4>
                                                    <button type="button" class="close"
                                                        data-dismiss="modal"><span
                                                            aria-hidden="true">&times;</span><span
                                                            class="sr-only">{{ translate('messages.cancel') }}</span></button>
                                                </div>
                                                <div class="modal-body">
                                                    <img src="{{ asset('storage/app/' . 'public/order/' . $img) }}"
                                                        class="initial--22 w-100" alt="img">
                                                </div>
                                                <div class="modal-footer">
                                                    <a class="btn btn-primary"
                                                        href="{{ route('admin.file-manager.download', base64_encode('public/order/' . $img)) }}"><i
                                                            class="tio-download"></i>
                                                        {{ translate('messages.download') }}
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @endif
                    </div>
                </div>

                <!-- Card -->
                <div class="card">
                    <!-- Header -->
                    <div class="card-header">
                        <h4 class="card-header-title">
                            <span class="card-header-icon"><i class="tio-user"></i></span>
                            <span>{{ translate('messages.customer') }}</span>
                        </h4>
                    </div>
                    <!-- End Header -->

                    <!-- Body -->
                    @if ($order->customer)
                        <div class="card-body">

                            <div class="media align-items-center customer--information-single" href="javascript:">
                                <div class="avatar avatar-circle">
                                    <img class="avatar-img onerror-image "
                                         data-onerror-image="{{ asset('public/assets/admin/img/160x160/img1.jpg') }}"
                                         src="{{\App\CentralLogics\Helpers::onerror_image_helper($order->customer->image, asset('storage/app/public/profile/').'/'.$order->customer->image, asset('public/assets/admin/img/160x160/img1.jpg'), 'profile/') }}"
                                        alt="Image Description">
                                </div>
                                <div class="media-body">
                                    <span
                                        class="text-body d-block text-hover-primary mb-1">{{ $order->customer['f_name'] . ' ' . $order->customer['l_name'] }}</span>

                                    <span class="text--title font-semibold d-flex align-items-center">
                                        <i class="tio-shopping-basket-outlined mr-2"></i>
                                        {{ $order->customer->orders_count }}
                                        {{ translate('messages.orders_delivered') }}
                                    </span>

                                    <span class="text--title font-semibold d-flex align-items-center">
                                        <i class="tio-call-talking-quiet mr-2"></i>
                                        {{ $order->customer['phone'] }}
                                    </span>

                                    <span class="text--title font-semibold d-flex align-items-center">
                                        <i class="tio-email-outlined mr-2"></i>
                                        {{ $order->customer['email'] }}
                                    </span>

                                </div>
                            </div>
                            <hr>




                            @if ($order->delivery_address)
                                @php($address = json_decode($order->delivery_address, true))
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5>{{ translate('messages.delivery_info') }}</h5>
                                </div>
                                @if (isset($address))
                                    <span class="delivery--information-single d-block">
                                        <div class="d-flex">
                                            <span class="name">{{ translate('messages.name') }}:</span>
                                            <span class="info">{{ $address['contact_person_name'] }}</span>
                                        </div>
                                        <div class="d-flex">
                                            <span class="name">{{ translate('messages.contact') }}:</span>
                                            <a class="info deco-none"
                                                href="tel:{{ $address['contact_person_number'] }}">
                                                {{ $address['contact_person_number'] }}</a>
                                        </div>
                                        <div class="d-flex">
                                            <span class="name">{{ translate('Floor') }}:</span>
                                            <span
                                                class="info">{{ isset($address['floor']) ? $address['floor'] : '' }}</span>
                                        </div>
                                        <div class="d-flex">
                                            <span class="name">{{ translate('Road') }}:</span>
                                            <span
                                                class="info">{{ isset($address['road']) ? $address['road'] : '' }}</span>
                                        </div>
                                        <div class="d-flex mb-2">
                                            <span class="name">{{ translate('House') }}:</span>
                                            <span
                                                class="info">{{ isset($address['house']) ? $address['house'] : '' }}</span>
                                        </div>

                                        @if ($order['order_type'] != 'take_away' && isset($address['address']))
                                            @if (isset($address['latitude']) && isset($address['longitude']))
                                                <a target="_blank"
                                                    href="http://maps.google.com/maps?z=12&t=m&q=loc:{{ $address['latitude'] }}+{{ $address['longitude'] }}">
                                                    <i class="tio-map"></i>{{ $address['address'] }}<br>
                                                </a>
                                            @else
                                                <i class="tio-map"></i>{{ $address['address'] }}<br>
                                            @endif
                                        @endif
                                    </span>
                                @endif
                            @endif
                        </div>

                    @elseif($order->is_guest)
                        <div class="card-body">
                            <span class="badge badge-soft-success py-2 mb-2 d-block qcont">
                                {{ translate('Guest_user') }}
                            </span>
                            @if ($order->delivery_address)
                            @php($address = json_decode($order->delivery_address, true))
                            <div class="d-flex justify-content-between align-items-center">
                                <h5>{{ translate('messages.delivery_info') }}</h5>
                            </div>
                            @if (isset($address))
                                <span class="delivery--information-single d-block">
                                    <div class="d-flex">
                                        <span class="name">{{ translate('messages.name') }}:</span>
                                        <span class="info">{{ $address['contact_person_name'] }}</span>
                                    </div>
                                    <div class="d-flex">
                                        <span class="name">{{ translate('messages.contact') }}:</span>
                                        <a class="info deco-none"
                                            href="tel:{{ $address['contact_person_number'] }}">
                                            {{ $address['contact_person_number'] }}</a>
                                    </div>
                                    <div class="d-flex">
                                        <span class="name">{{ translate('Floor') }}:</span>
                                        <span
                                            class="info">{{ isset($address['floor']) ? $address['floor'] : '' }}</span>
                                    </div>
                                    <div class="d-flex">
                                        <span class="name">{{ translate('Road') }}:</span>
                                        <span
                                            class="info">{{ isset($address['road']) ? $address['road'] : '' }}</span>
                                    </div>
                                    <div class="d-flex mb-2">
                                        <span class="name">{{ translate('House') }}:</span>
                                        <span
                                            class="info">{{ isset($address['house']) ? $address['house'] : '' }}</span>
                                    </div>

                                    @if ($order['order_type'] != 'take_away' && isset($address['address']))
                                    <hr>
                                        @if (isset($address['latitude']) && isset($address['longitude']))
                                            <a target="_blank"
                                                href="http://maps.google.com/maps?z=12&t=m&q=loc:{{ $address['latitude'] }}+{{ $address['longitude'] }}">
                                                <i class="tio-map"></i>{{ $address['address'] }}<br>
                                            </a>
                                        @else
                                            <i class="tio-map"></i>{{ $address['address'] }}<br>
                                        @endif
                                    @endif
                                </span>
                            @endif
                        @endif

                        </div>
                    @else
                        <div class="card-body">
                            <span class="badge badge-soft-danger py-2 d-block qcont">
                                {{ translate('Customer Not found!') }}
                            </span>
                        </div>
                    @endif
                    <!-- End Body -->
                </div>
                <!-- End Card -->

                @if ($order->store && \App\CentralLogics\Helpers::get_store_data()->store_type == 'company' && $order->order_type!="parcel" )
                <!-- Restaurant Card -->
                <div class="card mt-2">
                    <!-- Body -->
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <span class="card-header-icon">
                                <i class="tio-user"></i>
                            </span>
                      
                               <span>{{ translate('messages.store_information') }}</span>
                          
                        </h5>
                        <div class="media align-items-center deco-none resturant--information-single"
                            href="{{ route('admin.store.view', [$order->store['id'],'module_id' => $order->module_id]) }}"
                            >
                            <div class="avatar avatar-circle">
                                <img class="avatar-img w-75px onerror-image"
                                    data-onerror-image="{{ asset('public/assets/admin/img/100x100/1.png') }}"
                                    src="{{\App\CentralLogics\Helpers::get_image_helper($order?->store,'logo' , asset('storage/app/public/store/') .'/'. $order?->store?->logo , asset('public/assets/admin/img/100x100/1.png'), 'store/') }}"
                                    alt="Image Description">
                            </div>
                            <div class="media-body">
                                <span class="fz--14px text--title font-semibold text-hover-primary d-block">
                                    {{ $order->store['name'] }}
                                </span>
                                <span>{{ $order->store->orders_count }} {{ translate('messages.orders') }}</span>
                                <span class="text--title font-semibold d-flex align-items-center">
                                    <i class="tio-call-talking-quiet mr-2"></i>{{ $order->store['phone'] }}
                                </span>
                                <span class="text--title d-flex align-items-center">
                                    <i class="tio-email mr-2"></i>{{ $order->store['email'] }}
                                </span>
                            </div>
                        </div>
                        <hr>
                        <span class="d-block">
                            <a target="_blank" class="d-flex align-items-center __gap-5px" href="http://maps.google.com/maps?z=12&t=m&q=loc:{{ $order->store['latitude'] }}+{{ $order->store['longitude'] }}">
                                <i class="tio-poi"></i> <span>{{ $order->store['address'] }}</span><br>
                            </a>
                        </span>
                    </div>
                    <!-- End Body -->
                </div>
                <!-- End Card -->
            @endif

            </div>
        </div>
        <!-- End Row -->
    </div>



        <!-- Modal -->
        <div class="modal fade order-proof-modal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title h4" id="mySmallModalLabel">{{ translate('messages.add_delivery_proof') }}</h5>
                    <button type="button" class="btn btn-xs btn-icon btn-ghost-secondary" data-dismiss="modal"
                        aria-label="Close">
                        <i class="tio-clear tio-lg"></i>
                    </button>
                </div>

                <form action="{{ route('vendor.order.add-order-proof', [$order['id']]) }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <!-- Input Group -->
                        <div class="flex-grow-1 mx-auto">

                            <div class="d-flex flex-wrap __gap-12px __new-coba" id="coba">
                                @php($proof = isset($order->order_proof) ? json_decode($order->order_proof, true) : 0)
                                @if ($proof)

                                @foreach ($proof as $key => $photo)
                                            <div class="spartan_item_wrapper min-w-100px max-w-100px">
                                                <img class="img--square"
                                                    src="{{ asset("storage/app/public/order/$photo") }}"
                                                    alt="order image">
                                                <a href="{{ route('vendor.order.remove-proof-image', ['id' => $order['id'], 'name' => $photo]) }}"
                                                    class="spartan_remove_row"><i class="tio-add-to-trash"></i></a>
                                            </div>
                                        @endforeach
                                @endif
                            </div>
                        </div>
                        <!-- End Input Group -->
                        <div class="text-right mt-2">
                            <button class="btn btn--primary">{{ translate('messages.submit') }}</button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>

      <!--Dm assign Modal -->
      <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel">{{ translate('messages.assign_deliveryman') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-5 my-2">
                            <ul class="list-group overflow-auto initial--23">
                                @foreach ($deliveryMen as $dm)
                                    <li class="list-group-item">
                                        <span class="dm_list" role='button' data-id="{{ $dm['id'] }}">
                                            <img class="avatar avatar-sm avatar-circle mr-1 onerror-image"
                                                data-onerror-image="{{ asset('public/assets/admin/img/160x160/img1.jpg') }}"
                                                src="{{\App\CentralLogics\Helpers::onerror_image_helper($dm['image'], asset('storage/app/public/delivery-man/') .'/'. $dm['image'], asset('public/assets/admin/img/160x160/img1.jpg'), 'delivery-man/') }}"
                                                alt="{{ $dm['name'] }}">
                                            {{ $dm['name'] }}
                                        </span>

                                        <a class="btn btn-primary btn-xs float-right add-delivery-man" data-id="{{ $dm['id'] }}">{{ translate('messages.assign') }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="col-md-7 modal_body_map">
                            <div class="location-map" id="dmassign-map">
                                <div class="initial--24" id="map_canvas"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

        <!--Show locations on map Modal -->
        <div class="modal fade" id="locationModal" tabindex="-1" role="dialog" aria-labelledby="locationModalLabel">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="locationModalLabel">{{ translate('messages.location_data') }}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 modal_body_map">
                                <div class="location-map" id="location-map">
                                    <div class="initial--25" id="location_map_canvas"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <!-- End Modal -->

    <div class="modal fade" id="edit-order-amount" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ translate('messages.update_order_amount') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('vendor.order.update-order-amount') }}" method="POST" class="row">
                        @csrf
                        <input type="hidden" name="order_id" value="{{ $order->id }}">
                        <div class="form-group col-12">
                            <label for="order_amount">{{ translate('messages.order_amount') }}</label>
                            <input id="order_amount" type="number" class="form-control" name="order_amount" min="0"
                                value="{{ round($order['order_amount'] - $order['total_tax_amount']  - $order['additional_charge'] -  $order['delivery_charge'] + $order['store_discount_amount'] ,6) }}" step=".01">
                        </div>

                        <div class="form-group col-sm-12">
                            <button class="btn btn-sm btn-primary"
                                type="submit">{{ translate('messages.submit') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="edit-discount-amount" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ translate('messages.update_discount_amount') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('vendor.order.update-discount-amount') }}" method="POST" class="row">
                        @csrf
                        <input type="hidden" name="order_id" value="{{ $order->id }}">
                        <div class="form-group col-12">
                            <label for="discount_amount">{{ translate('messages.discount_amount') }}</label>
                            <input type="number" id="discount_amount" class="form-control" name="discount_amount" min="0"
                                value="{{ $order['store_discount_amount'] }}" step=".01">
                        </div>
                        <div class="mb-2 h-200px" id="map"></div>

                        <div class="form-group col-sm-12">
                            <button class="btn btn-sm btn-primary"
                                type="submit">{{ translate('messages.submit') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- End Content -->


@endsection
@push('script_2')
    <script src="{{ asset('public/assets/admin/js/spartan-multi-image-picker.js') }}"></script>
    <script type="text/javascript">
        "use strict";


        $('.self-delivery-warning').on('click',function (event ){
            event.preventDefault();
            toastr.info(
                "{{ translate('messages.Self_Delivery_is_Disable') }}", {
                    CloseButton: true,
                    ProgressBar: true
                });
        });



        $('.cancelled-status').on('click',function (){
            Swal.fire({
                title: '{{ translate('messages.are_you_sure') }}',
                text: '{{ translate('messages.Change status to canceled ?') }}',
                type: 'warning',
                html:
                    `   <select class="form-control js-select2-custom mx-1" name="reason" id="reason">
                    @foreach ($reasons as $r)
                    <option value="{{ $r->reason }}">
                            {{ $r->reason }}
                    </option>
                    @endforeach

                    </select>`,
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#FC6A57',
                cancelButtonText: '{{ translate('messages.no') }}',
                confirmButtonText: '{{ translate('messages.yes') }}',
                reverseButtons: true,
                onOpen: function () {
                    $('.js-select2-custom').select2({
                        minimumResultsForSearch: 5,
                        width: '100%',
                        placeholder: "Select Reason",
                        language: "en",
                    });
                }
            }).then((result) => {
                if (result.value) {
                    let reason = document.getElementById('reason').value;
                    location.href = '{!! route('vendor.order.status', ['id' => $order['id'],'order_status' => 'canceled']) !!}&reason='+reason,'{{ translate('Change status to canceled ?') }}';
                }
            })

        });

        $('.order-status-change-alert').on('click',function (){
            let route = $(this).data('url');
            let message = $(this).data('message');
            let verification = $(this).data('verification');
            let processing = $(this).data('processing-time') ?? false;

            if (verification) {
                Swal.fire({
                    title: '{{ translate('Enter order verification code') }}',
                    input: 'text',
                    inputAttributes: {
                        autocapitalize: 'off'
                    },
                    showCancelButton: true,
                    cancelButtonColor: 'default',
                    confirmButtonColor: '#FC6A57',
                    confirmButtonText: '{{ translate('messages.submit') }}',
                    showLoaderOnConfirm: true,
                    preConfirm: (otp) => {
                        location.href = route + '&otp=' + otp;
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                })
            } else if (processing) {
                Swal.fire({
                    title: '{{ translate('messages.Are you sure ?') }}',
                    type: 'warning',
                    showCancelButton: true,
                    cancelButtonColor: 'default',
                    confirmButtonColor: '#FC6A57',
                    cancelButtonText: '{{ translate('messages.Cancel') }}',
                    confirmButtonText: '{{ translate('messages.submit') }}',
                    inputPlaceholder: "{{ translate('Enter processing time') }}",
                    input: 'text',
                    html: message + '<br/>'+'<label>{{ translate('Enter Processing time in minutes') }}</label>',
                    inputValue: processing,
                    preConfirm: (processing_time) => {
                        location.href = route + '&processing_time=' + processing_time;
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                })
            } else {
                Swal.fire({
                    title: '{{ translate('messages.Are you sure ?') }}',
                    text: message,
                    type: 'warning',
                    showCancelButton: true,
                    cancelButtonColor: 'default',
                    confirmButtonColor: '#FC6A57',
                    cancelButtonText: '{{ translate('messages.No') }}',
                    confirmButtonText: '{{ translate('messages.Yes') }}',
                    reverseButtons: true
                }).then((result) => {
                    if (result.value) {
                        location.href = route;
                    }
                })
            }

        });

        $(function() {
            $("#coba").spartanMultiImagePicker({
                fieldName: 'order_proof[]',
                maxCount: 6-{{ ($order->order_proof && is_array($order->order_proof))?count(json_decode($order->order_proof)):0 }},
                rowHeight: '100px !important',
                groupClassName: 'spartan_item_wrapper min-w-100px max-w-100px',
                maxFileSize: '',
                placeholderImage: {
                    image: "{{ asset('public/assets/admin/img/upload.png') }}",
                    width: '100px'
                },
                dropFileLabel: "Drop Here",
                onAddRow: function(index, file) {

                },
                onRenderedPreview: function(index) {

                },
                onRemoveRow: function(index) {

                },
                onExtensionErr: function() {
                    toastr.error(
                        "{{ translate('messages.please_only_input_png_or_jpg_type_file') }}", {
                            CloseButton: true,
                            ProgressBar: true
                        });
                },
                onSizeErr: function() {
                    toastr.error("{{ translate('messages.file_size_too_big') }}", {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        });
    </script>

    <script>

        $('.add-delivery-man').on('click',function (){
                id = $(this).data('id');
            $.ajax({
                type: "GET",
                url: '{{ url('/') }}/store-panel/parcel/add-delivery-man/{{ $order['id'] }}/' + id,
                success: function(data) {
                    location.reload();
                    console.log(data)
                    toastr.success('Successfully added', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                },
                error: function(response) {
                    console.log(response);
                    toastr.error(response.responseJSON.message, {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        })
        </script>


    <script
        src="https://maps.googleapis.com/maps/api/js?key={{ \App\Models\BusinessSetting::where('key', 'map_api_key')->first()->value }}&libraries=places&v=3.45.8">
    </script>
    <script>
        var deliveryMan = <?php echo json_encode($deliveryMen); ?>;
        var map = null;
        @if ($order->order_type == 'parcel')
            var myLatlng = new google.maps.LatLng({{ $address['latitude'] }}, {{ $address['longitude'] }});
        @else
            @php($default_location = App\CentralLogics\Helpers::get_business_settings('default_location'))
            var myLatlng = new google.maps.LatLng(
                {{ isset($order->store) ? $order->store->latitude : (isset($default_location) ? $default_location['lat'] : 0) }},
                {{ isset($order->store) ? $order->store->longitude : (isset($default_location['lng']) ? $default_location['lng'] : 0) }}
            );
        @endif
        var dmbounds = new google.maps.LatLngBounds(null);
        var locationbounds = new google.maps.LatLngBounds(null);
        var dmMarkers = [];
        dmbounds.extend(myLatlng);
        locationbounds.extend(myLatlng);
        var myOptions = {
            center: myLatlng,
            zoom: 13,
            mapTypeId: google.maps.MapTypeId.ROADMAP,

            panControl: true,
            mapTypeControl: false,
            panControlOptions: {
                position: google.maps.ControlPosition.RIGHT_CENTER
            },
            zoomControl: true,
            zoomControlOptions: {
                style: google.maps.ZoomControlStyle.LARGE,
                position: google.maps.ControlPosition.RIGHT_CENTER
            },
            scaleControl: false,
            streetViewControl: false,
            streetViewControlOptions: {
                position: google.maps.ControlPosition.RIGHT_CENTER
            }
        };

        function initializeGMap() {

            map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);

            var infowindow = new google.maps.InfoWindow();
            @if ($order->store)
                var Restaurantmarker = new google.maps.Marker({
                    @if ($parcel_order)
                        position: new google.maps.LatLng({{ $address['latitude'] }},
                            {{ $address['longitude'] }}),
                        title: "{{ Str::limit($order?->customer?->f_name . ' ' . $order?->customer?->l_name, 15, '...') }}",
                        // icon: "{{ asset('public/assets/admin/img/restaurant_map.png') }}"
                    @else
                        position: new google.maps.LatLng({{ $order->store->latitude }},
                            {{ $order->store->longitude }}),
                        title: "{{ Str::limit($order->store->name, 15, '...') }}",
                        icon: "{{ asset('public/assets/admin/img/restaurant_map.png') }}",
                    @endif
                    map: map,

                });

                google.maps.event.addListener(Restaurantmarker, 'click', (function(Restaurantmarker) {
                    return function() {
                        @if ($parcel_order)
                            infowindow.setContent(
                                "<div style='float:left'><img style='max-height:40px;wide:auto;' src='{{ asset('storage/app/public/profile/' . $order?->customer?->image) }}'></div><div style='float:right; padding: 10px;'><b>{{ $order->customer?->f_name }}{{ $order->customer?->l_name }}</b><br />{{ $address['address'] }}</div>"
                            );
                        @else
                            infowindow.setContent(
                                "<div style='float:left'><img style='max-height:40px;wide:auto;' src='{{ asset('storage/app/public/restaurant/' . $order->store?->logo) }}'></div><div class='text-break' style='float:right; padding: 10px;'><b>{{ Str::limit($order->store->name, 15, '...') }}</b><br /> {{ $order->store->address }}</div>"
                            );
                        @endif
                        infowindow.open(map, Restaurantmarker);
                    }
                })(Restaurantmarker));
            @endif

            map.fitBounds(dmbounds);
            for (var i = 0; i < deliveryMan.length; i++) {
                if (deliveryMan[i].lat) {
                    // var contentString = "<div style='float:left'><img style='max-height:40px;wide:auto;' src='{{ asset('storage/app/public/delivery-man') }}/"+deliveryMan[i].image+"'></div><div style='float:right; padding: 10px;'><b>"+deliveryMan[i].name+"</b><br/> "+deliveryMan[i].location+"</div>";
                    var point = new google.maps.LatLng(deliveryMan[i].lat, deliveryMan[i].lng);
                    dmbounds.extend(point);
                    map.fitBounds(dmbounds);
                    var marker = new google.maps.Marker({
                        position: point,
                        map: map,
                        title: deliveryMan[i].location,
                        icon: "{{ asset('public/assets/admin/img/delivery_boy_map.png') }}"
                    });
                    dmMarkers[deliveryMan[i].id] = marker;
                    google.maps.event.addListener(marker, 'click', (function(marker, i) {
                        return function() {
                            infowindow.setContent(
                                "<div style='float:left'><img style='max-height:40px;wide:auto;' src='{{ asset('storage/app/public/delivery-man') }}/" +
                                deliveryMan[i].image +
                                "'></div><div style='float:right; padding: 10px;'><b>" + deliveryMan[i]
                                .name + "</b><br/> " + deliveryMan[i].location + "</div>");
                            infowindow.open(map, marker);
                        }
                    })(marker, i));
                }

            };
        }

        function initMap() {
            let map = new google.maps.Map(document.getElementById("map"), {
                zoom: 13,
                center: {
                    lat: {{ isset($order->store) ? $order->store->latitude : '23.757989' }},
                    lng: {{ isset($order->store) ? $order->store->longitude : '90.360587' }}
                }
            });

            let zonePolygon = null;

            //get current location block
            let infoWindow = new google.maps.InfoWindow();
            // Try HTML5 geolocation.
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        myLatlng = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude,
                        };
                        infoWindow.setPosition(myLatlng);
                        infoWindow.setContent("Location found.");
                        infoWindow.open(map);
                        map.setCenter(myLatlng);
                    },
                    () => {
                        handleLocationError(true, infoWindow, map.getCenter());
                    }
                );
            } else {
                // Browser doesn't support Geolocation
                handleLocationError(false, infoWindow, map.getCenter());
            }
            //-----end block------
            const input = document.getElementById("pac-input");
            const searchBox = new google.maps.places.SearchBox(input);
            map.controls[google.maps.ControlPosition.TOP_CENTER].push(input);
            let markers = [];
            const bounds = new google.maps.LatLngBounds();
            searchBox.addListener("places_changed", () => {
                const places = searchBox.getPlaces();

                if (places.length == 0) {
                    return;
                }
                // Clear out the old markers.
                markers.forEach((marker) => {
                    marker.setMap(null);
                });
                markers = [];
                // For each place, get the icon, name and location.
                places.forEach((place) => {
                    if (!place.geometry || !place.geometry.location) {
                        console.log("Returned place contains no geometry");
                        return;
                    }
                    console.log(place.geometry.location);
                    if (!google.maps.geometry.poly.containsLocation(
                            place.geometry.location,
                            zonePolygon
                        )) {
                        toastr.error('{{ translate('messages.out_of_coverage') }}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                        return false;
                    }

                    document.getElementById('latitude').value = place.geometry.location.lat();
                    document.getElementById('longitude').value = place.geometry.location.lng();

                    const icon = {
                        url: place.icon,
                        size: new google.maps.Size(71, 71),
                        origin: new google.maps.Point(0, 0),
                        anchor: new google.maps.Point(17, 34),
                        scaledSize: new google.maps.Size(25, 25),
                    };
                    // Create a marker for each place.
                    markers.push(
                        new google.maps.Marker({
                            map,
                            icon,
                            title: place.name,
                            position: place.geometry.location,
                        })
                    );

                    if (place.geometry.viewport) {
                        // Only geocodes have viewport.
                        bounds.union(place.geometry.viewport);
                    } else {
                        bounds.extend(place.geometry.location);
                    }
                });
                map.fitBounds(bounds);
            });
            @if ($order->store)
                $.get({
                    url: '{{ url('/') }}/admin/zone/get-coordinates/{{ $order->store->zone_id }}',
                    dataType: 'json',
                    success: function(data) {
                        zonePolygon = new google.maps.Polygon({
                            paths: data.coordinates,
                            strokeColor: "#FF0000",
                            strokeOpacity: 0.8,
                            strokeWeight: 2,
                            fillColor: 'white',
                            fillOpacity: 0,
                        });
                        zonePolygon.setMap(map);
                        zonePolygon.getPaths().forEach(function(path) {
                            path.forEach(function(latlng) {
                                bounds.extend(latlng);
                                map.fitBounds(bounds);
                            });
                        });
                        map.setCenter(data.center);
                        google.maps.event.addListener(zonePolygon, 'click', function(mapsMouseEvent) {
                            infoWindow.close();
                            // Create a new InfoWindow.
                            infoWindow = new google.maps.InfoWindow({
                                position: mapsMouseEvent.latLng,
                                content: JSON.stringify(mapsMouseEvent.latLng.toJSON(), null,
                                    2),
                            });
                            var coordinates = JSON.stringify(mapsMouseEvent.latLng.toJSON(), null, 2);
                            var coordinates = JSON.parse(coordinates);

                            document.getElementById('latitude').value = coordinates['lat'];
                            document.getElementById('longitude').value = coordinates['lng'];
                            infoWindow.open(map);
                        });
                    },
                });
            @endif

        }

        $(document).ready(function() {

            // Re-init map before show modal
            $('#myModal').on('shown.bs.modal', function(event) {
                initMap();
                var button = $(event.relatedTarget);
                $("#dmassign-map").css("width", "100%");
                $("#map_canvas").css("width", "100%");
            });

            // Trigger map resize event after modal shown
            $('#myModal').on('shown.bs.modal', function() {
                initializeGMap();
                google.maps.event.trigger(map, "resize");
                map.setCenter(myLatlng);
            });

            // Address change modal modal shown
            $('#shipping-address-modal').on('shown.bs.modal', function() {
                initMap();
                // google.maps.event.trigger(map, "resize");
                // map.setCenter(myLatlng);
            });


            function initializegLocationMap() {
                map = new google.maps.Map(document.getElementById("location_map_canvas"), myOptions);

                var infowindow = new google.maps.InfoWindow();

                @if ($order->customer && isset($address))
                    var marker = new google.maps.Marker({
                        position: new google.maps.LatLng({{ $address['latitude'] }},
                            {{ $address['longitude'] }}),
                        map: map,
                        title: "{{ $order->customer->f_name }} {{ $order->customer->l_name }}",
                        icon: "{{ asset('public/assets/admin/img/customer_location.png') }}"
                    });

                    google.maps.event.addListener(marker, 'click', (function(marker) {
                        return function() {
                            infowindow.setContent(
                                "<div style='float:left'><img style='max-height:40px;wide:auto;' src='{{ asset('storage/app/public/profile/' . $order->customer->image) }}'></div><div style='float:right; padding: 10px;'><b>{{ $order->customer->f_name }} {{ $order->customer->l_name }}</b><br />{{ $address['address'] }}</div>"
                            );
                            infowindow.open(map, marker);
                        }
                    })(marker));
                    locationbounds.extend(marker.getPosition());
                @endif
                @if ($order->delivery_man && $order->dm_last_location)
                    var dmmarker = new google.maps.Marker({
                        position: new google.maps.LatLng({{ $order->dm_last_location['latitude'] }},
                            {{ $order->dm_last_location['longitude'] }}),
                        map: map,
                        title: "{{ $order->delivery_man->f_name }} {{ $order->delivery_man->l_name }}",
                        icon: "{{ asset('public/assets/admin/img/delivery_boy_map.png') }}"
                    });

                    google.maps.event.addListener(dmmarker, 'click', (function(dmmarker) {
                        return function() {
                            infowindow.setContent(
                                "<div style='float:left'><img style='max-height:40px;wide:auto;' src='{{ asset('storage/app/public/delivery-man/' . $order->delivery_man->image) }}'></div> <div style='float:right; padding: 10px;'><b>{{ $order->delivery_man->f_name }} {{ $order->delivery_man->l_name }}</b><br /> {{ $order->dm_last_location['location'] }}</div>"
                            );
                            infowindow.open(map, dmmarker);
                        }
                    })(dmmarker));
                    locationbounds.extend(dmmarker.getPosition());
                @endif

                @if ($order->store)
                    var Retaurantmarker = new google.maps.Marker({
                        position: new google.maps.LatLng({{ $order->store->latitude }},
                            {{ $order->store->longitude }}),
                        map: map,
                        title: "{{ Str::limit($order->store->name, 15, '...') }}",
                        icon: "{{ asset('public/assets/admin/img/restaurant_map.png') }}"
                    });

                    google.maps.event.addListener(Retaurantmarker, 'click', (function(Retaurantmarker) {
                        return function() {
                            infowindow.setContent(
                                "<div style='float:left'><img style='max-height:40px;wide:auto;' src='{{ asset('storage/app/public/restaurant/' . $order->store->logo) }}'></div> <div style='float:right; padding: 10px;'><b>{{ Str::limit($order->store->name, 15, '...') }}</b><br /> {{ $order->store->address }}</div>"
                            );
                            infowindow.open(map, Retaurantmarker);
                        }
                    })(Retaurantmarker));
                    locationbounds.extend(Retaurantmarker.getPosition());
                @endif
                @if ($parcel_order && isset($receiver_details))
                    var Receivermarker = new google.maps.Marker({
                        position: new google.maps.LatLng({{ $receiver_details['latitude'] }},
                            {{ $receiver_details['longitude'] }}),
                        map: map,
                        title: "{{ Str::limit($receiver_details['contact_person_name'], 15, '...') }}",
                        // icon: "{{ asset('public/assets/admin/img/restaurant_map.png') }}"
                    });

                    google.maps.event.addListener(Receivermarker, 'click', (function(Receivermarker) {
                        return function() {
                            infowindow.open(map, Receivermarker);
                        }
                    })(Receivermarker));
                    locationbounds.extend(Receivermarker.getPosition());
                @endif

                google.maps.event.addListenerOnce(map, 'idle', function() {
                    map.fitBounds(locationbounds);
                });
            }

            // Re-init map before show modal
            $('#locationModal').on('shown.bs.modal', function(event) {
                initializegLocationMap();
            });


            $('.dm_list').on('click', function() {
                var id = $(this).data('id');
                map.panTo(dmMarkers[id].getPosition());
                map.setZoom(13);
                dmMarkers[id].setAnimation(google.maps.Animation.BOUNCE);
                window.setTimeout(() => {
                    dmMarkers[id].setAnimation(null);
                }, 3);
            });
        })
    </script>
@endpush
