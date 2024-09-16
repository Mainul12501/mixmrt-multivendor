<div id="headerMain" class="d-none">
    <header id="header"
            class="navbar navbar-expand-lg navbar-fixed navbar-height navbar-flush navbar-container navbar-bordered">
        <div class="navbar-nav-wrap">
            <div class="navbar-nav-wrap-content-left  d-xl-none">
                <!-- Navbar Vertical Toggle -->
                <button type="button" class="js-navbar-vertical-aside-toggle-invoker close mr-3">
                    <i class="tio-first-page navbar-vertical-aside-toggle-short-align" data-toggle="tooltip"
                       data-placement="right" title="Collapse"></i>
                    <i class="tio-last-page navbar-vertical-aside-toggle-full-align"
                       data-template='<div class="tooltip d-none d-sm-block" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
                       data-toggle="tooltip" data-placement="right" title="Expand"></i>
                </button>
                <!-- End Navbar Vertical Toggle -->
            </div>

            <!-- Secondary Content -->
            <div class="navbar-nav-wrap-content-right">
                <!-- Navbar -->
                <ul class="navbar-nav align-items-center flex-row">
                    <li class="nav-item max-sm-m-0">
                        <div class="hs-unfold">
                            <div>
                                @php($local = session()->has('vendor_local')?session('vendor_local'):null)
                                @php($lang = \App\Models\BusinessSetting::where('key', 'system_language')->first())
                                @if ($lang)
                                <div
                                    class="topbar-text dropdown disable-autohide text-capitalize d-flex">
                                    <a class="topbar-link dropdown-toggle d-flex align-items-center title-color"
                                    href="#" data-toggle="dropdown">
                                            @foreach(json_decode($lang['value'],true) as $data)
                                                @if($data['code']==$local)
                                                    <i class="tio-globe"></i> {{$data['code']}}

                                                @elseif(!$local &&  $data['default'] == true)
                                                    <i class="tio-globe"></i> {{$data['code']}}
                                                @endif
                                            @endforeach
                                    </a>
                                    <ul class="dropdown-menu lang-menu">
                                        @foreach(json_decode($lang['value'],true) as $key =>$data)
                                            @if($data['status']==1)
                                                <li>
                                                    <a class="dropdown-item py-1"
                                                        href="{{route('vendor.lang',[$data['code']])}}">
                                                        <span class="text-capitalize">{{$data['code']}}</span>
                                                    </a>
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </div>
                                @endif
                            </div>
                        </div>
                    </li>
                    <li class="nav-item d-none d-sm-inline-block mr-4">
                        <!-- Notification -->
                        <div class="hs-unfold">
                            <a class="js-hs-unfold-invoker btn btn-icon btn-soft-secondary rounded-circle"
                               href="{{route('vendor.message.list')}}">
                                <i class="tio-messages-outlined"></i>
                                @php($message=\App\Models\Conversation::whereUser(\App\CentralLogics\Helpers::get_loggedin_user()->id)->where('unread_message_count','>','0')->count())
                                @if($message!=0)
                                    <span class="btn-status btn-sm-status btn-status-danger"></span>
                                @endif
                            </a>
                        </div>
                        <!-- End Notification -->
                    </li>



                    <li class="nav-item">
                        <!-- Account -->
                        <div class="hs-unfold">
                            <a class="js-hs-unfold-invoker navbar-dropdown-account-wrapper" href="javascript:;"
                               data-hs-unfold-options='{
                                     "target": "#accountNavbarDropdown",
                                     "type": "css-animation"
                                   }'>
                                <div class="cmn--media right-dropdown-icon d-flex align-items-center">
                                    <div class="media-body pl-0 pr-2">
                                        <span class="card-title h5 text-right">
                                            {{\App\CentralLogics\Helpers::get_loggedin_user()->f_name}}
                                            {{\App\CentralLogics\Helpers::get_loggedin_user()->l_name}}
                                        </span>
                                        <span class="card-text">{{\App\CentralLogics\Helpers::get_loggedin_user()->email}}</span>
                                    </div>
                                    <div class="avatar avatar-sm avatar-circle">
                                        <img class="avatar-img  onerror-image"  data-onerror-image="{{asset('public/assets/admin/img/160x160/img1.jpg')}}"
                                        src="{{\App\CentralLogics\Helpers::get_image_helper(\App\CentralLogics\Helpers::get_loggedin_user(),'image', asset('storage/app/public/vendor/').'/'.\App\CentralLogics\Helpers::get_loggedin_user()->image, asset('public/assets/admin/img/160x160/img1.jpg'), 'vendor/') }}"
                                            alt="Image Description">
                                        <span class="avatar-status avatar-sm-status avatar-status-success"></span>
                                    </div>
                                </div>
                            </a>

                            <div id="accountNavbarDropdown"
                                 class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-right navbar-dropdown-menu navbar-dropdown-account min--240">
                                <div class="dropdown-item-text">
                                    <div class="media align-items-center">
                                        <div class="avatar avatar-sm avatar-circle mr-2">
                                            <img class="avatar-img  onerror-image"  data-onerror-image="{{asset('public/assets/admin/img/160x160/img1.jpg')}}"
                                            src="{{\App\CentralLogics\Helpers::get_image_helper(\App\CentralLogics\Helpers::get_loggedin_user(),'image', asset('storage/app/public/vendor/').'/'.\App\CentralLogics\Helpers::get_loggedin_user()->image, asset('public/assets/admin/img/160x160/img1.jpg'), 'vendor/') }}"
                                                 alt="Owner image">
                                        </div>
                                        <div class="media-body">
                                            <span class="card-title h5">{{\App\CentralLogics\Helpers::get_loggedin_user()->f_name}}</span>
                                            <span class="card-text">{{\App\CentralLogics\Helpers::get_loggedin_user()->email}}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="dropdown-divider"></div>

                                <a class="dropdown-item" href="{{route('vendor.profile.view')}}">
                                    <span class="text-truncate pr-2" title="Settings">{{translate('messages.settings')}}</span>
                                </a>

                                <div class="dropdown-divider"></div>

                                <a class="dropdown-item log-out" >
                                    <span class="text-truncate pr-2 log-out" title="Sign out">{{translate('messages.sign_out')}}</span>
                                </a>
                            </div>
                        </div>
                        <!-- End Account -->
                    </li>
                </ul>
                <!-- End Navbar -->
            </div>
            <!-- End Secondary Content -->
        </div>
    </header>
