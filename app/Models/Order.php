<?php

namespace App\Models;

use App\CentralLogics\Helpers;
use Carbon\Carbon;
use App\Scopes\ZoneScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use HasFactory;

    protected $casts = [
        'order_amount' => 'float',
        'coupon_discount_amount' => 'float',
        'total_tax_amount' => 'float',
        'store_discount_amount' => 'float',
        'flash_admin_discount_amount' => 'float',
        'flash_store_discount_amount' => 'float',
        'delivery_address_id' => 'integer',
        'delivery_man_id' => 'integer',
        'delivery_charge' => 'float',
        'additional_charge' => 'float',
        'original_delivery_charge' => 'float',
        'user_id' => 'integer',
        'zone_id' => 'integer',
        'scheduled' => 'integer',
        'store_id' => 'integer',
        'details_count' => 'integer',
        'module_id' => 'integer',
        'dm_vehicle_id' => 'integer',
        'processing_time' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'extra_packaging_amount' => 'float',
        'receiver_details' => 'array',
        'dm_tips' => 'float',
        'distance' => 'float',
        'tax_percentage' => 'float',
        'prescription_order' => 'boolean',
        'cutlery' => 'boolean',
        'is_guest' => 'boolean',
        'ref_bonus_amount' => 'float',
        'third_party' => 'boolean',
        'company_id' => 'integer',
    ];

    protected $appends = ['module_type','order_attachment_full_url','order_proof_full_url'];

    public function getOrderAttachmentFullUrlAttribute(){
        $images = [];
        $value = is_array($this->order_attachment)?$this->order_attachment:json_decode($this->order_attachment,true);
        if ($value){
            foreach ($value as $item){
                $item = is_array($item)?$item:(is_object($item) && get_class($item) == 'stdClass' ? json_decode(json_encode($item), true):['img' => $item, 'storage' => 'public']);
                if($item['storage']=='s3'){
                    $images[] = Helpers::s3_storage_link('order',$item['img']);
                }else{
                    $images[] = Helpers::local_storage_link('order',$item['img']);
                }
            }
        }

        return $images;
    }
    public function getOrderProofFullUrlAttribute(){
        $images = [];
        $value = is_array($this->order_proof)?$this->order_proof:json_decode($this->order_proof,true);
        if ($value){
            foreach ($value as $item){
                $item = is_array($item)?$item:(is_object($item) && get_class($item) == 'stdClass' ? json_decode(json_encode($item), true):['img' => $item, 'storage' => 'public']);
                if($item['storage']=='s3'){
                    $images[] = Helpers::s3_storage_link('order',$item['img']);
                }else{
                    $images[] = Helpers::local_storage_link('order',$item['img']);
                }
            }
        }

        return $images;
    }

    public function setDeliveryChargeAttribute($value)
    {
        $this->attributes['delivery_charge'] = round($value, 3);
    }

    public function cashback_history()
    {
        return $this->hasOne(CashBackHistory::class, 'order_id');
    }


    public function offline_payments()
    {
        return $this->belongsTo(OfflinePayments::class,'id','order_id');
    }

    public function details()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function payments()
    {
        return $this->hasMany(OrderPayment::class);
    }

    public function delivery_man()
    {
        return $this->belongsTo(DeliveryMan::class, 'delivery_man_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class, 'user_id','id');
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_code', 'code');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function delivery_history()
    {
        return $this->hasMany(DeliveryHistory::class, 'order_id');
    }

    public function dm_last_location()
    {
        // return $this->hasOne(DeliveryHistory::class, 'order_id')->latest();
        return $this->delivery_man->last_location();
    }

    public function transaction()
    {
        return $this->hasOne(OrderTransaction::class);
    }

    public function parcel_category()
    {
        return $this->belongsTo(ParcelCategory::class);
    }

    public function refund()
    {
        return $this->hasOne(Refund::class, 'order_id');
    }
    public function OrderReference()
    {
        return $this->hasOne(OrderReference::class, 'order_id');
    }
    public function delivery_company()
    {
        return $this->hasOne(DeliveryCompany::class,'id', 'company_id');
    }
    public function parcel_company()
    {
        return $this->belongsTo(Store::class,'parcel_company_id');
    }
    public function getModuleTypeAttribute()
    {
        return $this->module ? $this->module->module_type : null;
    }

    public function scopeAccepteByDeliveryman($query)
    {
        return $query->where('order_status', 'accepted');
    }

    public function scopePreparing($query)
    {
        return $query->whereIn('order_status', ['confirmed', 'processing', 'handover']);
    }

    public function scopeModule($query, $module_id)
    {
        return $query->where('module_id', $module_id);
    }

    public function scopeOngoing($query)
    {
        return $query->whereIn('order_status', ['accepted', 'confirmed', 'processing', 'handover', 'picked_up']);
    }

    public function scopeItemOnTheWay($query)
    {
        return $query->where('order_status', 'picked_up');
    }

    public function scopePending($query)
    {
        return $query->where('order_status', 'pending');
    }
    public function scopeParcelAll($query, $store_id)
    {
        return $query->where('order_status', 'pending')
        ->orWhere(function($query) use($store_id) {
            $query->whereIn('order_status', ['pending','confirmed,','handover','accepted','item_on_the_way','delivered','canceled','failed','scheduled','on_going'])
                  ->where('parcel_company_id', $store_id);
        });

    }

    public function scopeFailed($query)
    {
        return $query->where('order_status', 'failed');
    }

    public function scopeCanceled($query)
    {
        return $query->where('order_status', 'canceled');
    }

    public function scopeDelivered($query)
    {
        return $query->where('order_status', 'delivered');
    }

    public function scopeNotRefunded($query)
    {
        return $query->where(function ($query) {
            $query->whereNotIn('order_status', ['refunded']);
        });
    }

    public function scopeRefunded($query)
    {
        return $query->where('order_status', 'refunded');
    }
    public function scopeRefund_requested($query)
    {
        return $query->where('order_status', 'refund_requested');
    }

    public function scopeRefund_request_canceled($query)
    {
        return $query->where('order_status', 'refund_request_canceled');
    }

    public function scopeSearchingForDeliveryman($query)
    {
        return $query->whereNull('delivery_man_id')->whereIn('order_type', ['delivery', 'parcel'])->whereNotIn('order_status', ['delivered', 'failed', 'canceled', 'refund_requested', 'refund_request_canceled', 'refunded']);
    }

    public function scopeDelivery($query)
    {
        return $query->where('order_type', '=', 'delivery');
    }

    public function scopeScheduled($query)
    {
        return $query->whereRaw('created_at <> schedule_at')->where('scheduled', '1');
    }

    public function scopeOrderScheduledIn($query, $interval)
    {
        return $query->where(function ($query) use ($interval) {
            $query->whereRaw('created_at <> schedule_at')->where(function ($q) use ($interval) {
                $q->whereBetween('schedule_at', [Carbon::now()->toDateTimeString(), Carbon::now()->addMinutes($interval)->toDateTimeString()]);
            })->orWhere('schedule_at', '<', Carbon::now()->toDateTimeString());
        })->orWhereRaw('created_at = schedule_at');
    }


    public function scopeStoreOrder($query)
    {
        return $query->where(function ($q) {
            $q->where('order_type', 'take_away')->orWhere('order_type', 'delivery');
        });
    }
    public function scopeCompanyOrder($query)
    {
        return $query->where(function ($q) {
            $q->where('order_type', 'delivery');
        });
    }
    

    public function scopeDmOrder($query)
    {
        return $query->where(function ($q) {
            $q->where('order_type', 'parcel')->orWhere('order_type', 'delivery');
        });
    }

    public function scopeParcelOrder($query)
    {
        return $query->where('order_type', 'parcel')->OrWhere('store_id',null);
    }
    public function scopeNot_take_away($query)
    {
        return $query->where('order_type', '<>', 'take_away');
    }
    public function scopePos($query)
    {
        return $query->where('order_type', '=', 'pos');
    }

    public function scopeNotpos($query)
    {
        return $query->where('order_type', '<>', 'pos');
    }

    public function scopeNotDigitalOrder($query)
    {
        return $query->where(function ($q){
            $q->whereNotIn('payment_method', ['digital_payment','offline_payment'])->orwhereNot('order_status' , 'pending');
        });
    }

    public function getCreatedAtAttribute($value)
    {
        return date('Y-m-d H:i:s', strtotime($value));
    }

    protected static function booted()
    {
        static::addGlobalScope(new ZoneScope);
        static::addGlobalScope('storage', function ($builder) {
            $builder->with('storage');
        });
    }
    public function storage()
    {
        return $this->morphMany(Storage::class, 'data');
    }
    protected static function boot()
    {
        parent::boot();
    }
}
