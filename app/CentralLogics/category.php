<?php

namespace App\CentralLogics;

use App\Models\Item;
use App\Models\Store;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class CategoryLogic
{
    public static function parents()
    {
        return Category::where('position', 0)->get();
    }

    public static function child($parent_id)
    {
        return Category::where(['parent_id' => $parent_id])->get();
    }

    public static function products($category_id, $zone_id, int $limit,int $offset, $type)
    {
        $paginator = Item::
        whereHas('module.zones', function($query)use($zone_id){
            $query->whereIn('zones.id', json_decode($zone_id, true));
        })
            ->whereHas('store', function($query)use($zone_id){
                $query->whereIn('zone_id', json_decode($zone_id, true))->whereHas('zone.modules',function($query){
                    $query->when(config('module.current_module_data'), function($query){
                        $query->where('modules.id', config('module.current_module_data')['id']);
                    });
                });
            })
            ->whereHas('category',function($q)use($category_id){
                return $q->when(is_numeric($category_id),function ($qurey) use($category_id){
                    return $qurey->whereId($category_id)->orWhere('parent_id', $category_id);
                })
                    ->when(!is_numeric($category_id),function ($qurey) use($category_id){
                        $qurey->where('slug', $category_id);
                    });
            })
            ->active()->type($type)->latest()->paginate($limit, ['*'], 'page', $offset);

        return [
            'total_size' => $paginator->total(),
            'limit' => $limit,
            'offset' => $offset,
            'products' => $paginator->items()
        ];
    }

    public static function category_products($category_ids, $zone_id, int $limit,int $offset, $type, $filter=null, $min=false, $max=false, $rating_count=null, $brand_ids = null)
    {
        $category_ids = isset($category_ids)?(is_array($category_ids)?$category_ids:json_decode($category_ids)):[];
        $brand_ids = isset($brand_ids)?(is_array($brand_ids)?$brand_ids:json_decode($brand_ids)):[];
        $filter = $filter?(is_array($filter)?$filter:str_getcsv(trim($filter, "[]"), ',')):'';
        $paginator = Item::
        whereHas('module.zones', function($query)use($zone_id){
            $query->whereIn('zones.id', json_decode($zone_id, true));
        })
            ->whereHas('store', function($query)use($zone_id){
                $query->whereIn('zone_id', json_decode($zone_id, true))->whereHas('zone.modules',function($query){
                    $query->when(config('module.current_module_data'), function($query){
                        $query->where('modules.id', config('module.current_module_data')['id']);
                    });
                });
            })
            ->when(isset($category_ids) && (count($category_ids)>0), function($query)use($category_ids){
                $query->whereHas('category',function($q)use($category_ids){
                    return $q->whereIn('id',$category_ids)->orWhereIn('parent_id', $category_ids);
                });
            })
            ->when(isset($brand_ids) && (count($brand_ids)>0), function($query)use($brand_ids){
                $query->whereHas('ecommerce_item_details',function($q)use($brand_ids){
                    return $q->whereHas('brand',function($q)use($brand_ids){
                        return $q->whereIn('id',$brand_ids);
                    });
                });
            })
            ->active()->type($type)
            ->when($rating_count, function($query) use ($rating_count){
                $query->where('avg_rating', '>=' , $rating_count);
            })
            ->when($min && $max, function($query)use($min,$max){
                $query->whereBetween('price',[$min,$max]);
            })
            ->when($filter&&in_array('top_rated',$filter),function ($qurey){
                $qurey->withCount('reviews')->orderBy('reviews_count','desc');
            })
            ->when($filter&&in_array('popular',$filter),function ($qurey){
                $qurey->popular();
            })
            ->when($filter&&in_array('high',$filter),function ($qurey){
                $qurey->orderBy('price', 'desc');
            })
            ->when($filter&&in_array('low',$filter),function ($qurey){
                $qurey->orderBy('price', 'asc');
            })
            ->when($filter&&in_array('discounted',$filter),function ($qurey){
                $qurey->Discounted()->orderBy('discount','desc');
            })
            ->latest()->paginate($limit, ['*'], 'page', $offset);


            $item_categories = Item::
            whereHas('module.zones', function($query)use($zone_id){
                $query->whereIn('zones.id', json_decode($zone_id, true));
            })
                ->whereHas('store', function($query)use($zone_id){
                    $query->whereIn('zone_id', json_decode($zone_id, true))->whereHas('zone.modules',function($query){
                        $query->when(config('module.current_module_data'), function($query){
                            $query->where('modules.id', config('module.current_module_data')['id']);
                        });
                    });
                })
                ->when(isset($category_ids) && (count($category_ids)>0), function($query)use($category_ids){
                    $query->whereHas('category',function($q)use($category_ids){
                        return $q->whereIn('id',$category_ids)->orWhereIn('parent_id', $category_ids);
                    });
                })
                ->when(isset($brand_ids) && (count($brand_ids)>0), function($query)use($brand_ids){
                    $query->whereHas('ecommerce_item_details',function($q)use($brand_ids){
                        return $q->whereHas('brand',function($q)use($brand_ids){
                            return $q->whereIn('id',$brand_ids);
                        });
                    });
                })
                ->active()->type($type)
                ->when($rating_count, function($query) use ($rating_count){
                    $query->where('avg_rating', '>=' , $rating_count);
                })
                ->when($min && $max, function($query)use($min,$max){
                    $query->whereBetween('price',[$min,$max]);
                })
                ->when($filter&&in_array('top_rated',$filter),function ($qurey){
                    $qurey->withCount('reviews')->orderBy('reviews_count','desc');
                })
                ->when($filter&&in_array('popular',$filter),function ($qurey){
                    $qurey->popular();
                })
                ->when($filter&&in_array('discounted',$filter),function ($qurey){
                    $qurey->Discounted()->orderBy('discount','desc');
                })
                ->when($filter&&in_array('high',$filter),function ($qurey){
                    $qurey->orderBy('price', 'desc');
                })
                ->when($filter&&in_array('low',$filter),function ($qurey){
                    $qurey->orderBy('price', 'asc');
                })
                ->latest()
            ->pluck('category_id')->toArray();

            $item_categories = array_unique($item_categories);

            $categories = Category::withCount(['products','childes'])->with(['childes' => function($query)  {
                $query->withCount(['products','childes']);
            }])
            ->where(['position'=>0,'status'=>1])
            ->when(config('module.current_module_data'), function($query){
                $query->module(config('module.current_module_data')['id']);
            })
            ->whereIn('id',$item_categories)
            ->orderBy('priority','desc')->get();

        return [
            'total_size' => $paginator->total(),
            'limit' => $limit,
            'offset' => $offset,
            'products' => $paginator->items(),
            'categories' => $categories,
        ];
    }


    public static function category_stores($category_ids, $zone_id, int $limit,int $offset, $type,$longitude=0,$latitude=0,$filter=null,$rating_count=null)
    {
        $category_ids = isset($category_ids)?(is_array($category_ids)?$category_ids:json_decode($category_ids)):[];
        $paginator = Store::
        withOpen($longitude??0,$latitude??0)
            ->withCount(['items','campaigns'])
            ->whereHas('items.category',function($q)use($category_ids){
                return $q->whereIn('id',$category_ids)->orWhereIn('parent_id', $category_ids);
            })
            ->when(config('module.current_module_data'), function($query)use($zone_id){
                $query->whereHas('zone.modules', function($query){
                    $query->where('modules.id', config('module.current_module_data')['id']);
                })->module(config('module.current_module_data')['id']);
                if(!config('module.current_module_data')['all_zone_service']) {
                    $query->whereIn('zone_id', json_decode($zone_id, true));
                }
            })
            ->active()->type($type)
            ->when($rating_count, function($query) use ($rating_count){
                $query->selectSub(function ($query) use ($rating_count){
                    $query->selectRaw('AVG(reviews.rating)')
                        ->from('reviews')
                        ->join('items', 'items.id', '=', 'reviews.item_id')
                        ->whereColumn('items.store_id', 'stores.id')
                        ->groupBy('items.store_id')
                        ->havingRaw('AVG(reviews.rating) >= ?', [$rating_count]);
                }, 'avg_r')->having('avg_r', '>=', $rating_count);
            })
            ->when($filter && in_array('top_rated',$filter),function ($qurey){
                $qurey->whereNotNull('rating')->whereRaw("LENGTH(rating) > 0");
            })
            ->when($filter && in_array('popular',$filter),function ($qurey){
                $qurey->withCount('orders')->orderBy('orders_count', 'desc');
            })
            ->when($filter && in_array('discounted',$filter),function ($qurey){
                $qurey->where(function ($query) {
                    $query->whereHas('items', function ($q) {
                        $q->Discounted();
                    });
                });
            })
            ->when($filter && in_array('open',$filter),function ($qurey){
                $qurey->orderBy('open', 'desc');
            })
            ->when($filter && in_array('nearby',$filter),function ($qurey){
                $qurey->orderBy('distance');
            })
            ->orderBy('open', 'desc')
            ->latest()
            ->paginate($limit, ['*'], 'page', $offset);


        $paginator->each(function ($store) {
            $category_ids = DB::table('items')
                ->join('categories', 'items.category_id', '=', 'categories.id')
                ->selectRaw('
                CAST(categories.id AS UNSIGNED) as id,
                categories.parent_id
            ')
                ->where('items.store_id', $store->id)
                ->where('categories.status', 1)
                ->groupBy('id', 'categories.parent_id')
                ->get();

            $data = json_decode($category_ids, true);

            $mergedIds = [];

            foreach ($data as $item) {
                if ($item['id'] != 0) {
                    $mergedIds[] = $item['id'];
                }
                if ($item['parent_id'] != 0) {
                    $mergedIds[] = $item['parent_id'];
                }
            }

            $category_ids = array_values(array_unique($mergedIds));

            $store->category_ids = $category_ids;
            $store->discount_status = !empty($store->items->where('discount', '>', 0));
            unset($store['items']);
        });

        return [
            'total_size' => $paginator->total(),
            'limit' => $limit,
            'offset' => $offset,
            'stores' => $paginator->items()
        ];
    }


    public static function stores($category_id, $zone_id, int $limit,int $offset, $type,$longitude=0,$latitude=0)
    {
        $paginator = Store::
        withOpen($longitude??0,$latitude??0)
            ->withCount(['items','campaigns'])
            ->whereHas('items.category',function($q)use($category_id){
                return $q->when(is_numeric($category_id),function ($qurey) use($category_id){
                    return $qurey->whereId($category_id)->orWhere('parent_id', $category_id);
                })
                    ->when(!is_numeric($category_id),function ($qurey) use($category_id){
                        $qurey->where('slug', $category_id);
                    });
            })
            ->when(config('module.current_module_data'), function($query)use($zone_id){
                $query->whereHas('zone.modules', function($query){
                    $query->where('modules.id', config('module.current_module_data')['id']);
                })->module(config('module.current_module_data')['id']);
                if(!config('module.current_module_data')['all_zone_service']) {
                    $query->whereIn('zone_id', json_decode($zone_id, true));
                }
            })
            ->active()->type($type)
            ->latest()->paginate($limit, ['*'], 'page', $offset);


        $paginator->each(function ($store) {
            $category_ids = DB::table('items')
                ->join('categories', 'items.category_id', '=', 'categories.id')
                ->selectRaw('
                CAST(categories.id AS UNSIGNED) as id,
                categories.parent_id
            ')
                ->where('items.store_id', $store->id)
                ->where('categories.status', 1)
                ->groupBy('id', 'categories.parent_id')
                ->get();

            $data = json_decode($category_ids, true);

            $mergedIds = [];

            foreach ($data as $item) {
                if ($item['id'] != 0) {
                    $mergedIds[] = $item['id'];
                }
                if ($item['parent_id'] != 0) {
                    $mergedIds[] = $item['parent_id'];
                }
            }

            $category_ids = array_values(array_unique($mergedIds));

            $store->category_ids = $category_ids;
            $store->discount_status = !empty($store->items->where('discount', '>', 0));
            unset($store['items']);
        });

        return [
            'total_size' => $paginator->total(),
            'limit' => $limit,
            'offset' => $offset,
            'stores' => $paginator->items()
        ];
    }


    public static function all_products($id, $zone_id)
    {
        $cate_ids=[];
        array_push($cate_ids,(int)$id);
        foreach (CategoryLogic::child($id) as $ch1){
            array_push($cate_ids,$ch1['id']);
            foreach (CategoryLogic::child($ch1['id']) as $ch2){
                array_push($cate_ids,$ch2['id']);
            }
        }

        return Item::whereIn('category_id', $cate_ids)
            ->whereHas('module.zones', function($query)use($zone_id){
                $query->whereIn('zones.id', json_decode($zone_id, true));
            })
            ->whereHas('store', function($query)use($zone_id){
                $query->whereIn('zone_id', json_decode($zone_id, true))->whereHas('zone.modules',function($query){
                    $query->when(config('module.current_module_data'), function($query){
                        $query->where('modules.id', config('module.current_module_data')['id']);
                    });
                });
            })
            ->get();
    }


    public static function featured_category_products($zone_id, int $limit,int $offset, $type)
    {
        $paginator = Item::active()->type($type)
            ->whereHas('module.zones', function($query)use($zone_id){
                $query->whereIn('zones.id', json_decode($zone_id, true));
            })
            ->whereHas('store', function($query)use($zone_id){
                $query->whereIn('zone_id', json_decode($zone_id, true))->whereHas('zone.modules',function($query){
                    $query->when(config('module.current_module_data'), function($query){
                        $query->where('modules.id', config('module.current_module_data')['id']);
                    });
                });
            })
            ->whereHas('category',function($q){
                return $q->where(['featured' => 1 , 'status' => 1 , 'module_id' => config('module.current_module_data')['id']]);
            })
            ->latest()->paginate($limit, ['*'], 'page', $offset);

        $item_categories = Item::active()->type($type)
            ->whereHas('module.zones', function($query)use($zone_id){
                $query->whereIn('zones.id', json_decode($zone_id, true));
            })
            ->whereHas('store', function($query)use($zone_id){
                $query->whereIn('zone_id', json_decode($zone_id, true))->whereHas('zone.modules',function($query){
                    $query->when(config('module.current_module_data'), function($query){
                        $query->where('modules.id', config('module.current_module_data')['id']);
                    });
                });
            })
            ->whereHas('category',function($q){
                return $q->where(['featured' => 1 , 'status' => 1 , 'module_id' => config('module.current_module_data')['id']]);
            })
            ->pluck('category_id')->toArray();

        $item_categories = array_unique($item_categories);

        $categories = Category::where(['featured' => 1 , 'status' => 1])->whereIn('id',$item_categories)->get(['id','name','image']);

        return [
            'total_size' => $paginator->total(),
            'limit' => $limit,
            'offset' => $offset,
            'categories' => $categories,
            'products' => $paginator->items()
        ];
    }
}
