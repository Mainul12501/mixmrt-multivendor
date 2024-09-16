<!-- Header -->
<div class="card-header">
    <h5 class="card-header-title text-capitalize">
        <i class="tio-align-to-top"></i> {{translate('messages.top_selling_items')}}
    </h5>
</div>
<!-- End Header -->

<!-- Body -->
<div class="card-body">
    <div class="row g-2">
        @foreach($top_sell as $key=>$item)
            <div class="col-md-4 col-sm-6 initial--27 redirect-url"
                 data-url="{{route('vendor.item.view',[$item['id']])}}">
                <div class="grid-card">
                    <label class="label_1 text-center">{{translate('messages.sold')}} : {{$item['order_count']}}</label>
                    <img class="initial--28 onerror-image"
                    src="{{\App\CentralLogics\Helpers::get_image_helper($item,'image', asset('storage/app/public/product/').'/'.$item['image'], asset('public/assets/admin/img/placeholder-2.png'), 'product/') }}"
                         data-onerror-image="{{asset('public/assets/admin/img/placeholder-2.png')}}"
                         alt="{{$item->name}} image">
                    <div class="text-center mt-2">
                        <span class="fz--13">{{Str::limit($item['name'],20,'...')}}</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
<!-- End Body -->
<script src="{{asset('public/assets/admin')}}/js/view-pages/common.js"></script>