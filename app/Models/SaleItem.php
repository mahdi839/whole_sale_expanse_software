<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
   protected $fillable = [
        'sale_id',
        'product_id',
        'purchase_item_id',
        'batch',
        'qty',
        'price_on_sale',
        'cost_price',
        'profit',
        'line_total',
        'line_profit',
    ];

    protected $casts = [
        'qty'           => 'decimal:2',
        'price_on_sale' => 'decimal:2',
        'cost_price'    => 'decimal:2',
        'profit'        => 'decimal:2',
        'line_total'    => 'decimal:2',
        'line_profit'   => 'decimal:2',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function purchaseItem()
    {
        return $this->belongsTo(PurchaseItem::class);
    }

    public function returnItems()
    {
        return $this->hasMany(SaleReturnItem::class);
    }
}
