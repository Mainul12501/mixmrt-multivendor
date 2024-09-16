<div class="row">
    <div class="col-lg-12 text-center "><h1 >{{ translate('Courier_Company_reports') }}</h1></div>
    <div class="col-lg-12">



    <table>
        <thead>
            <tr>
                <th>{{ translate('Search_Criteria') }}</th>
                <th></th>
                <th></th>
                <th>
                    @if(isset($data['module']))
                    {{ translate('module' )}} - {{ $data['module']?translate($data['module']):translate('all') }}
                    <br>
                    @endif
                    <br>
                    {{ translate('company' )}} - {{ $data['store']??translate('all') }}
                    @if (!isset($data['type']) )
                    <br>
                    {{ translate('customer' )}} - {{ $data['customer']??translate('all') }}
                    @endif
                    @if ($data['from'])
                    <br>
                    {{ translate('from' )}} - {{ $data['from']?Carbon\Carbon::parse($data['from'])->format('d M Y'):'' }}
                    @endif
                    @if ($data['to'])
                    <br>
                    {{ translate('to' )}} - {{ $data['to']?Carbon\Carbon::parse($data['to'])->format('d M Y'):'' }}
                    @endif
                    <br>
                    {{ translate('filter')  }}- {{  translate($data['filter']) }}
                    <br>
                    {{ translate('Search_Bar_Content')  }}- {{ $data['search'] ??translate('N/A') }}

                </th>
                <th> </th>
                <th></th>
                <th></th>
                <th></th>
                </tr>
        <tr>
            <th >{{translate('sl')}}</th>
            <th  >{{translate('messages.order_id')}}</th>
            <th  >{{ translate('Customer Name') }}</th>
            <th  >{{ translate('delivery_charge') }}</th>
            <th  >{{ translate('admin_commission') }}</th>
            <th  >{{ translate('additional_charge') }}</th>
            <th  >{{ translate('company_net_income') }}</th>
            <th  >{{ translate('amount_received_by') }}</th>
            <th  >{{ translate('payment_method') }}</th>
            <th  >{{ translate('payment_status') }}</th>
        </thead>
        <tbody>
            @foreach ($data['parcel_transactions'] as $key => $ot)
            <tr>
                <td>{{ $key+1}}</td>
                <td  >
                    @if ($ot->order)
                    {{ $ot['order_id'] }}
                    @endif
                </td>
                <td >
                    @if (isset($ot->order->customer))
                    {{ $ot->order->customer->f_name.' '.$ot->order->customer->l_name }}
                    @elseif ($ot['type'] == 'add_fund_bonus')
                    {{ $ot->user->f_name.' '.$ot->user->l_name }}
                    @else
                    {{translate('messages.invalid_customer')}}
                    @endif
                </td>
                <td  >
                    {{ \App\CentralLogics\Helpers::format_currency($ot->delivery_charge) }}</td>

                <td class="text-right pr-xl-5">
                    {{ \App\CentralLogics\Helpers::format_currency(($ot->admin_commission - $ot->order['flash_admin_discount_amount'])) }}
                </td>
                <td >
                    {{ \App\CentralLogics\Helpers::format_currency(($ot->additional_charge)) }}
                </td>
                <td >
                    {{ \App\CentralLogics\Helpers::format_currency($ot->company_amount) }}
                </td>
                @if ($ot->received_by == 'admin')
                <td >{{ translate('messages.admin') }}</td>
                @elseif ($ot->received_by == 'company')
                <td >{{ translate('messages.company') }}</td>
            @elseif ($ot->received_by == 'deliveryman')
                <td >
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
                <td >{{ translate('messages.store') }}</td>
            @endif
            <td >
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
</div>
