<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryCompany extends Model
{
    use HasFactory;
    protected $fillable = ['order_id', 'company_name', 'tracking_url', 'serial_number'];

    public function order(){
        return $this->belongsTo(Order::class, 'order_id');
    }
}
