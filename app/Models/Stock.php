<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $fillable = [
        'product_id',
        'shop_id',
        'stock_qty',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function scopeCentral($query)
    {
        return $query->whereNull('shop_id');
    }

    public function scopeForExistingLocation($query)
    {
        return $query->where(function ($query) {
            $query->whereNull('shop_id')
                ->orWhereHas('shop');
        });
    }

    public function scopeForExistingShop($query)
    {
        return $query->whereNotNull('shop_id')->whereHas('shop');
    }
}
