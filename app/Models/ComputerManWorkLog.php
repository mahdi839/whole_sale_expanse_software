<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComputerManWorkLog extends Model
{
    protected $fillable = [
        'computer_man_id',
        'product_id',
        'date',
        'computer_design_qty',
        'received_qty',
        'rate_per_piece',
        'total_rate',
    ];

    protected $casts = [
        'date' => 'date',
        'computer_design_qty' => 'decimal:2',
        'received_qty' => 'decimal:2',
        'rate_per_piece' => 'decimal:2',
        'total_rate' => 'decimal:2',
    ];

    public function computerMan()
    {
        return $this->belongsTo(ComputerMan::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
