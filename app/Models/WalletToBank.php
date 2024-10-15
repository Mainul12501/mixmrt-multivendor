<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletToBank extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'request_balance',
        'bank_name',
        'bank_account_number',
        'bank_routing_number',
        'notes',
        'payment_status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
