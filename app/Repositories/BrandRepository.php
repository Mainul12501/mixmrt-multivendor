<?php

namespace App\Repositories;

use App\Contracts\Repositories\BrandRepositoryInterface;
use App\Models\Brand;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class BrandRepository implements BrandRepositoryInterface
{
    public function __construct(protected Brand $brand)
    {
    }

    public function add(array $data): string|object
    {
        $brand = $this->brand->newInstance();
        foreach ($data as $key => $column) {
            $brand[$key] = $column;
        }
        $brand->save();
        return $brand;
    }

    public function getFirstWhere(array $params, array $relations = []): ?Model
    {
        return $this->brand->where($params)->first();
    }

    public function getList(array $orderBy = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        return $this->brand->get();
    }

    public function getListWhere(string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $key = explode(' ', $searchValue);

        return $this->brand->orderBy('name')
            ->when(isset($key) , function($q) use($key){
                $q->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('name', 'like', "%{$value}%");
                    }
                });
            })->paginate($dataLimit);
    }

    public function update(string $id, array $data): bool|string|object
    {
        $brand = $this->brand->find($id);
        foreach ($data as $key => $column) {
            $brand[$key] = $column;
        }
        $brand->save();
        return $brand;
    }

    public function delete(string $id): bool
    {
        $brand = $this->brand->find($id);
        $brand->translations()->delete();
        $brand->delete();

        return true;
    }

    public function getExportList(Request $request): Collection
    {
        $key = explode(' ', $request['search']);
        return $this->brand->orderBy('name')
            ->when(isset($key) , function($q) use($key){
                $q->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('name', 'like', "%{$value}%");
                    }
                });
            })
            ->get();
    }

    public function getFirstWithoutGlobalScopeWhere(array $params, array $relations = []): ?Model
    {
        return $this->brand->withoutGlobalScope('translate')->where($params)->first();
    }

    public function getDropdownList(Request $request, int|string $dataLimit = DEFAULT_DATA_LIMIT): Collection
    {
        return $this->brand->where('name', 'like', '%'.$request->q.'%')->limit($dataLimit)->get();
    }
}
