<?php

namespace App\Models;

use App\CentralLogics\Helpers;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\DB;

class Refund extends Model
{
    use HasFactory;
    protected $guarded = ['id'];


    protected $casts = [
        'refund_amount' => 'float',
        'order_id' => 'integer',
        'user_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',

    ];
    protected $appends = ['image_full_url'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function getImageFullUrlAttribute(){
        $images = [];
        $value = is_array($this->image)?$this->image:json_decode($this->image,true);
        if ($value){
            foreach ($value as $item){
                $item = is_array($item)?$item:(is_object($item) && get_class($item) == 'stdClass' ? json_decode(json_encode($item), true):['img' => $item, 'storage' => 'public']);
                if($item['storage']=='s3'){
                    $images[] = Helpers::s3_storage_link('refund',$item['img']);
                }else{
                    $images[] = Helpers::local_storage_link('refund',$item['img']);
                }
            }
        }

        return $images;
    }

    public function storage()
    {
        return $this->morphMany(Storage::class, 'data');
    }
    protected static function booted()
    {
        static::addGlobalScope('storage', function ($builder) {
            $builder->with('storage');
        });
    }
    protected static function boot()
    {
        parent::boot();

    }
}
