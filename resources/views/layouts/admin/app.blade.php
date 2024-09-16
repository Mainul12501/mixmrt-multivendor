<!DOCTYPE html>
<?php

$country=\App\Models\BusinessSetting::where('key','country')->first();
$countryCode= strtolower($country?$country->value:'auto');
?>
<html dir="{{ session()->get('site_direction') }}" lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{session()->get('site_direction') === 'rtl'?'active':'' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" id="csrf-token" content="{{ csrf_token() }}">
    <!-- Title -->
    <title>@yield('title')</title>
    <!-- Favicon -->
    @php($logo=\App\Models\BusinessSetting::where(['key'=>'icon'])->first())
    <link rel="shortcut icon" href="">
    <link rel="icon" type="image/x-icon" href="{{\App\CentralLogics\Helpers::get_image_helper($logo,'value', asset('storage/app/public/business/').'/' . $logo?->value, asset('public/assets/admin/img/160x160/img1.jpg') ,'business/' )}}">
    <!-- Font -->
    <link href="{{asset('public/assets/admin/css/fonts.css')}}" rel="stylesheet">
    <!-- CSS Implementing Plugins -->
    <link rel="stylesheet" href="{{asset('public/assets/admin/css/vendor.min.css')}}">
    <link rel="stylesheet" href="{{asset('public/assets/admin/vendor/icon-set/style.css')}}">
    <link rel="stylesheet" href="{{asset('public/assets/admin/css/custom.css')}}">
    <!-- CSS Front Template -->
    <link rel="stylesheet" href="{{asset('public/assets/admin/css/owl.min.css')}}">
    <link rel="stylesheet" href="{{asset('public/assets/admin/css/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset('public/assets/admin/css/theme.minc619.css?v=1.0')}}">
    <link rel="stylesheet" href="{{asset('public/assets/admin/css/bootstrap-tour-standalone.min.css')}}">
    <link rel="stylesheet" href="{{asset('public/assets/admin/css/emogi-area.css')}}">
    <link rel="stylesheet" href="{{asset('public/assets/admin/css/style.css')}}">

    <link rel="stylesheet" href="{{asset('public/assets/admin/intltelinput/css/intlTelInput.css')}}">

    @stack('css_or_js')

    <script src="{{asset('public/assets/admin/vendor/hs-navbar-vertical-aside/hs-navbar-vertical-aside-mini-cache.js')}}"></script>
    <link rel="stylesheet" href="{{asset('public/assets/admin/css/toastr.css')}}">
</head>

<body class="footer-offset">
    @if (env('APP_MODE')=='demo')
        <div class="direction-toggle">
            <i class="tio-settings"></i>
            <span></span>
        </div>
    @endif

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div id="loading" class="initial-hidden">
                <div class="loader--inner">
                    <img width="200" src="{{asset('public/assets/admin/img/loader.gif')}}" alt="image">
                </div>
            </div>
        </div>
    </div>
</div>
@if (!isset($module_type))
@php($module_type = Config::get('module.current_module_type'))
@endif

<!-- Builder -->
@include('layouts.admin.partials._front-settings')
<!-- End Builder -->

<!-- JS Preview mode only -->
@include('layouts.admin.partials._header')

@if(Request::is('admin/payment/configuration*') || Request::is('admin/sms/configuration*'))
@php($module_type = 'settings')
@endif

@include("layouts.admin.partials._sidebar_{$module_type}")

<!-- END ONLY DEV -->

<main id="content" role="main" class="main pointer-event">
    <!-- Content -->
@yield('content')
<!-- End Content -->

    <!-- Footer -->
@include('layouts.admin.partials._footer')
<!-- End Footer -->

    <div class="modal fade" id="popup-modal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="text-center">
                                <h2>
                                    <i class="tio-shopping-cart-outlined"></i> {{translate('messages.You have new order, Check Please.')}}
                                </h2>
                                <hr>
                                <button class="btn btn-primary check-order">{{translate('messages.Ok, let me check')}}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="toggle-modal">
        <div class="modal-dialog status-warning-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true" class="tio-clear"></span>
                    </button>
                </div>
                <div class="modal-body pb-5 pt-0">
                    <div class="max-349 mx-auto mb-20">
                        <div>
                            <div class="text-center">
                                <img id="toggle-image" alt="" class="mb-20">
                                <h5 class="modal-title" id="toggle-title"></h5>
                            </div>
                            <div class="text-center" id="toggle-message">
                            </div>
                        </div>
                        <div class="btn--container justify-content-center">
                            <button type="button" id="toggle-ok-button" class="btn btn--primary min-w-120 confirm-Toggle" data-dismiss="modal" >{{translate('Ok')}}</button>
                            <button id="reset_btn" type="reset" class="btn btn--cancel min-w-120" data-dismiss="modal">
                                {{translate("Cancel")}}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="toggle-status-modal">
        <div class="modal-dialog status-warning-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true" class="tio-clear"></span>
                    </button>
                </div>
                <div class="modal-body pb-5 pt-0">
                    <div class="max-349 mx-auto mb-20">
                        <div>
                            <div class="text-center">
                                <img id="toggle-status-image" alt="" class="mb-20">
                                <h5 class="modal-title" id="toggle-status-title"></h5>
                            </div>
                            <div class="text-center" id="toggle-status-message">
                            </div>
                        </div>
                        <div class="btn--container justify-content-center">
                            <button type="button" id="toggle-status-ok-button" class="btn btn--primary min-w-120 confirm-Status-Toggle" data-dismiss="modal" >{{translate('Ok')}}</button>
                            <button id="reset_btn" type="reset" class="btn btn--cancel min-w-120" data-dismiss="modal">
                                {{translate("Cancel")}}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal" id="instruction-modal">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-body">
                    <button type="button" class="close instruction-Modal-Close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    <div class="embed-responsive embed-responsive-16by9">
                        <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/0sus46BflpU" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                      </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal" id="email-modal">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-body">
                    <button type="button" class="close email-Modal-Close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    <div class="embed-responsive embed-responsive-16by9">
                        <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/_BIHsClZtOE" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                      </div>
                </div>
            </div>
        </div>
    </div>


<!-- ========== END MAIN CONTENT ========== -->

<!-- ========== END SECONDARY CONTENTS ========== -->
<script src="{{asset('public/assets/admin')}}/js/custom.js"></script>
<script src="{{asset('public/assets/admin')}}/js/firebase.min.js"></script>
<!-- JS Implementing Plugins -->

@stack('script')
<!-- JS Front -->
<script src="{{asset('public/assets/admin')}}/js/vendor.min.js"></script>
<script src="{{asset('public/assets/admin')}}/js/theme.min.js"></script>
<script src="{{asset('public/assets/admin')}}/js/sweet_alert.js"></script>
<script src="{{asset('public/assets/admin')}}/js/bootstrap-tour-standalone.min.js"></script>
<script src="{{asset('public/assets/admin/js/owl.min.js')}}"></script>
<script src="{{asset('public/assets/admin')}}/js/font-awesome.min.js"></script>
<script src="{{asset('public/assets/admin')}}/js/emogi-area.js"></script>
<script src="{{asset('public/assets/admin')}}/js/toastr.js"></script>
<script src="{{asset('public/assets/admin/js/app-blade/admin.js')}}"></script>


{!! Toastr::message() !!}

@if ($errors->any())
    <script>
        @foreach($errors->all() as $error)
        toastr.error('{{translate($error)}}', Error, {
            CloseButton: true,
            ProgressBar: true
        });
        @endforeach
    </script>
@endif
<!-- JS Plugins Init. -->


@stack('script_2')
<script>
    let baseUrl = '{{ url('/') }}';
</script>

<script src="{{asset('public/assets/admin/js/view-pages/common.js')}}"></script>
<audio id="myAudio">
    <source src="{{asset('public/assets/admin/sound/notification.mp3')}}" type="audio/mpeg">
</audio>
<script>
    var audio = document.getElementById("myAudio");
function playAudio() {
    audio.play();
}

function pauseAudio() {
    audio.pause();
}
</script>
<script>
    var audio = document.getElementById("myAudio");
    function playAudio() {
        audio.play();
    }

    function pauseAudio() {
        audio.pause();
    }
"use strict";


    @php($modules = \App\Models\Module::Active()->get())

    @if(isset($modules) && ($modules->count()<1))
    $('#instruction-modal').show();
    @endif



    $('.restart-Tour').on('click',function (){
        @if(isset($modules) && ($modules->count()>0))
            tour.restart();
            $('body').css('overflow','hidden')
        @endif
    });


    $('.log-out').on('click',function (){

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


    function route_alert(route, message, title="{{translate('messages.are_you_sure')}}") {
        Swal.fire({
            title: title,
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
                location.href = route;
            }
        })
    }

    $('.form-alert').on('click',function (){
        let id = $(this).data('id')
        let message = $(this).data('message')
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
                $('#'+id).submit()
            }
        })
    })

    $('.canceled-status').on('click',function (){
        let route = $(this).data('url');
        let message = $(this).data('message');
        let processing = $(this).data('processing')??false;
        cancelled_status(route, message, processing);
    })

    function cancelled_status(route, message, processing = false) {
        Swal.fire({
            //text: message,
            title: '<?php echo e(translate('messages.Are you sure ?')); ?>',
            type: 'warning',
            showCancelButton: true,
            cancelButtonColor: 'default',
            confirmButtonColor: '#FC6A57',
            cancelButtonText: '<?php echo e(translate('messages.Cancel')); ?>',
            confirmButtonText: '<?php echo e(translate('messages.submit')); ?>',
            inputPlaceholder: "<?php echo e(translate('Enter_a_reason')); ?>",
            input: 'text',
            html: message + '<br/>'+'<label><?php echo e(translate('Enter_a_reason')); ?></label>',
            inputValue: processing,
            preConfirm: (note) => {
                location.href = route + '&note=' + note;
            },
            allowOutsideClick: () => !Swal.isLoading()
        })
    }

    function set_mail_filter(url, id, filter_by) {
        Swal.fire({
            title: '{{ translate('messages.Are you sure?') }}',
            text: 'Please save changes before switching template',
            type: 'warning',
            showCancelButton: true,
            cancelButtonColor: 'default',
            confirmButtonColor: '#FC6A57',
            cancelButtonText: '{{ translate('messages.no') }}',
            confirmButtonText: '{{ translate('messages.Yes') }}',
            reverseButtons: true
        }).then((result) => {
            if (result.value) {
                let nurl = new URL(url);
                nurl.searchParams.set(filter_by, id);
                location.href = nurl;
            }
        })
    }


    function copy_text(copyText) {
        navigator.clipboard.writeText(copyText);
        toastr.success('{{translate('messages.text_copied')}}', {
            CloseButton: true,
            ProgressBar: true
        });
    }

    $(document).on('ready', function(){
        $(".direction-toggle").on("click", function () {
            if($('html').hasClass('active')){
                $('html').removeClass('active')
                setDirection(1);
            }else {
                setDirection(0);
                $('html').addClass('active')
            }
        });

        if ($('html').attr('dir') === "rtl") {
            $(".direction-toggle").find('span').text('Toggle LTR')
        } else {
            $(".direction-toggle").find('span').text('Toggle RTL')
        }

        function setDirection(status) {
            if (status === 1) {
                $("html").attr('dir', 'ltr');
                $(".direction-toggle").find('span').text('Toggle RTL')
            } else {
                $("html").attr('dir', 'rtl');
                $(".direction-toggle").find('span').text('Toggle LTR')
            }
            $.get({
                    url: '{{ route('admin.business-settings.site_direction') }}',
                    dataType: 'json',
                    data: {
                        status: status,
                    },
                    success: function() {
                        alert(ok);
                    },

                });
            }
        });

    @php($fcm_credentials = \App\CentralLogics\Helpers::get_business_settings('fcm_credentials'))
    let firebaseConfig = {
        apiKey: "{{isset($fcm_credentials['apiKey']) ? $fcm_credentials['apiKey'] : ''}}",
        authDomain: "{{isset($fcm_credentials['authDomain']) ? $fcm_credentials['authDomain'] : ''}}",
        projectId: "{{isset($fcm_credentials['projectId']) ? $fcm_credentials['projectId'] : ''}}",
        storageBucket: "{{isset($fcm_credentials['storageBucket']) ? $fcm_credentials['storageBucket'] : ''}}",
        messagingSenderId: "{{isset($fcm_credentials['messagingSenderId']) ? $fcm_credentials['messagingSenderId'] : ''}}",
        appId: "{{isset($fcm_credentials['appId']) ? $fcm_credentials['appId'] : ''}}",
        measurementId: "{{isset($fcm_credentials['measurementId']) ? $fcm_credentials['measurementId'] : ''}}"
    };
    firebase.initializeApp(firebaseConfig);
    const messaging = firebase.messaging();

    function startFCM() {
        messaging
            .requestPermission()
            .then(function() {
                return messaging.getToken();
            })
            .then(function(token) {
                // console.log('FCM Token:', token);
                // Send the token to your backend to subscribe to topic
                subscribeTokenToBackend(token, 'admin_message');
            }).catch(function(error) {
            console.error('Error getting permission or token:', error);
        });
    }

    function subscribeTokenToBackend(token, topic) {
        fetch('{{url('/')}}/subscribeToTopic', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ token: token, topic: topic })
        }).then(response => {
            if (response.status < 200 || response.status >= 400) {
                return response.text().then(text => {
                    throw new Error(`Error subscribing to topic: ${response.status} - ${text}`);
                });
            }
            console.log(`Subscribed to "${topic}"`);
        }).catch(error => {
            console.error('Subscription error:', error);
        });
    }

    {{--function startFCM() {--}}

    {{--    messaging--}}
    {{--        .requestPermission()--}}
    {{--        .then(function() {--}}
    {{--            return messaging.getToken()--}}
    {{--        })--}}
    {{--        .then(function(response) {--}}
    {{--            subscribeTokenToTopic(response, 'admin_message');--}}
    {{--            console.log('subscribed');--}}
    {{--        }).catch(function(error) {--}}
    {{--            console.log(error);--}}
    {{--        });--}}
    {{--}--}}
    {{--@php($key = \App\Models\BusinessSetting::where('key', 'push_notification_key')->first())--}}

    {{--function subscribeTokenToTopic(token, topic) {--}}
    {{--    fetch('https://iid.googleapis.com/iid/v1/' + token + '/rel/topics/' + topic, {--}}
    {{--        method: 'POST',--}}
    {{--        headers: new Headers({--}}
    {{--            'Authorization': 'key={{ $key ? $key->value : '' }}'--}}
    {{--        })--}}
    {{--    }).then(response => {--}}
    {{--        if (response.status < 200 || response.status >= 400) {--}}
    {{--            throw 'Error subscribing to topic: ' + response.status + ' - ' + response.text();--}}
    {{--        }--}}
    {{--        console.log('Subscribed to "' + topic + '"');--}}
    {{--    }).catch(error => {--}}
    {{--        console.error(error);--}}
    {{--    })--}}
    {{--}--}}

    function conversationList() {
        $.ajax({
                url: "{{ route('admin.message.list') }}",
                success: function(data) {
                    $('#conversation-list').empty();
                    $("#conversation-list").append(data.html);
                    let user_id = getUrlParameter('user');
                $('.customer-list').removeClass('conv-active');
                $('#customer-' + user_id).addClass('conv-active');
                }
            })
    }

    function conversationView() {
        let conversation_id = getUrlParameter('conversation');
        let user_id = getUrlParameter('user');
        let url= '{{url('/')}}/admin/message/view/'+conversation_id+'/' + user_id;
        $.ajax({
            url: url,
            success: function(data) {
                $('#view-conversation').html(data.view);
            }
        })
    }



    function vendorConversationView() {
        let conversation_id = getUrlParameter('conversation');
        let user_id = getUrlParameter('user');
        let url= '{{url('/')}}/admin/store/message/'+conversation_id+'/' + user_id;
        $.ajax({
            url: url,
            success: function(data) {
                $('#vendor-view-conversation').html(data.view);
            }
        })
    }

    function dmConversationView() {
        let conversation_id = getUrlParameter('conversation');
        let user_id = getUrlParameter('user');
        let url= '{{url('/')}}/admin/users/delivery-man/message/'+conversation_id+'/' + user_id;
        $.ajax({
            url: url,
            success: function(data) {
                $('#dm-view-conversation').html(data.view);
            }
        })
    }

    let new_order_type='store_order';
    let new_module_id=null;
    let admin_zone_id=null;
    let admin_role_id=null;

    @php($order_notification_type = \App\Models\BusinessSetting::where('key', 'order_notification_type')->first())
    @php($order_notification_type = $order_notification_type ? $order_notification_type->value : 'firebase')
    messaging.onMessage(function(payload) {
        console.log(payload.data)
        if(payload.data.order_id && payload.data.type == "order_request"){
                @php($admin_order_notification = \App\Models\BusinessSetting::where('key', 'admin_order_notification')->first())
                @php($admin_order_notification = $admin_order_notification ? $admin_order_notification->value : 0)
                @if (\App\CentralLogics\Helpers::module_permission_check('order') && $admin_order_notification && $order_notification_type == 'firebase')
                new_order_type = payload.data.order_type
                new_module_id = payload.data.module_id
                admin_zone_id = '<?php echo auth()->guard('admin')->user()->zone_id ;?>';
                admin_role_id = '<?php echo auth()->guard('admin')->user()->role_id ;?>';
                if(admin_role_id === '1'){
                    playAudio();
                    $('#popup-modal').appendTo("body").modal('show');
                }
                if((admin_role_id !== '1') && (admin_zone_id === payload.data.zone_id)){
                    playAudio();
                    $('#popup-modal').appendTo("body").modal('show');
                }
                @endif

        }else{
            if (window.location.href.includes('message/list?conversation')) {
                let conversation_id = getUrlParameter('conversation');
                let user_id = getUrlParameter('user');
                let url = '{{url('/')}}/admin/message/view/' + conversation_id + '/' + user_id;
                console.log(url);
                $.ajax({
                    url: url,
                    success: function (data) {
                        $('#view-conversation').html(data.view);
                    }
                })
            }
            toastr.success('New message arrived', {
                CloseButton: true,
                ProgressBar: true
            });
            if($('#conversation-list').scrollTop() === 0){
                conversationList();
            }
        }
    });

    @if(\App\CentralLogics\Helpers::module_permission_check('order') && $order_notification_type == 'manual')
        @php($admin_order_notification=\App\Models\BusinessSetting::where('key','admin_order_notification')->first())
        @php($admin_order_notification=$admin_order_notification?$admin_order_notification->value:0)
        @if($admin_order_notification)
            setInterval(function () {
                $.get({
                    url: '{{route('admin.get-store-data')}}',
                    dataType: 'json',
                    success: function (response) {
                        let data = response.data;
                        new_order_type = data.type;
                        new_module_id = data.module_id;
                        if (data.new_order > 0) {
                            playAudio();
                            $('#popup-modal').appendTo("body").modal('show');
                        }else{
                            $('#popup-modal').appendTo("body").modal('hide');
                        }
                    },
                });
            }, 10000);
        @endif
    @endif

    $(document).on('click', '.check-order', function () {
        if(new_order_type === 'parcel')
        {
            location.href = '{{url('/')}}/admin/parcel/orders/all?module_id=' + new_module_id;
        }
        else
        {
            location.href = '{{url('/')}}/admin/order/list/all?module_id=' + new_module_id;
        }
    });

    startFCM();
    conversationList();
    if(getUrlParameter('conversation')){
        conversationView();
        vendorConversationView();
        dmConversationView();
    }


    $(document).on('click', '.call-demo', function () {
        @if(env('APP_MODE') =='demo')
            toastr.info('{{ translate('Update option is disabled for demo!') }}', {
                CloseButton: true,
                ProgressBar: true
            });
        @endif
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
</script>
        <script src="{{asset('public/assets/admin/intltelinput/js/intlTelInput.min.js')}}"></script>

        <script>

    //     const input = document.querySelector('input[type="tel"]');
    // const iti = window.intlTelInput(input, {
    //     utilsScript: "",
    //     initialCountry: "{{$countryCode}}",
    //     autoInsertDialCode: true,
    //     autoPlaceholder: 'polite',
    //     // formatOnDisplay: true,
    //     // placeholderNumberType: "MOBILE",
    //     // separateDialCode: true,
    //     // showSelectedDialCode: true,
    //     // allowDropdown : true,
    //     // hiddenInput: "phone"
    // });

    const inputs = document.querySelectorAll('input[type="tel"]');

    inputs.forEach(input => {
        window.intlTelInput(input, {
            initialCountry: "{{$countryCode}}",
            utilsScript: "{{ asset('public/assets/admin/intltelinput/js/utils.js') }}",
            autoInsertDialCode: true,
            nationalMode: false,
            formatOnDisplay: false,
        });
    });


  function keepNumbersAndPlus(inputString) {
    let regex = /[0-9+]/g;
    let filteredString = inputString.match(regex);
    let result = filteredString ? filteredString.join('') : '';
    return result;
}

$(document).on('keyup', 'input[type="tel"]', function () {
        let input = $(this).val();
        $(this).val(keepNumbersAndPlus(input));
        });


</script>

<script>
    if (/MSIE \d|Trident.*rv:/.test(navigator.userAgent)) document.write('<script src="{{asset('public/assets/admin')}}/vendor/babel-polyfill/polyfill.min.js"><\/script>');
</script>
</body>
</html>
