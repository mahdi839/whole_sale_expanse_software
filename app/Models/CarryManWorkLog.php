<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarryManWorkLog extends Model
{
    protected $fillable = [
        'carry_man_id',
        'date',
        'memo_no',
        'marka',
        'document_path',
        'bale_qty',
        'total_unit_kg',
        'rate_per_kg',
        'total_rate',
    ];

    protected $casts = [
        'date' => 'date',
        'bale_qty' => 'decimal:2',
        'total_unit_kg' => 'decimal:2',
        'rate_per_kg' => 'decimal:2',
        'total_rate' => 'decimal:2',
    ];

    public function carryMan()
    {
        return $this->belongsTo(CarryMan::class);
    }
}
