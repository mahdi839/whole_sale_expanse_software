<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockDistributionItem extends Model
{
    protected $fillable = [
        'stock_distribution_id',
        'product_id',
        'qty',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
    ];

    public function distribution()
    {
        return $this->belongsTo(StockDistribution::class, 'stock_distribution_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
