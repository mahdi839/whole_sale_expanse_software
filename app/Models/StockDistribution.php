<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockDistribution extends Model
{
    protected $fillable = [
        'shop_id',
        'distributor',
        'carry_man',
        'receiver',
        'distribution_date',
        'status',
        'action_note',
        'received_at',
        'received_by',
    ];

    protected $casts = [
        'distribution_date' => 'date',
        'received_at' => 'datetime',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function items()
    {
        return $this->hasMany(StockDistributionItem::class);
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
