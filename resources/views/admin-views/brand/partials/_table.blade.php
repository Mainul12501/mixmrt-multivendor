@foreach($brands as $key=>$brand)
<tr>
    <td>{{$key+1}}</td>
    <td>
        <span class="d-block font-size-sm text-body">
            {{Str::limit($brand['name'], 20,'...')}}
        </span>
    </td>
    <td>
        <span class="d-block font-size-sm text-body text-center">
            {{ $brand->items->count()}}
        </span>
    </td>
    <td>
        <label class="toggle-switch toggle-switch-sm" for="stocksCheckbox{{$brand->id}}">
            <input type="checkbox" data-url="{{route('admin.brand.status',[$brand['id'],$brand->status?0:1])}}" class="toggle-switch-input redirect-url" id="stocksCheckbox{{$brand->id}}" {{$brand->status?'checked':''}}>
            <span class="toggle-switch-label mx-auto">
                <span class="toggle-switch-indicator"></span>
            </span>
        </label>
    </td>
    <td>
        <div class="btn--container justify-content-center">
            <a class="btn action-btn btn--primary btn-outline-primary"
                href="{{route('admin.brand.edit',[$brand['id']])}}" title="{{translate('messages.edit_brand')}}"><i class="tio-edit"></i>
            </a>
            <a class="btn action-btn btn--danger btn-outline-danger form-alert" href="javascript:" data-id="brand-{{$brand['id']}}" data-message="{{ translate('messages.Want to delete this brand') }}"  title="{{translate('messages.delete_brand')}}"><i class="tio-delete-outlined"></i>
            </a>
            <form action="{{route('admin.brand.delete',[$brand['id']])}}" method="post" id="brand-{{$brand['id']}}">
                @csrf @method('delete')
            </form>
        </div>
    </td>
</tr>
@endforeach
