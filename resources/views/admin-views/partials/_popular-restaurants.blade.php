<!-- Header -->
<div class="card-header border-0 order-header-shadow">
    <h5 class="card-title d-flex justify-content-between">
        {{translate('most popular')}} @if (Config::get('module.current_module_type')== 'food')
            {{ translate('messages.restaurants') }}
        @else
            {{ translate('messages.stores') }}
        @endif
    </h5>
    @php($params=session('dash_params'))
    @if($params['zone_id']!='all')
        @php($zone_name=\App\Models\Zone::where('id',$params['zone_id'])->first()->name)
    @else
        @php($zone_name = translate('messages.all'))
    @endif
</div>
<!-- End Header -->

<!-- Body -->
<div class="card-body">
    <ul class="most-popular">
    @foreach($popular as $key=>$item)
        <li class="cursor-pointer redirect-url" data-url="{{route('admin.store.view', $item->store_id)}}">
            <div class="img-container">
                <img class="onerror-image" data-onerror-image="{{asset('public/assets/admin/img/100x100/1.png')}}"

                src="{{ \App\CentralLogics\Helpers::get_image_helper(
                    $item->store,'logo',
                    asset('storage/app/public/store').'/'.$item->store['logo'] ?? '',
                    asset('public/assets/admin/img/100x100/1.png'),
                    'store/'
                ) }}"

                alt="{{translate('store')}}" title="{{ $item?->store?->name }}">
                <span class="ml-2" title="{{ $item?->store?->name }}"> {{Str::limit($item->store->name??translate('messages.store deleted!'), 20, '...')}} </span>
            </div>
            <div>
                <span class="text-FF6D6D">{{$item['count']}} <i class="tio-heart"></i></span>
            </div>
{{--            <span class="badge badge-soft text--primary px-2">--}}
{{--                <span>--}}
{{--                    {{$item['count']}}--}}
{{--                </span>--}}
{{--                <i class="tio-star"></i>--}}
{{--            </span>--}}
        </li>
    @endforeach
    </ul>
</div>
<script src="{{asset('public/assets/admin')}}/js/view-pages/common.js"></script>
