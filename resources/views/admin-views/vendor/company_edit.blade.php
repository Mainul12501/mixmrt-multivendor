@extends('layouts.admin.app')

@section('title','Update courier company info')
@push('css_or_js')
    <link rel="stylesheet" href="{{asset('/public/assets/admin/css/intlTelInput.css')}}" />
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('public/assets/admin/img/edit.png')}}" class="w--26" alt="">
                </span>
                <span>{{translate('messages.update_courier_company')}}</span>
            </h1>
        </div>
        @php
        $delivery_time_start = preg_match('([0-9]+[\-][0-9]+\s[min|hours|days])', $store->delivery_time??'')?explode('-',$store->delivery_time)[0]:10;
        $delivery_time_end = preg_match('([0-9]+[\-][0-9]+\s[min|hours|days])', $store->delivery_time??'')?explode(' ',explode('-',$store->delivery_time)[1])[0]:30;
        $delivery_time_type = preg_match('([0-9]+[\-][0-9]+\s[min|hours|days])', $store->delivery_time??'')?explode(' ',explode('-',$store->delivery_time)[1])[1]:'min';
    @endphp
        @php($language=\App\Models\BusinessSetting::where('key','language')->first())
        @php($language = $language->value ?? null)
        @php($defaultLang = 'en')
        <!-- End Page Header -->
        <form action="{{route('admin.company.update',[$store['id']])}}" method="post" class="js-validate"
                enctype="multipart/form-data" id="vendor_form">
            @csrf

            <div class="row g-2">
                <div class="col-lg-6">
                    <div class="card shadow--card-2">
                        <div class="card-body">
                            @if($language)
                            <ul class="nav nav-tabs mb-4">
                                <li class="nav-item">
                                    <a class="nav-link lang_link active"
                                    href="#"
                                    id="default-link">{{ translate('Default') }}</a>
                                </li>
                                @foreach (json_decode($language) as $lang)
                                    <li class="nav-item">
                                        <a class="nav-link lang_link"
                                            href="#"
                                            id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                    </li>
                                @endforeach
                            </ul>
                            @endif
                            @if ($language)
                            <div class="lang_form"
                            id="default-form">
                                <div class="form-group">
                                    <label class="input-label"
                                        for="default_name">{{ translate('messages.name') }}
                                        ({{ translate('messages.Default') }})
                                    </label>
                                    <input type="text" name="name[]" id="default_name"
                                        class="form-control" placeholder="{{ translate('messages.courier_company_name') }}" value="{{$store->getRawOriginal('name')}}"
                                        required
                                         >
                                </div>
                                <input type="hidden" name="lang[]" value="default">
                                <div class="form-group mb-0">
                                    <label class="input-label"
                                        for="exampleFormControlInput1">{{ translate('messages.address') }} ({{ translate('messages.default') }})</label>
                                    <textarea type="text" name="address[]" placeholder="{{translate('messages.courier_company')}}" class="form-control min-h-90px ckeditor">{{$store->getRawOriginal('address')}}</textarea>
                                </div>
                            </div>
                                @foreach (json_decode($language) as $lang)
                                <?php
                                    if(count($store['translations'])){
                                        $translate = [];
                                        foreach($store['translations'] as $t)
                                        {
                                            if($t->locale == $lang && $t->key=="name"){
                                                $translate[$lang]['name'] = $t->value;
                                            }
                                            if($t->locale == $lang && $t->key=="address"){
                                                $translate[$lang]['address'] = $t->value;
                                            }
                                        }
                                    }
                                ?>
                                    <div class="d-none lang_form"
                                        id="{{ $lang }}-form">
                                        <div class="form-group">
                                            <label class="input-label"
                                                for="{{ $lang }}_name">{{ translate('messages.name') }}
                                                ({{ strtoupper($lang) }})
                                            </label>
                                            <input type="text" name="name[]" id="{{ $lang }}_name"
                                                class="form-control" value="{{ $translate[$lang]['name']??'' }}" placeholder="{{ translate('messages.courier_company') }}"
                                                 >
                                        </div>
                                        <input type="hidden" name="lang[]" value="{{ $lang }}">
                                        <div class="form-group mb-0">
                                            <label class="input-label"
                                                for="exampleFormControlInput1">{{ translate('messages.address') }} ({{ strtoupper($lang) }})</label>
                                            <textarea type="text" name="address[]" placeholder="{{translate('messages.courier_company')}}" class="form-control min-h-90px ckeditor">{{ $translate[$lang]['address']??'' }}</textarea>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div id="default-form">
                                    <div class="form-group">
                                        <label class="input-label"
                                            for="exampleFormControlInput1">{{ translate('messages.name') }} ({{ translate('messages.default') }})</label>
                                        <input type="text" name="name[]" class="form-control"
                                            placeholder="{{ translate('messages.courier_company_name') }}" required>
                                    </div>
                                    <input type="hidden" name="lang[]" value="default">
                                    <div class="form-group mb-0">
                                        <label class="input-label"
                                            for="exampleFormControlInput1">{{ translate('messages.address') }}
                                        </label>
                                        <textarea type="text" name="address[]" placeholder="{{translate('messages.courier_company')}}" class="form-control min-h-90px ckeditor"></textarea>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card shadow--card-2">
                        <div class="card-header">
                            <h5 class="card-title">
                                <span class="card-header-icon mr-1"><i class="tio-dashboard"></i></span>
                                <span>{{translate('Courier Company Logo & Covers')}}</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-wrap flex-sm-nowrap __gap-12px">
                                <label class="__custom-upload-img mr-lg-5">
                                    @php($logo = \App\Models\BusinessSetting::where('key', 'logo')->first())
                                    @php($logo = $logo->value ?? '')
                                    <label class="form-label">
                                        {{ translate('logo') }} <span class="text--primary">({{ translate('1:1') }})</span>
                                    </label>
                                    <div class="text-center">
                                        <img class="img--110 min-height-170px min-width-170px onerror-image" id="viewer"
                                        data-onerror-image="{{ asset('public/assets/admin/img/upload.png') }}"
                                        src="{{ \App\CentralLogics\Helpers::onerror_image_helper(
                                            $store->logo ?? '',
                                            asset('storage/app/public/store').'/'.$store->logo ?? '',
                                            asset('public/assets/admin/img/upload.png'),
                                            'store/'
                                        ) }}"
                                            alt="logo image" />
                                    </div>
                                    <input type="file" name="logo" id="customFileEg1" class="custom-file-input"
                                        accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" required>
                                </label>

                                <label class="__custom-upload-img">
                                    @php($icon = \App\Models\BusinessSetting::where('key', 'icon')->first())
                                    @php($icon = $icon->value ?? '')
                                    <label class="form-label">
                                        {{ translate('Store Cover') }}  <span class="text--primary">({{ translate('2:1') }})</span>
                                    </label>
                                    <div class="text-center">
                                        <img class="img--vertical min-height-170px min-width-170px onerror-image" id="coverImageViewer"
                                        data-onerror-image="{{ asset('public/assets/admin/img/upload-img.png') }}"
                                        src="{{ \App\CentralLogics\Helpers::onerror_image_helper(
                                            $store->cover_photo ?? '',
                                            asset('storage/app/public/store/cover').'/'.$store->cover_photo ?? '',
                                            asset('public/assets/admin/img/upload-img.png'),
                                            'store/cover/'
                                        ) }}"
                                            alt="Fav icon" />
                                    </div>
                                    <input type="file" name="cover_photo" id="coverImageUpload"  class="custom-file-input"
                                        accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                 <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title m-0 d-flex align-items-center">
                                <img class="mr-2 align-self-start w--20" src="{{asset('public/assets/admin/img/resturant.png')}}" alt="instructions">
                                <span>{{translate('courier_company_information')}}</span>
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="row g-3 my-0">
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="input-label" for="choice_zones">{{translate('messages.zone')}}<span
                                                class="form-label-secondary" data-toggle="tooltip" data-placement="right"
        data-original-title="{{translate('messages.select_zone_for_map')}}"><img src="{{asset('/public/assets/admin/img/info-circle.svg')}}" alt="{{translate('messages.select_zone_for_map')}}"></span></label>
                                        <select name="zone_id" id="choice_zones" data-placeholder="{{translate('messages.select_zone')}}"
                                                class="form-control js-select2-custom get_zone_data">
                                            @foreach(\App\Models\Zone::active()->get() as $zone)
                                                @if(isset(auth('admin')->user()->zone_id))
                                                    @if(auth('admin')->user()->zone_id == $zone->id)
                                                        <option value="{{$zone->id}}" {{$store->zone_id == $zone->id? 'selected': ''}}>{{$zone->name}}</option>
                                                    @endif
                                                @else
                                                    <option value="{{$zone->id}}" {{$store->zone_id == $zone->id? 'selected': ''}}>{{$zone->name}}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="input-label" for="latitude">{{translate('messages.latitude')}}<span
                                                class="form-label-secondary" data-toggle="tooltip" data-placement="right"
        data-original-title="{{translate('messages.store_lat_lng_warning')}}"><img src="{{asset('/public/assets/admin/img/info-circle.svg')}}" alt="{{translate('messages.store_lat_lng_warning')}}"></span></label>
                                        <input type="text" id="latitude"
                                                name="latitude" class="form-control"
                                                placeholder="{{ translate('messages.Ex:') }} -94.22213" value="{{$store->latitude}}" required readonly>
                                    </div>
                                    <div class="form-group mb-5">
                                        <label class="input-label" for="longitude">{{translate('messages.longitude')}}<span
                                                class="form-label-secondary" data-toggle="tooltip" data-placement="right"
        data-original-title="{{translate('messages.store_lat_lng_warning')}}"><img src="{{asset('/public/assets/admin/img/info-circle.svg')}}" alt="{{translate('messages.store_lat_lng_warning')}}"></span></label>
                                        <input type="text"
                                                name="longitude" class="form-control"
                                                placeholder="{{ translate('messages.Ex:') }} 103.344322" id="longitude" value="{{$store->longitude}}" required readonly>
                                    </div>
                                </div>
                                <div class="col-lg-8">
                                    <input id="pac-input" class="controls rounded"
                                        data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('messages.search_your_location_here') }}" type="text" placeholder="{{ translate('messages.search_here') }}" />
                                    <div id="map"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> 
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title m-0 d-flex align-items-center">
                                <span class="card-header-icon mr-2"><i class="tio-user"></i></span>
                                <span>{{translate('messages.owner_information')}}</span>
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-group mb-0">
                                        <label class="input-label" for="f_name">{{translate('messages.first_name')}}</label>
                                        <input type="text" name="f_name" class="form-control" placeholder="{{translate('messages.first_name')}}"
                                                value="{{$store->vendor->f_name}}"  required>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-group mb-0">
                                        <label class="input-label" for="l_name">{{translate('messages.last_name')}}</label>
                                        <input type="text" name="l_name" class="form-control" placeholder="{{translate('messages.last_name')}}"
                                        value="{{$store->vendor->l_name}}"  required>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-group mb-0">
                                        <label class="input-label" for="phone">{{translate('messages.phone')}}</label>
                                        <input type="text" id="phone" name="phone" class="form-control"
                                        placeholder="{{ translate('messages.Ex:') }} 017********" value="{{$store->vendor->phone}}"
                                        required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


        <div class="col-lg-12">
                    <div class="card shadow--card-2">
                        <div class="card-header">
                            <h5 class="card-title">
                                <span class="card-header-icon mr-1"><i class="tio-dashboard"></i></span>
                                <span>{{translate('Additional Information')}}</span>
                            </h5>
                        </div>
                <div class="card-body d-flex justify-content-center align-items-center">

                    <div class="row g-12 w-100 d-flex justify-content-start align-items-center">


                        <div class="col-md-4 col-lg-4 col-sm-12">
                            <div class="form-group">
                                <label class="input-label"
                                    for="tex_id">{{ translate('messages.Tax_Id') }}</label>
                                <input type="text" id="tax_id"
                                    name="tax_id" class="form-control __form-control"
                                    placeholder="{{ translate('messages.Tax_Id') }}"
                                    value="{{ $store->tax_id }}" required>
                            </div>
                        </div>

                        <div class="col-md-4 col-lg-4 col-sm-12">
                            <div class="form-group">
                                <label class="input-label"
                                    for="reg_no">{{ translate('messages.registration_number') }}</label>
                                <input type="text" id="reg_no"
                                    name="register_no" class="form-control __form-control"
                                    placeholder="{{ translate('messages.registration_number') }}"
                                    value="{{ $store->register_no }}" required>
                            </div>
                        </div>

                        <div class="col-2 card p-5 mx-5">
                            <label class="__custom-upload-img">
                                <label class="form-label">
                                    {{ translate('tax_document') }}
                                </label>

                                <div class="text-center">
                                    <img class="img--110 onerror-image" id="tax_document_view"
                                        data-onerror-image="{{ asset('public/assets/admin/img/important-file.png') }}"
                                        src="{{\App\CentralLogics\Helpers::onerror_file_or_image_helper($store['tax_document'], asset('storage/app/public/store/').'/'.$store['tax_document'], asset('public/assets/admin/img/important-file.png'), 'store/') }}"
                                        alt="tax_document" />
                                </div>

                                <input type="file" name="tax_document" id="tax_document" class="custom-file-input"
                                    accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff, .pdf, .doc, .docx|image/*, application/pdf, application/msword, application/vnd.openxmlformats-officedocument.wordprocessingml.document" required>
                            </label>
                        </div>
                        <div class="col-2 card p-5 mx-5">
                            <label class="__custom-upload-img">
                                <label class="form-label">
                                    {{ translate('registration_document') }}
                                </label>

                                <div class="text-center">
                                    <img class="img--110 onerror-image" id="registration_document_view"
                                        data-onerror-image="{{ asset('public/assets/admin/img/important-file.png') }}"
                                        src="{{\App\CentralLogics\Helpers::onerror_file_or_image_helper($store['registration_document'], asset('storage/app/public/store/').'/'.$store['registration_document'], asset('public/assets/admin/img/important-file.png'), 'store/') }}"
                                        alt="registration_document" />
                                </div>

                                <input type="file" name="registration_document" id="registration_document" class="custom-file-input"
                                    accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff, .pdf, .doc, .docx|image/*, application/pdf, application/msword, application/vnd.openxmlformats-officedocument.wordprocessingml.document" required>
                            </label>
                        </div>
                        <div class="col-2 card p-5 mx-5">
                            <label class="__custom-upload-img">
                                <label class="form-label">
                                    {{ translate('agreement_document') }}
                                </label>

                                <div class="text-center">
                                    <img class="img--110 onerror-image" id="agreement_document_view"
                                        data-onerror-image="{{ asset('public/assets/admin/img/important-file.png') }}"
                                        src="{{\App\CentralLogics\Helpers::onerror_file_or_image_helper($store['agreement_document'], asset('storage/app/public/store/').'/'.$store['agreement_document'], asset('public/assets/admin/img/important-file.png'), 'store/') }}"
                                        alt="agreement_document" />
                                </div>

                                <input type="file" name="agreement_document" id="agreement_document" class="custom-file-input"
                                    accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff, .pdf, .doc, .docx|image/*, application/pdf, application/msword, application/vnd.openxmlformats-officedocument.wordprocessingml.document" required>
                            </label>
                        </div>


                    </div>

                </div>
                    </div>
            </div>


                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title m-0 d-flex align-items-center">
                                <span class="card-header-icon mr-2"><i class="tio-user"></i></span>
                                <span>{{translate('messages.account_information')}}</span>
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-group mb-0">
                                        <label class="input-label" for="exampleFormControlInput1">{{translate('messages.email')}}</label>
                                        <input type="email" name="email" class="form-control" placeholder="{{ translate('messages.Ex:') }} ex@example.com" value="{{$store->email}}" required>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <div class="js-form-message form-group mb-0">
                                        <label class="input-label" for="signupSrPassword">{{ translate('password') }}<span class="form-label-secondary" data-toggle="tooltip" data-placement="right"
                                 data-original-title="{{ translate('messages.Must_contain_at_least_one_number_and_one_uppercase_and_lowercase_letter_and_symbol,_and_at_least_8_or_more_characters') }}"><img src="{{ asset('/public/assets/admin/img/info-circle.svg') }}" alt="{{ translate('messages.Must_contain_at_least_one_number_and_one_uppercase_and_lowercase_letter_and_symbol,_and_at_least_8_or_more_characters') }}"></span></label>

                                        <div class="input-group input-group-merge">
                                            <input type="password" class="js-toggle-password form-control" name="password" id="signupSrPassword" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="{{ translate('messages.Must_contain_at_least_one_number_and_one_uppercase_and_lowercase_letter_and_symbol,_and_at_least_8_or_more_characters') }}"
                                            placeholder="{{ translate('messages.password_length_placeholder', ['length' => '8+']) }}"
                                            aria-label="8+ characters required"
                                            data-msg="Your password is invalid. Please try again."
                                            data-hs-toggle-password-options='{
                                            "target": [".js-toggle-password-target-1", ".js-toggle-password-target-2"],
                                            "defaultClass": "tio-hidden-outlined",
                                            "showClass": "tio-visible-outlined",
                                            "classChangeTarget": ".js-toggle-passowrd-show-icon-1"
                                            }'>
                                            <div class="js-toggle-password-target-1 input-group-append">
                                                <a class="input-group-text" href="javascript:;">
                                                    <i class="js-toggle-passowrd-show-icon-1 tio-visible-outlined"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <div class="js-form-message form-group mb-0">
                                        <label class="input-label" for="signupSrConfirmPassword">{{ translate('messages.Confirm Password') }}</label>

                                        <div class="input-group input-group-merge">
                                        <input type="password" class="js-toggle-password form-control" name="confirmPassword" id="signupSrConfirmPassword" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="{{ translate('messages.Must_contain_at_least_one_number_and_one_uppercase_and_lowercase_letter_and_symbol,_and_at_least_8_or_more_characters') }}"
                                        placeholder="{{ translate('messages.password_length_placeholder', ['length' => '8+']) }}"
                                        aria-label="8+ characters required"                                      data-msg="Password does not match the confirm password."
                                                data-hs-toggle-password-options='{
                                                "target": [".js-toggle-password-target-1", ".js-toggle-password-target-2"],
                                                "defaultClass": "tio-hidden-outlined",
                                                "showClass": "tio-visible-outlined",
                                                "classChangeTarget": ".js-toggle-passowrd-show-icon-2"
                                                }'>
                                        <div class="js-toggle-password-target-2 input-group-append">
                                            <a class="input-group-text" href="javascript:;">
                                            <i class="js-toggle-passowrd-show-icon-2 tio-visible-outlined"></i>
                                            </a>
                                        </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="btn--container justify-content-end">
                        <button type="reset" id="reset_btn" class="btn btn--reset">{{translate('messages.reset')}}</button>
                        <button type="submit" class="btn btn--primary">{{translate('messages.submit')}}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

@endsection

@push('script_2')
    <script src="{{asset('public/assets/admin/js/intlTelInputCdn.min.js')}}"></script>
    <script src="{{asset('public/assets/admin/js/intlTelInputCdn-jquery.min.js')}}"></script>
    <script src="{{asset('public/assets/admin/js/spartan-multi-image-picker.js')}}"></script>
    <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{\App\Models\BusinessSetting::where('key', 'map_api_key')->first()->value}}&libraries=places&callback=initMap&v=3.45.8"></script>

    <script>
        $("#tax_document").change(function() {
            var fallbackImageUrl = $("#tax_document_view").data("onerror-image");
            $("#tax_document_view").on("error", function() {
                $(this).attr("src", fallbackImageUrl);
            });
            var file = this.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $("#tax_document_view").attr("src", e.target.result);
                }
                reader.readAsDataURL(file);
            }
        });
        $("#registration_document").change(function() {
            var fallbackImageUrl = $("#registration_document_view").data("onerror-image");
            $("#registration_document_view").on("error", function() {
                $(this).attr("src", fallbackImageUrl);
            });
            var file = this.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $("#registration_document_view").attr("src", e.target.result);
                }
                reader.readAsDataURL(file);
            }
        });
        $("#agreement_document").change(function() {
            var fallbackImageUrl = $("#agreement_document_view").data("onerror-image");
            $("#agreement_document_view").on("error", function() {
                $(this).attr("src", fallbackImageUrl);
            });
            var file = this.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $("#agreement_document_view").attr("src", e.target.result);
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
    <script>
        "use strict";
      $(document).on('ready', function () {
            $('.offcanvas').on('click', function(){
                $('.offcanvas, .floating--date').removeClass('active')
            })
            $('.floating-date-toggler').on('click', function(){
                $('.offcanvas, .floating--date').toggleClass('active')
            })
        @if (isset(auth('admin')->user()->zone_id))
            $('#choice_zones').trigger('change');
        @endif
    });

        function readURL(input, viewer) {
            if (input.files && input.files[0]) {
                let reader = new FileReader();

                reader.onload = function (e) {
                    $('#'+viewer).attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        $("#customFileEg1").change(function () {
            readURL(this, 'viewer');
        });

        $("#coverImageUpload").change(function () {
            readURL(this, 'coverImageViewer');
        });
        @php($country=\App\Models\BusinessSetting::where('key','country')->first())
        let phone = $("#phone").intlTelInput({
            utilsScript: "{{asset('public/assets/admin/js/intlTelInputCdn-utils.min.js')}}",
            autoHideDialCode: true,
            autoPlaceholder: "ON",
            dropdownContainer: document.body,
            formatOnDisplay: true,
            hiddenInput: "phone",
            initialCountry: "{{$country?$country->value:auto}}",
            placeholderNumberType: "MOBILE",
            separateDialCode: true
        });

        $(function () {
            $("#coba").spartanMultiImagePicker({
                fieldName: 'identity_image[]',
                maxCount: 5,
                rowHeight: '120px',
                groupClassName: 'col-lg-2 col-md-4 col-sm-4 col-6',
                maxFileSize: '',
                placeholderImage: {
                    image: '{{asset('public/assets/admin/img/400x400/img2.jpg')}}',
                    width: '100%'
                },
                dropFileLabel: "Drop Here",
                onAddRow: function (index, file) {

                },
                onRenderedPreview: function (index) {

                },
                onRemoveRow: function (index) {

                },
                onExtensionErr: function (index, file) {
                    toastr.error('{{translate('messages.please_only_input_png_or_jpg_type_file')}}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                },
                onSizeErr: function (index, file) {
                    toastr.error('{{translate('messages.file_size_too_big')}}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        });

        let myLatlng = { lat: {{$store->latitude}}, lng: {{$store->longitude}} };
        const map = new google.maps.Map(document.getElementById("map"), {
            zoom: 13,
            center: myLatlng,
        });
        let zonePolygon = null;
        let infoWindow = new google.maps.InfoWindow({
                content: "Click the map to get Lat/Lng!",
                position: myLatlng,
            });
        let bounds = new google.maps.LatLngBounds();
        function initMap() {
            // Create the initial InfoWindow.
            new google.maps.Marker({
                position: { lat: {{$store->latitude}}, lng: {{$store->longitude}} },
                map,
                title: "{{$store->name}}",
            });
            infoWindow.open(map);
            const input = document.getElementById("pac-input");
            const searchBox = new google.maps.places.SearchBox(input);
            map.controls[google.maps.ControlPosition.TOP_CENTER].push(input);
            let markers = [];
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
                const bounds = new google.maps.LatLngBounds();
                places.forEach((place) => {
                    document.getElementById('latitude').value = place.geometry.location.lat();
                    document.getElementById('longitude').value = place.geometry.location.lng();
                    if (!place.geometry || !place.geometry.location) {
                        console.log("Returned place contains no geometry");
                        return;
                    }
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
        }
        initMap();
        $('.get_zone_data').on('change',function (){
            let id = $(this).val();
            console.log('get_zone_data');
            $.get({
                url: '{{url('/')}}/admin/zone/get-coordinates/'+id,
                dataType: 'json',
                success: function (data) {
                    if(zonePolygon)
                    {
                        zonePolygon.setMap(null);
                    }
                    zonePolygon = new google.maps.Polygon({
                        paths: data.coordinates,
                        strokeColor: "#FF0000",
                        strokeOpacity: 0.8,
                        strokeWeight: 2,
                        fillColor: 'white',
                        fillOpacity: 0,
                    });
                    zonePolygon.setMap(map);
                    map.setCenter(data.center);
                    google.maps.event.addListener(zonePolygon, 'click', function (mapsMouseEvent) {
                        infoWindow.close();
                        // Create a new InfoWindow.
                        infoWindow = new google.maps.InfoWindow({
                        position: mapsMouseEvent.latLng,
                        content: JSON.stringify(mapsMouseEvent.latLng.toJSON(), null, 2),
                        });
                        let coordinates = JSON.stringify(mapsMouseEvent.latLng.toJSON(), null, 2);
                        coordinates = JSON.parse(coordinates);

                        document.getElementById('latitude').value = coordinates['lat'];
                        document.getElementById('longitude').value = coordinates['lng'];
                        infoWindow.open(map);
                    });
                },
            });
        })
        $(document).on('ready', function (){
            let id = $('#choice_zones').val();
            $.get({
                url: '{{url('/')}}/admin/zone/get-coordinates/'+id,
                dataType: 'json',
                success: function (data) {
                    if(zonePolygon)
                    {
                        zonePolygon.setMap(null);
                    }
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
                    google.maps.event.addListener(zonePolygon, 'click', function (mapsMouseEvent) {
                        infoWindow.close();
                        // Create a new InfoWindow.
                        infoWindow = new google.maps.InfoWindow({
                        position: mapsMouseEvent.latLng,
                        content: JSON.stringify(mapsMouseEvent.latLng.toJSON(), null, 2),
                        });
                        let coordinates = JSON.stringify(mapsMouseEvent.latLng.toJSON(), null, 2);
                        coordinates = JSON.parse(coordinates);

                        document.getElementById('latitude').value = coordinates['lat'];
                        document.getElementById('longitude').value = coordinates['lng'];
                        infoWindow.open(map);
                    });
                },
            });
        });

    $('#reset_btn').click(function(){
        $('#viewer').attr('src', "{{ asset('public/assets/admin/img/upload.png') }}");
        $('#customFileEg1').val(null);
        $('#coverImageViewer').attr('src', "{{ asset('public/assets/admin/img/upload-img.png') }}");
        $('#coverImageUpload').val(null);
        $('#choice_zones').val(null).trigger('change');
        $('#module_id').val(null).trigger('change');
        zonePolygon.setMap(null);
        $('#coordinates').val(null);
        $('#latitude').val(null);
        $('#longitude').val(null);
    })

    let zone_id = 0;
   
    $('#choice_zones').on('change', function() {
        console.log('choice_zones');
        console.log($(this).val());
        if($(this).val())
    {
        zone_id = $(this).val();
        console.log(zone_id);
    }
    });



    $('#zone_id').select2({
            ajax: {
                 url: '{{url('/')}}/store/get-all-modules',
                data: function (params) {
                    return {
                        q: params.term, // search term
                        page: params.page,
                        zone_id: zone_id
                    };
                },
                processResults: function (data) {
                    return {
                    results: data
                    };
                },
                __port: function (params, success, failure) {
                    let $request = $.ajax(params);

                    $request.then(success);
                    $request.fail(failure);

                    return $request;
                }
            }
        });


    $('.delivery-time').on('click',function (){
        let min = $("#minimum_delivery_time").val();
        let max = $("#maximum_delivery_time").val();
        let type = $("#delivery_time_type").val();
        $("#floating--date").removeClass('active');
        $("#time_view").val(min+' to '+max+' '+type);

    })
</script>
@endpush
