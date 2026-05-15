<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Stock;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_name',
        'sku',
        'product_code',
        'purchase_price',
        'selling_price',
        'image',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
    ];

    public function stock()
    {
        return $this->hasOne(Stock::class)->whereNull('shop_id');
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function shopStock(?int $shopId)
    {
        return $this->hasOne(Stock::class)->where('shop_id', $shopId);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

     public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function sales()
    {
        return $this->belongsToMany(Sale::class, 'sale_items');
    }
}
