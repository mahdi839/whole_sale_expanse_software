<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GareyManWorkLog extends Model
{
    protected $fillable = [
        'garey_man_id',
        'date',
        'memo_no',
        'qty',
        'received_qty',
        'unit',
        'rate_per_goj',
        'total_rate',
    ];

    protected $casts = [
        'date' => 'date',
        'qty' => 'decimal:2',
        'received_qty' => 'decimal:2',
        'rate_per_goj' => 'decimal:2',
        'total_rate' => 'decimal:2',
    ];

    public function gareyMan()
    {
        return $this->belongsTo(GareyMan::class);
    }
}
