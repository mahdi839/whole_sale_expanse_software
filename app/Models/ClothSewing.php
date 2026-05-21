<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClothSewing extends Model
{
    protected $fillable = [
        'tailor_id',
        'product_id',
        'item_qty',
        'date',
    ];

    protected $casts = [
        'item_qty' => 'decimal:2',
        'date' => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function tailor()
    {
        return $this->belongsTo(Tailor::class);
    }
}
