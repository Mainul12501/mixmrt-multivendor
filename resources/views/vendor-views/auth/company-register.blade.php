@extends('layouts.landing.app')
@section('title', translate('messages.Courier_Company_registration'))
@push('css_or_js')
    <link rel="stylesheet" href="{{ asset('public/assets/admin/css/toastr.css') }}">
    <link rel="stylesheet" href="{{ asset('public/assets/admin/css/view-pages/vendor-registration.css') }}">
    <link rel="stylesheet" href="{{ asset('public/assets/landing/css/select2.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('public/assets/admin/css/style.css') }}">

    <link rel="stylesheet" href="{{asset('/public/assets/admin/css/intlTelInput.css')}}"/>

@endpush
@section('content')
    <section class="m-0 py-5">
        <div class="container">
            <!-- Page Header -->
            <div class="section-header">
                <h2 class="title mb-2">{{ translate('messages.Courier_Company') }} <span class="text--base">{{translate('application')}}</span></h2>
            </div>
            @php($language=\App\Models\BusinessSetting::where('key','language')->first())
            @php($language = $language->value ?? null)
            @php($defaultLang = 'en')
            <!-- End Page Header -->
            <form class="js-validate" action="{{ route('company.store') }}" method="post" enctype="multipart/form-data" id="form-id">
                @csrf
                <div class="card __card mb-3">
                    <div class="card-header d-flex justify-content-between flex align-items-center ">
                        <h5 class="card-title">
                            <svg width="20" x="0" y="0" viewBox="0 0 68 68" class="store-svg-logo" xml:space="preserve" ><g><g><path d="m62.99 57.53h-1.17v-29.22c-1.09-.47-2.02-1.25-2.67-2.23-1.08 1.63-2.93 2.71-5.03 2.71s-3.95-1.08-5.03-2.71c-1.08 1.63-2.93 2.71-5.03 2.71s-3.95-1.08-5.03-2.71c-1.08 1.63-2.93 2.71-5.03 2.71s-3.95-1.08-5.03-2.71c-1.08 1.63-2.92 2.71-5.02 2.71-2.11 0-3.97-1.09-5.05-2.74-1.09 1.61-2.92 2.67-5.01 2.67-2.1 0-3.95-1.08-5.03-2.71-.65.98-1.58 1.77-2.68 2.23v29.29h-1.17c-1.21 0-2.19.98-2.19 2.19v4.16h62.36v-4.16c0-1.21-.98-2.19-2.19-2.19zm-33.55 0h-16.45v-20.29c0-1.36 1.1-2.47 2.47-2.47h11.51c1.36 0 2.47 1.11 2.47 2.47zm24.43-9.54c0 .88-.71 1.59-1.59 1.59h-13.41c-.88 0-1.6-.71-1.6-1.59v-12.13c0-.88.72-1.59 1.6-1.59h13.41c.88 0 1.59.71 1.59 1.59z" fill="#000000" data-original="#000000"></path><path d="m59.86 19.99h7.77l-3.07-6.5c-.33-.7-1.03-1.15-1.81-1.15h-5.46z" fill="#000000" data-original="#000000"></path><path d="m10.72 12.27h-5.46c-.77 0-1.48.45-1.81 1.15l-3.07 6.5h7.76z" fill="#000000" data-original="#000000"></path><path d="m60.15 21.99v.77c0 2.22 1.8 4.03 4.03 4.03 2.22 0 4.02-1.81 4.02-4.03v-.77z" fill="#000000" data-original="#000000"></path><path d="m54.12 26.79c2.22 0 4.03-1.81 4.03-4.03v-.77h-8.06v.77c0 2.22 1.81 4.03 4.03 4.03z" fill="#000000" data-original="#000000"></path><path d="m46.71 14.26-.39-1.92h-6.87l.52 7.65h7.9z" fill="#000000" data-original="#000000"></path><path d="m9.86 22.69c0 2.22 1.81 4.03 4.03 4.03s4.03-1.81 4.03-4.03v-.7h-8.06z" fill="#000000" data-original="#000000"></path><path d="m55.18 12.34h-6.82l1.16 5.73.39 1.92h7.84z" fill="#000000" data-original="#000000"></path><path d="m19.92 22.76c0 2.22 1.8 4.03 4.03 4.03 2.22 0 4.02-1.81 4.02-4.03v-.77h-8.05z" fill="#000000" data-original="#000000"></path><path d="m7.86 22.69v-.77h-8.06v.77c0 2.22 1.81 4.03 4.03 4.03s4.03-1.81 4.03-4.03z" fill="#000000" data-original="#000000"></path><path d="m19.64 12.34h-6.81l-2.55 7.58h7.83z" fill="#000000" data-original="#000000"></path><path d="m30.56 12.34-.52 7.65h7.92l-.51-7.65z" fill="#000000" data-original="#000000"></path><path d="m44.06 26.79c2.22 0 4.03-1.81 4.03-4.03v-.77h-8.06v.77c0 2.22 1.81 4.03 4.03 4.03z" fill="#000000" data-original="#000000"></path><path d="m28.55 12.34h-6.86l-1.55 7.65h7.9z" fill="#000000" data-original="#000000"></path><path d="m29.97 22.76c0 2.22 1.81 4.03 4.03 4.03s4.03-1.81 4.03-4.03v-.77h-8.06z" fill="#000000" data-original="#000000"></path><path d="m13.49 10.34h48.33v-2.03c0-2.31-1.87-4.19-4.18-4.19h-47.27c-2.31 0-4.19 1.88-4.19 4.19v1.96h7.33z" fill="#000000" data-original="#000000"></path></g></g></svg>  {{ translate('messages.Courier_Company_info') }}</h5>

                            @php($courier_company_agereement = \App\Models\BusinessSetting::where('key', 'courier_company_agereement')->first())
{{--                            @if ($courier_company_agereement)--}}
{{--                            <a href="{{route('restaurant.download-courier-company-agreement')}}" class="text-center mr-3 mb-4 cmn--btn border-0 outline-0"> <strong>{{ translate('messages.download_Agreement') }} </strong> </a>--}}
{{--                            @endif--}}

                    </div>
                    <div class="card-body p-4">
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
                        <div class="row row-margin-top">
                            @if ($language)
                            <div class="lang_form row"
                            id="default-form">
                            <div class="col-md-6 col-lg-6 col-sm-12">
                                <div class="form-group">
                                    <label class="input-label"
                                        for="default_name">{{ translate('messages.name') }}
                                        ({{ translate('messages.Default') }})
                                    </label>
                                    <input type="text" name="name[]" id="default_name"
                                        class="form-control __form-control" placeholder="{{ translate('messages.Courier_Company_name') }}"
                                        required
                                         >
                                </div>
                                </div>
                                <input type="hidden" name="lang[]" value="default">
                                <div class="col-md-6 col-lg-6 col-sm-12">
                                <div class="form-group mb-0">
                                    <label class="input-label"
                                        for="address">{{ translate('messages.address') }} ({{ translate('messages.default') }})</label>
                                    <textarea type="text" id="address" name="address[]" placeholder="{{translate('messages.company')}}" class="form-control __form-control"></textarea>
                                </div>
                                </div>
                            </div>
                                @foreach (json_decode($language) as $lang)
                                    <div class="d-none lang_form row"
                                        id="{{ $lang }}-form">
                                        <div class="col-md-6 col-lg-6 col-sm-12">
                                        <div class="form-group">
                                            <label class="input-label"
                                                for="{{ $lang }}_name">{{ translate('messages.name') }}
                                                ({{ strtoupper($lang) }})
                                            </label>
                                            <input type="text" name="name[]" id="{{ $lang }}_name"
                                                class="form-control __form-control" placeholder="{{ translate('messages.store_name') }}"
                                                 >
                                        </div>
                                        </div>
                                        <input type="hidden" name="lang[]" value="{{ $lang }}">
                                        <div class="col-md-6 col-lg-6 col-sm-12">
                                        <div class="form-group mb-0">
                                            <label class="input-label"
                                                for="address{{$lang}}">{{ translate('messages.address') }} ({{ strtoupper($lang) }})</label>
                                            <textarea type="text" id="address{{$lang}}" name="address[]" placeholder="{{translate('messages.store')}}" class="form-control __form-control"></textarea>
                                        </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif

                            {{-- <div class="row row-margin-top">
                                <div class="col-md-6 col-lg-6 col-sm-12">
                                    <div class="form-group">
                                        <label class="input-label"  for="tax">{{ translate('messages.vat/tax') }} (%)</label>
                                        <input type="number" id="tax" name="tax" class="form-control __form-control"
                                            placeholder="{{ translate('messages.vat/tax') }}" min="0" step=".01" required
                                            value="{{ old('tax') }}">
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-6 col-sm-12">
                                <div class="form-group">
                                    <label class="input-label" for="minimum_delivery_time">{{translate('messages.approx_delivery_time')}}</label>
                                    <div class="input-group">
                                        <input type="number"  id="minimum_delivery_time" name="minimum_delivery_time" class="form-control __form-control" placeholder="Min: 10" value="{{old('minimum_delivery_time')}}">
                                        <input type="number" name="maximum_delivery_time" class="form-control __form-control" placeholder="Max: 20" value="{{old('maximum_delivery_time')}}">
                                        <select name="delivery_time_type"  class="form-control __form-control text-capitalize"  required>
                                            <option value="min">{{translate('messages.minutes')}}</option>
                                            <option value="hours">{{translate('messages.hours')}}</option>
                                            <option value="days">{{translate('messages.days')}}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div> --}}
                        <div class="row row-margin-top">
                            <div class="col-md-6 col-lg-6 col-sm-12 float-end">
                                <div class="form-group">

                                    <img class="__register-img" id="coverImageViewer"
                                        src="{{ asset('public/assets/admin/img/900x400/img1.jpg') }}"
                                        alt="Product thumbnail" />

                                    <label for="name" class="input-label pt-2">{{ translate('messages.upload_cover_photo') }} <span
                                            class="text-danger">({{ translate('messages.ratio') }}
                                            2:1)</span></label>
                                    <div class="custom-file">
                                        <input type="file" name="cover_photo" id="coverImageUpload" class="form-control __form-control"
                                            accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-6 col-sm-12">
                                <div class="form-group">

                                    <img class="__register-img" id="logoImageViewer"
                                        src="{{ asset('public/assets/admin/img/160x160/img1.jpg') }}"
                                        alt="Product thumbnail" />


                                    <label class="input-label pt-2">{{ translate('messages.store_logo') }}<small style="color: red"> (
                                            {{ translate('messages.ratio') }}
                                            1:1
                                            )</small></label>
                                    <div class="custom-file">
                                        <input type="file" name="logo" id="customFileEg1" class="form-control __form-control"
                                            accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                </div>
                <div class="card __card mb-3 mt-5">
                    <div class="card-header">
                        <h5 class="card-title">
                            <svg width="20" x="0" y="0" viewBox="0 0 460.8 460.8"  xml:space="preserve" class="store-svg-logo"><g><g><g><g>
                                    <path d="M230.432,239.282c65.829,0,119.641-53.812,119.641-119.641C350.073,53.812,296.261,0,230.432,0
                                        S110.792,53.812,110.792,119.641S164.604,239.282,230.432,239.282z" fill="#020202" data-original="#000000" class=""></path>
                                    <path d="M435.755,334.89c-3.135-7.837-7.314-15.151-12.016-21.943c-24.033-35.527-61.126-59.037-102.922-64.784
                                        c-5.224-0.522-10.971,0.522-15.151,3.657c-21.943,16.196-48.065,24.555-75.233,24.555s-53.29-8.359-75.233-24.555
                                        c-4.18-3.135-9.927-4.702-15.151-3.657c-41.796,5.747-79.412,29.257-102.922,64.784c-4.702,6.792-8.882,14.629-12.016,21.943
                                        c-1.567,3.135-1.045,6.792,0.522,9.927c4.18,7.314,9.404,14.629,14.106,20.898c7.314,9.927,15.151,18.808,24.033,27.167
                                        c7.314,7.314,15.673,14.106,24.033,20.898c41.273,30.825,90.906,47.02,142.106,47.02s100.833-16.196,142.106-47.02
                                        c8.359-6.269,16.718-13.584,24.033-20.898c8.359-8.359,16.718-17.241,24.033-27.167c5.224-6.792,9.927-13.584,14.106-20.898
                                        C436.8,341.682,437.322,338.024,435.755,334.89z" fill="#020202" data-original="#000000" class=""></path>
                                </g>
                            </g>
                        </g>
                        </g>
                        </svg>
                        {{ translate('messages.owner_info') }}</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row row-margin-top">
                            <div class="col-md-4 col-lg-4 col-sm-12">
                                <div class="form-group">
                                    <label class="input-label" for="f_name">{{ translate('messages.first_name') }}</label>
                                    <input type="text" id="f_name" name="f_name" class="form-control __form-control"
                                        placeholder="{{ translate('messages.first_name') }}"
                                        value="{{ old('f_name') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4 col-lg-4 col-sm-12">
                                <div class="form-group">
                                    <label class="input-label" for="l_name">{{ translate('messages.last_name') }}</label>
                                    <input type="text" id="l_name" name="l_name" class="form-control __form-control"
                                        placeholder="{{ translate('messages.last_name') }}"
                                        value="{{ old('l_name') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4 col-lg-4 col-sm-12">
                                <div class="form-group mb-0">
                                    <label class="input-label" for="phone">{{translate('messages.phone')}}</label>
                                    <input type="text" id="phone" name="phone" class="form-control"
                                    placeholder="{{ translate('messages.Ex:') }} 017********"
                                    required>
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
                                <span>{{ translate('messages.additional_information') }}</span>
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
                                            value="{{ old('tax_id') }}" required>
                                    </div>
                                </div>

                                <div class="col-md-4 col-lg-4 col-sm-12">
                                    <div class="form-group">
                                        <label class="input-label"
                                            for="reg_no">{{ translate('messages.registration_number') }}</label>
                                        <input type="text" id="reg_no"
                                            name="register_no" class="form-control __form-control"
                                            placeholder="{{ translate('messages.registration_number') }}"
                                            value="{{ old('registration_number') }}" required>
                                    </div>
                                </div>



                                <div class="col-3 card p-5 mx-5">
                                    <label class="__custom-upload-img">
                                        <label class="form-label">
                                            {{ translate('tax_document') }}
                                        </label>

                                        <div class="text-center">
                                            <img class="img--110 onerror-image" id="tax_document_view"
                                                data-onerror-image="{{ asset('public/assets/admin/img/important-file.png') }}"
                                                src="{{ asset('public/assets/admin/img/important-file-upload.png') }}"
                                                alt="tax_document" />
                                        </div>

                                        <input type="file" name="tax_document" id="tax_document" required
                                            class="custom-file-input"
                                            accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                    </label>
                                </div>
                                <div class="col-3 card p-5 mx-5">
                                    <label class="__custom-upload-img">
                                        <label class="form-label">
                                            {{ translate('registration_document') }}
                                        </label>

                                        <div class="text-center">
                                            <img class="img--110 onerror-image" id="registration_document_view"
                                                data-onerror-image="{{ asset('public/assets/admin/img/important-file.png') }}"
                                                src="{{ asset('public/assets/admin/img/important-file-upload.png') }}"
                                                alt="registration_document" />
                                        </div>

                                        <input type="file" name="registration_document" id="registration_document" required
                                            class="custom-file-input"
                                            accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                    </label>
                                </div>
                                <div class="col-4 card p-5 mx-5">
                                    <label class="__custom-upload-img">
                                        <label class="form-label">
                                            {{ translate('agreement/contact document  ') }}
                                        </label>

                                        <div class="text-center">
                                            <a href="{{route('show-agreement', ['key' => 'courier'])}}" target="_blank">
                                                <img class="img--110 onerror-image" id="agreement_document_view"
                                                     data-onerror-image="{{ asset('public/assets/admin/img/important-file.png') }}"
                                                     src="{{ asset('public/assets/admin/img/important-file.png') }}"
                                                     alt="agreement_document" />
                                                <br>
{{--                                                <span class="text-center" style="text-decoration: none;">Download</span>--}}
                                            </a>
                                        </div>

