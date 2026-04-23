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
        'image',
    ];

    public function stock()
    {
        return $this->hasOne(Stock::class);
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
