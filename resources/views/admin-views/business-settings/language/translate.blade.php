@extends('layouts.admin.app')

@section('title',translate('messages.language'))


@section('content')
    <div class="content container-fluid">
        <div class="row __mt-20">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="search--button-wrapper justify-content-between">
                            <h5 class="m-0">{{translate('language_content_table')}}</h5>
                            <form class="search-form min--260">
                                <!-- Search -->
                                <div class="input-group input--group">
                                    <input id="datatableSearch_" type="search" name="search" class="form-control h--40px"
                                            placeholder="{{ translate('messages.Ex : Search') }}" aria-label="{{translate('messages.search')}}" value="{{ request()?->search ?? null }}" required>
                                    <input type="hidden">
                                    <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>

                                </div>
                                <!-- End Search -->
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" >
                                <thead>
                                <tr>
                                    <th>{{translate('SL#')}}</th>
                                    <th class="__width-400">{{translate('Current_value')}}</th>
                                    <th class="__min-width">{{translate('translated_value')}}</th>
                                    <th>{{translate('auto_translate')}}</th>
                                    <th>{{translate('update')}}</th>
                                </tr>
                                </thead>

                                <tbody>
                                @php($count=0)
                                @foreach($full_data as $key=>$value)
                                    @php($count++)
                                    <tr id="lang-{{$count}}">
                                        <td>{{ $count+$full_data->firstItem() -1}}</td>
                                        <td>
                                            <input id="key" type="text" name="key[]"
                                            value="{{$key}}" hidden>
                                        <label for="key">{{translate($key) }}</label>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" name="value[]"
                                            id="value-{{$count}}"
                                            value="{{$full_data[$key]}}">
                                            <label for="value-{{$count}}"></label>
                                        </td>
                                        @php($key=\App\CentralLogics\Helpers::remove_invalid_charcaters($key))
                                        <td class="__w-100px">
                                            <button type="button"
                                                    data-key="{{$key}}" data-id="{{$count}}"
                                                    class="btn btn-ghost-success btn-block auto-translate-btn" ><i class="tio-globe"></i>
                                            </button>
                                        </td>
                                        <td class="__w-100px">
                                            <button type="button"
                                                    data-key="{{$key}}"
                                                    data-id="{{$count}}"
                                                    class="btn btn--primary btn-block update-language-btn"><i class="tio-save-outlined"></i>
                                            </button>
                                        </td>

                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                            @if(count($full_data) !== 0)
                            <hr>
                            @endif
                            <div class="page-area">
                                {!! $full_data->appends(request()->query())->links() !!}
                            </div>
                            @if(count($full_data) === 0)
                            <div class="empty--data">
                                <img src="{{asset('/public/assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
                                <h5>
                                    {{translate('no_data_found')}}
                                </h5>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <!-- Page level custom scripts -->
    <script>

        "use strict"

        $(document).on('click', '.auto-translate-btn', function () {

            let key = $(this).data('key');
            let id = $(this).data('id');


            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{ route('admin.business-settings.language.auto-translate', [$lang]) }}",
                method: 'POST',
                data: {
                    key: key
                },
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (response) {
                    toastr.success('{{ translate('Key translated successfully') }}');
                    $('#value-' + id).val(response.translated_data);
                },
                complete: function () {
                    $('#loading').hide();
                },
            });
        });

        $(document).on('click', '.update-language-btn', function () {
            let key = $(this).data('key');
            let id = $(this).data('id');
            let value = $('#value-'+id).val() ;
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{route('admin.business-settings.language.translate-submit',[$lang])}}",
                method: 'POST',
                data: {
                    key: key,
                    value: value
                },
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (response) {
                    toastr.success('{{translate('text_updated_successfully')}}');
                },
                complete: function () {
                    $('#loading').hide();
                },
            });

        });



    </script>

@endpush