{{--                                        <input type="file" name="agreement_document" id="agreement_document" required--}}
{{--                                            class="custom-file-input"--}}
{{--                                            accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff, .pdf, .doc, .docx|image/*, application/pdf, application/msword, application/vnd.openxmlformats-officedocument.wordprocessingml.document">--}}
                                    </label>
                                </div>


                            </div>

                        </div>
                    </div>
                </div>
                <div class="row row-margin-top">
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label class="input-label" for="choice_zones">{{ translate('messages.zone') }} <span
                                    class="form-label-secondary" data-toggle="tooltip" data-placement="right"
data-original-title="{{ translate('messages.select_zone_for_map') }}"><img
                                        src="{{ asset('/public/assets/admin/img/info-circle.svg') }}"
                                        alt="{{ translate('messages.select_zone_for_map') }}"></span></label>
                            <select name="zone_id" id="choice_zones" required class="form-control __form-control js-select2-custom js-example-basic-single"
                                data-placeholder="{{ translate('messages.select_zone') }}">
                                <option value="" selected disabled>{{ translate('messages.select_zone') }}</option>
                                @foreach (\App\Models\Zone::active()->get() as $zone)
                                    @if (isset(auth('admin')->user()->zone_id))
                                        @if (auth('admin')->user()->zone_id == $zone->id)
                                            <option value="{{ $zone->id }}" selected>{{ $zone->name }}</option>
                                        @endif
                                    @else
                                        <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                    </div>
                    {{-- <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label for="module_id" class="input-label">{{translate('messages.module')}}</label>
                            <select name="module_id" required id="module_id"
                                    class="js-data-example-ajax form-control __form-control"  data-placeholder="{{translate('messages.select_module')}}">
                            </select>
                            <small class="text-danger">{{translate('messages.Select_zone_first')}}</small>
                        </div>
                    </div> --}}
                    <div class="col-md-12 col-lg-12 col-sm-12 mb-3">
                        <input id="pac-input" class="controls rounded" style="height: 3em;width:fit-content;" title="{{translate('messages.search_your_location_here')}}" type="text" placeholder="{{translate('messages.search_here')}}"/>
                        <div id="map"></div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="input-label" for="latitude">{{ translate('messages.latitude') }} <span
                                    class="input-label-secondary"
                                    title="{{ translate('messages.store_lat_lng_warning') }}"><img
                                        src="{{ asset('/public/assets/admin/img/info-circle.svg') }}"
                                        alt="{{ translate('messages.store_lat_lng_warning') }}"></span></label>
                            <input type="text" id="latitude" name="latitude" class="form-control __form-control"
                                placeholder="{{ translate('messages.Ex:') }} -94.22213" value="{{ old('latitude') }}" required readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="input-label" for="longitude">{{ translate('messages.longitude') }} <span
                                    class="input-label-secondary"
                                    title="{{ translate('messages.store_lat_lng_warning') }}"><img
                                        src="{{ asset('/public/assets/admin/img/info-circle.svg') }}"
                                        alt="{{ translate('messages.store_lat_lng_warning') }}"></span></label>
                            <input type="text" name="longitude" class="form-control __form-control" placeholder="{{ translate('messages.Ex:') }} 103.344322"
                                id="longitude" value="{{ old('longitude') }}" required readonly>
                        </div>
                    </div>
                </div>
                </div>
                <div class="card __card mb-3">
                    <div class="card-header">
                        <h5 class="card-title">
                            <svg width="20" x="0" y="0" viewBox="0 0 460.8 460.8" class="store-svg-logo" xml:space="preserve" ><g><g><g><g>
                                    <path d="M230.432,239.282c65.829,0,119.641-53.812,119.641-119.641C350.073,53.812,296.261,0,230.432,0
                                        S110.792,53.812,110.792,119.641S164.604,239.282,230.432,239.282z" fill="#020202" data-original="#000000" class=""></path>
                                    <path d="M435.755,334.89c-3.135-7.837-7.314-15.151-12.016-21.943c-24.033-35.527-61.126-59.037-102.922-64.784
                                        c-5.224-0.522-10.971,0.522-15.151,3.657c-21.943,16.196-48.065,24.555-75.233,24.555s-53.29-8.359-75.233-24.555
                                        c-4.18-3.135-9.927-4.702-15.151-3.657c-41.796,5.747-79.412,29.257-102.922,64.784c-4.702,6.792-8.882,14.629-12.016,21.943
                                        c-1.567,3.135-1.045,6.792,0.522,9.927c4.18,7.314,9.404,14.629,14.106,20.898c7.314,9.927,15.151,18.808,24.033,27.167
                                        c7.314,7.314,15.673,14.106,24.033,20.898c41.273,30.825,90.906,47.02,142.106,47.02s100.833-16.196,142.106-47.02
                                        c8.359-6.269,16.718-13.584,24.033-20.898c8.359-8.359,16.718-17.241,24.033-27.167c5.224-6.792,9.927-13.584,14.106-20.898
                                        C436.8,341.682,437.322,338.024,435.755,334.89z" fill="#020202" data-original="#000000" class=""></path>
                                </g>
                            </g>
                        </g>
                        </g>
                        </svg>
                        {{ translate('messages.login_info') }}
                    </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row mt-3">
                            <div class="col-md-4 col-sm-12 col-lg-4">
                                <div class="form-group">
                                    <label class="input-label" for="email">{{ translate('messages.email') }}</label>
                                    <input type="email"  id="email" name="email" class="form-control __form-control" placeholder="{{ translate('messages.Ex:') }} ex@example.com"
                                        value="{{ old('email') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-12 col-lg-4">
                                <div class="form-group">
                                    <label class="input-label"
                                        for="exampleInputPassword">{{ translate('messages.password') }}

                                        </label>
                                    <input type="password" name="password"
                                        placeholder="{{ translate('messages.password_length_placeholder', ['length' => '6+']) }}"
                                        class="form-control __form-control form-control __form-control-user" minlength="6" id="exampleInputPassword" required
                                        value="{{ old('password') }}">
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-12 col-lg-4">
                                <div class="form-group">
                                    <label class="input-label"
                                        for="exampleRepeatPassword">{{ translate('messages.confirm_password') }}</label>
                                    <input type="password"  name="confirm-password" class="form-control __form-control form-control __form-control-user"
                                        minlength="6" id="exampleRepeatPassword"
                                        placeholder="{{ translate('messages.password_length_placeholder', ['length' => '6+']) }}"
                                        required value="{{ old('confirm-password') }}">
                                    <div class="pass invalid-feedback">{{ translate('messages.password_not_matched') }}</div>
                                </div>

                            </div>
                            <div class="row mt-5">
                                <div class="col-sm-4 col-12">
                                    @php($recaptcha = \App\CentralLogics\Helpers::get_business_settings('recaptcha'))
                                    @if(isset($recaptcha) && $recaptcha['status'] == 1)
                                        <div id="recaptcha_element"  class="w-100" data-type="image"></div>
                                        <br/>
                                    @else
                                        <div class="row p-2">
                                            <div class="col-6 pr-0">
                                                <input type="text" class="form-control" name="custome_recaptcha"
                                                        id="custome_recaptcha" required placeholder="{{translate('Enter recaptcha value')}}" autocomplete="off" value="{{env('APP_DEBUG')?session('six_captcha'):''}}">
                                            </div>
                                            <div class="col-6 recap-img-div" >
                                                <img src="{!!  $custome_recaptcha->inline() ?? '' !!}"  alt="image" class="recap-img"/>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                    {{--                            mainul start--}}
                    <div class=" pt-4 ps-4 d-flex flex-wrap ">
                        <label for="termsConditions" class="me-3">
                            <input type="checkbox" style="width: auto" name="agree_terms_conditions" id="termsConditions"> I agree to the <a href="{{ route('terms-and-conditions') }}" style="text-decoration: none; color: blue" target="_blank">Terms and Conditions</a>.
                        </label>
                        <label for="privacyPolicy" class="me-3">
                            <input type="checkbox" style="width: auto" name="agree_privacy_policy" id="privacyPolicy"> I agree to the <a href="{{ route('privacy-policy') }}" style="text-decoration: none; color: blue" target="_blank">Privacy Policy</a>.
                        </label>
                        <label for="agreementDocs" class="me-3">
                            <input type="checkbox" style="width: auto" name="agree_agreement_docs" id="agreementDocs"> I agree to the <a href="{{ route('show-agreement', ['key' => 'courier']) }}" target="_blank" style="text-decoration: none; color: blue">Agreement Documents</a>.
                        </label>
                    </div>
                    {{--                            mainul ends--}}
                <div class="text-end">
                    <button type="submit" class="cmn--btn rounded-md border-0 outline-0 mt-0 m-3" id="nextBtn">{{ translate('messages.submit') }}</button>
                </div>
        </div>
            </form>
        </div>
    </section>

    @endsection
    @push('script_2')

        <script src="{{asset('public/assets/admin/js/intlTelInputCdn.min.js')}}"></script>
        <script src="{{asset('public/assets/admin/js/intlTelInputCdn-jquery.min.js')}}"></script>
        <script src="{{ asset('public/assets/admin/js/spartan-multi-image-picker.js') }}"></script>
        <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
        <script
            src="https://maps.googleapis.com/maps/api/js?key={{ \App\Models\BusinessSetting::where('key', 'map_api_key')->first()->value }}&libraries=drawing,places&v=3.45.8">
        </script>

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
</script>
        <script type="text/javascript">
            "use strict";
            @php($default_location = \App\Models\BusinessSetting::where('key', 'default_location')->first())
            @php($default_location = $default_location->value ? json_decode($default_location->value, true) : 0)
            let myLatlng = {
                lat: {{ $default_location ? $default_location['lat'] : '23.757989' }},
                lng: {{ $default_location ? $default_location['lng'] : '90.360587' }}
            };
            let map = new google.maps.Map(document.getElementById("map"), {
                zoom: 13,
                center: myLatlng,
            });
           let zonePolygon = null;
            let infoWindow = new google.maps.InfoWindow({
                content: "Click the map to get Lat/Lng!",
                position: myLatlng,
            });
           let bounds = new google.maps.LatLngBounds();

            $('#choice_zones').on('change', function() {
               let id = $(this).val();
                $.get({
                    url: '{{ url('/') }}/admin/zone/get-coordinates/' + id,
                    dataType: 'json',
                    success: function(data) {
                        if (zonePolygon) {
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
                        google.maps.event.addListener(zonePolygon, 'click', function(mapsMouseEvent) {
                            infoWindow.close();
                            // Create a new InfoWindow.
                            infoWindow = new google.maps.InfoWindow({
                                position: mapsMouseEvent.latLng,
                                content: JSON.stringify(mapsMouseEvent.latLng.toJSON(),
                                    null, 2),
                            });
                            let coordinates;
                            coordinates = JSON.stringify(mapsMouseEvent.latLng.toJSON(), null,
                                2);
                            coordinates = JSON.parse(coordinates);

                            document.getElementById('latitude').value = coordinates['lat'];
                            document.getElementById('longitude').value = coordinates['lng'];
                            infoWindow.open(map);
                        });
                    },
                });
            });

            $(document).ready(function() {
                $('#module_id').select2({
                    ajax: {
                        url: '{{url('/')}}/store/get-all-modules/',
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
            });

        </script>
        <script src="{{ asset('public/assets/admin/js/view-pages/vendor-registration.js') }}"></script>
            @if(isset($recaptcha) && $recaptcha['status'] == 1)

                <script type="text/javascript">
                "use strict";
                    let onloadCallback = function () {
                        grecaptcha.render('recaptcha_element', {
                            'sitekey': '{{ \App\CentralLogics\Helpers::get_business_settings('recaptcha')['site_key'] }}'
                        });
                    };
                </script>
                <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>
                <script>
                    "use strict";
                    $("#form-id").on('submit',function(e) {
                        let response = grecaptcha.getResponse();

                        if (response.length === 0) {
                            e.preventDefault();
                            toastr.error("{{translate('messages.Please check the recaptcha')}}");
                        }
                    });
                </script>
            @endif

        {{--        mainul starts--}}
        <script>
            $(document).on('click', '#nextBtn', function (e) {
                if ($('#termsConditions').length)
                {

                    if (!$('#termsConditions').is(':checked'))
                    {
                        e.preventDefault();
                        toastr.error('You must agree with our terms and conditions.');
                    }
                }
                if ($('#privacyPolicy').length)
                {

                    if (!$('#privacyPolicy').is(':checked'))
                    {
                        e.preventDefault();
                        toastr.error('You must agree with our Privacy Policy.');
                    }
                }
                if ($('#agreementDocs').length)
                {

                    if (!$('#agreementDocs').is(':checked'))
                    {
                        e.preventDefault();
                        toastr.error('You must agree with our Agreement Documents.');
                    }
                }
            })
        </script>
        {{--        mainul ends--}}

    <script src="{{ asset('public/assets/landing/js/select2.min.js') }}"></script>
    @endpush