</div>
<div id="headerFluid" class="d-none"></div>
<div id="headerDouble" class="d-none"></div>
<?php
$wallet = \App\Models\StoreWallet::where('vendor_id',\App\CentralLogics\Helpers::get_vendor_id())->first();
$Payable_Balance = $wallet?->collected_cash  > 0 ? 1: 0;

$cash_in_hand_overflow=  \App\Models\BusinessSetting::where('key' ,'cash_in_hand_overflow_store')->first()?->value;
$cash_in_hand_overflow_store_amount =  \App\Models\BusinessSetting::where('key' ,'cash_in_hand_overflow_store_amount')->first()?->value;
$val= (string) ($cash_in_hand_overflow_store_amount - (($cash_in_hand_overflow_store_amount * 10)/100));
?>

@if ($Payable_Balance == 1 &&  $cash_in_hand_overflow &&  $wallet?->balance < 0 &&  $val <=  abs($wallet?->collected_cash)  )
    <div class="alert __alert-2 alert-warning m-0 py-1 px-2" role="alert">
        <img class="rounded mr-1"  width="25" src="{{ asset('/public/assets/admin/img/header_warning.png') }}" alt="">
        <div class="cont">
            <h4 class="m-0">{{ translate('Attention_Please') }} </h4>
            {{ translate('The_Cash_in_Hand_amount_is_about_to_exceed_the_limit._Please_pay_the_due_amount._If_the_limit_exceeds,_your_account_will_be_suspended.') }}
        </div>
    </div>
@endif

