<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id',
        'product_id',
        'qty',
        'price_on_sale',
        'line_total',
    ];

    protected $casts = [
        'qty'           => 'decimal:2',
        'price_on_sale' => 'decimal:2',
        'line_total'    => 'decimal:2',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function returnItems()
    {
        return $this->hasMany(SaleReturnItem::class);
    }
}