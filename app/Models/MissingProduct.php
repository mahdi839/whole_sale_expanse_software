<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MissingProduct extends Model
{
    protected $fillable = [
        'product_id',
        'supplier_id',
        'bill_no',
        'missing_qty',
        'purchase_rate',
        'purchase_value',
        'date',
        'document',
        'note',
    ];

    protected $casts = [
        'missing_qty' => 'decimal:2',
        'purchase_rate' => 'decimal:2',
        'purchase_value' => 'decimal:2',
        'date' => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
