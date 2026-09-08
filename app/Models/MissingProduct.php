<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MissingProduct extends Model
{
    protected $fillable = [
        'product_id',
        'missing_qty',
        'date',
        'document',
        'note',
    ];

    protected $casts = [
        'missing_qty' => 'decimal:2',
        'date' => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
