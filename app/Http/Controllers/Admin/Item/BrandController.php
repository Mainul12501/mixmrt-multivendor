<?php

namespace App\Http\Controllers\Admin\Item;

use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Services\BrandService;
use Illuminate\Http\JsonResponse;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\BrandAddRequest;
use Illuminate\Database\Eloquent\Collection;
use App\Http\Requests\Admin\BrandUpdateRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Enums\ViewPaths\Admin\Brand as BrandViewPath;
use App\Contracts\Repositories\BrandRepositoryInterface;
use App\Contracts\Repositories\TranslationRepositoryInterface;

class BrandController extends BaseController
{
    public function __construct(
        protected BrandRepositoryInterface $brandRepo,
        protected BrandService $brandService,
        protected TranslationRepositoryInterface $translationRepo
    )
    {
    }

    public function index(?Request $request): View|Collection|LengthAwarePaginator|null
    {
        return $this->getListView($request);
    }

    private function getListView(Request $request): View
    {
        $brands = $this->brandRepo->getListWhere(
            searchValue: $request['search'],
            dataLimit: config('default_pagination')
        );
        $language = getWebConfig('language');
        $defaultLang = str_replace('_', '-', app()->getLocale());
        return view(BrandViewPath::INDEX[VIEW], compact('brands','language','defaultLang'));
    }

    public function add(BrandAddRequest $request): RedirectResponse
    {
        $brand = $this->brandRepo->add(data: $this->brandService->getAddData(request: $request));
        $this->translationRepo->addByModel(request: $request, model: $brand, modelPath: 'App\Models\Brand', attribute: 'name');
        Toastr::success(translate('messages.brand_added_successfully'));
        return back();
    }

    public function getUpdateView(string|int $id): View
    {
        $brand = $this->brandRepo->getFirstWithoutGlobalScopeWhere(params: ['id' => $id]);
        $language = getWebConfig('language');
        $defaultLang = str_replace('_', '-', app()->getLocale());
        return view(BrandViewPath::UPDATE[VIEW], compact('brand','language','defaultLang'));
    }

    public function update(BrandUpdateRequest $request, $id): RedirectResponse
    {
        $brand = $this->brandRepo->getFirstWithoutGlobalScopeWhere(params: ['id' => $id]);
        $brand = $this->brandRepo->update(id: $id ,data: $this->brandService->getUpdateData(request: $request,brand: $brand));
        $this->translationRepo->updateByModel(request: $request, model: $brand, modelPath: 'App\Models\Brand', attribute: 'name');
        Toastr::success(translate('messages.brand_updated_successfully'));
        return back();
    }

    public function updateStatus(Request $request): RedirectResponse
    {
        $this->brandRepo->update(id: $request['id'] ,data: ['status'=>$request['status']]);
        Toastr::success(translate('messages.brand_status_updated'));
        return back();
    }

    public function delete(Request $request): RedirectResponse
    {
        $this->brandRepo->delete(id: $request['id']);
        Toastr::success(translate('messages.brand_deleted_successfully'));
        return back();
    }

    public function getDropdownList(Request $request): JsonResponse
    {
        $data = $this->brandRepo->getDropdownList(request: $request, dataLimit: 8);
        $data = $this->brandService->getDropdownData(data: $data, request: $request);

        return response()->json($data);
    }
}
