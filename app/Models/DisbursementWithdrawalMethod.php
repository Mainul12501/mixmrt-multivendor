<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisbursementWithdrawalMethod extends Model
{
    use HasFactory;
    protected $casts = [
        'delivery_man_id' => 'integer',
        'withdrawal_method_id' => 'integer',
        'store_id' => 'integer',
        'is_default'=>'boolean',
    ];

    protected $guarded = []; //mainul

    public function withdrawalMethod()
    {
        return $this->belongsTo(WithdrawalMethod::class);
    }
    public function store()
    {
        return $this->belongsTo(Store::class);
    }
    public function deliveryMan()
    {
        return $this->belongsTo(DeliveryMan::class);
    }
}
