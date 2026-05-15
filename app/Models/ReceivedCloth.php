<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceivedCloth extends Model
{
    protected $fillable = [
        'tailor_name',
        'tailor_id',
        'product_id',
        'item_qty',
        'date',
    ];

    protected $casts = [
        'item_qty' => 'decimal:2',
        'date' => 'date',
    ];

    public function tailor()
    {
        return $this->belongsTo(Tailor::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
