{{-- <div class="card-header border-0">
    <h4 class="text-center">{{ translate('messages.stock_Update') }}</h4>
</div> --}}
<div class="card-body">
    <h3 class="text-center mb-4">{{ translate('messages.stock_Update') }}</h3>
    <input name="product_id" value="{{$product['id']}}" type="hidden" class="initial-hidden">

    <div class="d-flex align-items-center gap-2 flex-column mb-3">
        <img width="50" height="50" class="rounded"  src="{{ \App\CentralLogics\Helpers::get_image_helper(
            $product,'image',
            asset('storage/app/public/product').'/'.$product['image'] ?? '',
            asset('public/assets/admin/img/160x160/img2.jpg'),
            'product/'
        ) }}"alt="product">
        <p class="mb-0">{{ $product->name }}</p>
        <div class="d-flex gap-2 align-items-center">
            <span>{{ translate('Current_Stock') }} </span>:
            <span class="font-semibold text-dark">{{ $product->stock }}</span>
        </div>
    </div>
    <div class="form-group">
        <div class="mb-4">
            <div class="variant_combination" id="variant_combination">
                @include('admin-views.product.partials._edit-combinations',['combinations'=>json_decode($product['variations'],true),'stock'=>config('module.'.$product->module->module_type)['stock']])
            </div>
            <div id="quantity">
                <label class="control-label"></label>
                <label class="input-label" for="total_stock">{{translate('messages.total_stock')}}</label>
                <input type="number" min="0" class="form-control" name="current_stock" value="{{$product->stock}}" id="quantity" {{count(json_decode($product['variations'],true)) > 0 ? 'readonly' : ""}}>
            </div>
        </div>
    </div>
</div>