@if ($Payable_Balance == 1 &&  $cash_in_hand_overflow &&  $wallet?->balance < 0 &&  $cash_in_hand_overflow_store_amount < $wallet?->collected_cash)
    <div class="alert __alert-2 alert-warning m-0 py-1 px-2" role="alert">
        <img class="mr-1"  width="25" src="{{ asset('/public/assets/admin/img/header_warning.png') }}" alt="">
        <div class="cont">
            <h4 class="m-0">{{ translate('Attention_Please') }} </h4>{{ translate('The_Cash_in_Hand_amount_limit_is_exceeded._Your_account_is_now_suspended._Please_pay_the_due_amount_to_receive_new_order_requests_again.') }}<a href="{{ route('vendor.wallet.index') }}" class="alert-link"> &nbsp; {{ translate('Pay_the_due') }}</a>
        </div>
    </div>
@endif




    <?php
    $store_data=\App\CentralLogics\Helpers::get_store_data();
    $subscription_deadline_warning_days =  \App\Models\BusinessSetting::where('key','subscription_deadline_warning_days')->first()?->value ?? 7;
    $subscription_deadline_warning_message =  \App\Models\BusinessSetting::where('key','subscription_deadline_warning_message')->first()?->value ?? null;
    ?>





@if ( !in_array($store_data->store_business_model, ['none','commission']) && !Request::is('store-panel/subscription/*') )

        <?php
            $pers=10;
            if($store_data?->store_sub){
                $validity=$store_data?->store_sub?->validity;
                    $remaining_days= Carbon\Carbon::now()->subDays(1)->diffInDays($store_data?->store_sub?->expiry_date_parsed->format('Y-m-d'), false);
                    $pers=  $validity-$remaining_days > 0  ? (($validity-$remaining_days) /$validity) *100 : 1;
                    $pers=  439.6 * $pers / 100;
            }
        ?>
        @if ($store_data?->store_sub?->is_trial == 0 && $store_data?->store_sub?->expiry_date_parsed && $store_data?->store_sub->expiry_date_parsed->subDays($subscription_deadline_warning_days)->isBefore(now()) && Request::is('store-panel'))

                <!--Always in header Renew -->
                <div class="renew-badge mb-20" id="renew-badge">
                    <div class="renew-content d-flex align-items-center">

                        <img src="{{asset('/public/assets/admin/img/timer.svg')}}" alt="">
                        <div class="txt">
                            {{ $subscription_deadline_warning_message != null ?  $subscription_deadline_warning_message : translate('Your subscription ending soon. Please renew to continue access') }}
                        </div>
                    </div>
                    <div>
                        <a href="{{route('vendor.subscriptionackage.subscriberDetail',['renew_now' => true])}}" class="btn btn--danger">{{ translate('Renew') }}</a>
                    </div>
                </div>



        @elseif ( Session::get('subscription_renew_close_btn') !== true && $store_data?->store_sub?->is_trial == 0  && $store_data?->store_sub?->expiry_date_parsed && $store_data?->store_sub->expiry_date_parsed->subDays($subscription_deadline_warning_days)->isBefore(now()) && !Request::is('store-panel'))


                <div class="renew-badge mb-20 hide-warning" id="renew-badge">
                    <div class="renew-content d-flex align-items-center">

                        <img src="{{asset('/public/assets/admin/img/timer.svg')}}" alt="">
                        <div class="txt">
                            {{ $subscription_deadline_warning_message != null ?  $subscription_deadline_warning_message : translate('Your subscription ending soon. Please renew to continue access') }}
                        </div>
                    </div>
                    <div>
                        @if ($store_data?->store_sub?->is_canceled == 1)
                        <a href="{{route('vendor.subscriptionackage.subscriberDetail',['open_plans' => true])}}" class="btn btn--danger">{{ translate('Change_Subscription') }}</a>
                        @else

                        <a href="{{route('vendor.subscriptionackage.subscriberDetail',['renew_now' => true])}}" class="btn btn--danger">{{ translate('Renew') }}</a>

                        @endif
                        <button  data-id="subscription_renew_close_btn" id="hide-warning"  class="btn btn-sm btn-primary add-to-session" >{{ translate('remind_me_later') }}</button>
                    </div>
                </div>
                <!-- Renew -->


        @endif
        @if ( Session::get('subscription_free_trial_close_btn') !== true && $store_data?->store_sub?->status == 1 && $store_data?->store_sub?->is_trial == 1 && $store_data?->store_sub?->is_canceled == 0)

        <div class="free-trial trial success-bg">
            <div class="inner-div">
                <div class="left">
                    <img src="{{asset('/public/assets/admin/img/icon-puck.svg')}}" alt="">
                    <div class="left-content">
                        <h6>{{ translate('Get the best experience of on demand service business') }}</h6>
                        <div>{{ translate('Run your on demand business with the most popular platform') }}</div>
                    </div>
                </div>
                <div class="right">
                    <a href="#" class="btn btn-2">
                        <span class="circle-progress-container">
                            <svg width="40" viewBox="0 0 160 160">
                                <circle r="70" cx="80" cy="80" fill="transparent" stroke="#ffffff20" stroke-width="12px"></circle>
                                <circle r="70" cx="80" cy="80" fill="transparent" stroke="#ffffff" stroke-width="12px" stroke-dasharray="439.6px" stroke-dashoffset="{{ $pers }}px"></circle>
                            </svg>
                            {{ Carbon\Carbon::now()->subDays(1)->diffInDays($store_data?->store_sub?->expiry_date_parsed->format('Y-m-d'), false) }}
                        </span>
                        {{translate('Days_left_in_free_trial')}}
                    </a>
                    <a href="{{route('vendor.subscriptionackage.subscriberDetail' ,['open_plans' => true])}}" class="btn btn-light">{{ translate('Choose_Subscription_Plan') }} <i class="tio-arrow-forward"></i></a>
                </div>

                <button type="button" data-id="subscription_free_trial_close_btn" class="trial-close add-to-session ">
                    <i class="tio-clear-circle"></i>
                </button>
            </div>
        </div>
        @elseif ($store_data?->store_sub == null && $store_data?->store_sub_update_application?->is_trial == 1)



        <div class="modal fade show trial-ended-modal" id="free-trial-modal">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body p-0">
                        <div class="trial-ended-modal-wrapper">
                            {{-- <button type="button" class="trial-ended-close-btn text-md-white" data-dismiss="modal">
                                <i class="tio-clear-circle"></i>
                            </button> --}}
                            <div class="trial-ended-modal-content align-self-center">
                                <h3 class="title">{{ translate('Your_Free_Trial_Has_Been_Ended') }}</h3>
                                <p class="mb-4">
                                    {{ translate('Purchase a subscription plan or contact with the admin to settle the payment and unblock the access to service.') }}
                                </p>
                                <a href="{{route('vendor.subscriptionackage.subscriberDetail' ,['open_plans' => true])}}" class="btn btn--primary">{{ translate('Choose Subscription Plan') }} <i class="tio-arrow-forward"></i></a>
                                <div class="blocked-subscription mt-5">
                                    <img src="{{asset('/public/assets/admin/img/WarningOctagon.svg')}}" alt="">
                                    <span>{{ translate('All Access to service has been blocked due to no active subscription') }}</span>
                                </div>
                            </div>
                            <div class="trial-ended-modal-img d-none d-md-block">
                                <img src="{{asset('/public/assets/admin/img/trial-ended-bg.png')}}" alt="">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>



        <div class="free-trial trial danger-bg">
            <div class="inner-div">
                <div class="left">
                    <img src="{{asset('/public/assets/admin/img/timer-2.svg')}}" alt="">
                    <div class="left-content">
                        <h6>{{ translate('Free_Trial_Has_Been_Ended') }}</h6>
                        <div>{{ translate('Get_a_subscription_plan_to_continue_with_your_business') }}</div>
                    </div>
                </div>
                <div class="right">
                    <a href="{{route('vendor.subscriptionackage.subscriberDetail' ,['open_plans' => true])}}" class="btn btn-light">{{ translate('Choose_Subscription_Plan') }} <i class="tio-arrow-forward"></i></a>
                </div>
                {{-- <button type="button" class="trial-close">
                    <i class="tio-clear-circle"></i>
                </button> --}}
            </div>
        </div>
        @elseif ( Session::get('subscription_cancel_close_btn') !== true &&  $store_data?->store_sub  && $store_data?->store_sub?->is_canceled == 1)
        <div class="free-trial trial danger-bg">
            <div class="inner-div">
                <div class="left">
                    <img src="{{asset('/public/assets/admin/img/timer-2.svg')}}" alt="">
                    <div class="left-content">
                        <h6>{{ translate('Your_Subscription_Has_Been_Cnaceled_by') }} {{ $store_data?->store_sub?->canceled_by == 'admin' ? translate($store_data?->store_sub?->canceled_by) : translate('Yourself') }}</h6>
                        <div>{{ translate('You_can_not_consume_your_subscription_after') }} {{ \App\CentralLogics\Helpers::date_format($store_data?->store_sub?->expiry_date_parsed) }}</div>
                    </div>
                </div>
                <div class="right">
                    <a href="" class="btn btn-2">
                        <span class="circle-progress-container">
                            <svg width="40" viewBox="0 0 160 160">
                                <circle r="70" cx="80" cy="80" fill="transparent" stroke="#ffffff20" stroke-width="12px"></circle>
                                <circle r="70" cx="80" cy="80" fill="transparent" stroke="#ffffff" stroke-width="12px" stroke-dasharray="439.6px" stroke-dashoffset="{{ $pers }}px"></circle>
                            </svg>
                            {{ Carbon\Carbon::now()->subDays(1)->diffInDays($store_data?->store_sub?->expiry_date_parsed->format('Y-m-d'), false) }}
                        </span>
                        {{translate('Days_left_in_this_subscription')}}
                    </a>
                    <a href="{{route('vendor.subscriptionackage.subscriberDetail' ,['open_plans' => true])}}" class="btn btn-light">{{ translate('Change_Subscription_Plan') }} <i class="tio-arrow-forward"></i></a>
                </div>

                <button type="button" data-id="subscription_cancel_close_btn" class="trial-close add-to-session ">
                    <i class="tio-clear-circle"></i>
                </button>
            </div>
        </div>

        @elseif ($store_data?->store_sub == null)
        <div class="free-trial trial danger-bg">
            <div class="inner-div">
                <div class="left">
                    <img src="{{asset('/public/assets/admin/img/timer-2.svg')}}" alt="">
                    <div class="left-content">
                        <h6>{{ translate('Your_Subscription_Has_Been_Expired_on') }} {{  \App\CentralLogics\Helpers::date_format($store_data?->store_sub_update_application?->expiry_date_parsed) }} </h6>
                        <div>{{ translate('Purchase a subscription plan or contact with the admin to settle the payment and unblock the access to service') }} </div>
                    </div>
                </div>
                <div class="right">

                    <a href="{{route('vendor.subscriptionackage.subscriberDetail' ,['open_plans' => true])}}" class="btn btn-light">{{ translate('Change/Renew Subscription_Plan') }} <i class="tio-arrow-forward"></i></a>
                </div>
{{--
                <button type="button" class="trial-close">
                    <i class="tio-clear-circle"></i>
                </button> --}}
            </div>
        </div>

        @endif

@endif

<script>
    document.addEventListener('DOMContentLoaded', function () {
                $(document).on('click', '.log-out', function () {
                Swal.fire({
                title: '{{ translate('Do you want to logout?') }}',
                showDenyButton: true,
                showCancelButton: true,
                confirmButtonColor: '#FC6A57',
                cancelButtonColor: '#363636',
                confirmButtonText: `{{ translate('yes')}}`,
                cancelButtonText: `{{ translate('Do_not_Logout')}}`,
                }).then((result) => {
                if (result.value) {
                location.href='{{route('logout')}}';
                } else{
                Swal.fire('{{ translate('messages.canceled') }}', '', 'info')
                }
            })
        });
                $(document).on('click', '.add-to-session', function () {
                    var session_data = $(this).data("id");
                    $.ajax({
                        url: '{{ route('vendor.subscriptionackage.addToSession') }}',
                        method: 'POST',
                        data: {
                            value: session_data,
                            _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {

                            }
                        });
                });
                $(document).on('click', '#hide-warning', function () {
                $('.hide-warning').hide();
                });


    });


</script>
