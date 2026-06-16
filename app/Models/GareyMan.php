<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GareyMan extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'address',
        'nid_passport_no',
        'total_paid',
        'total_due',
        'advance',
    ];

    protected $casts = [
        'total_paid' => 'decimal:2',
        'total_due' => 'decimal:2',
        'advance' => 'decimal:2',
    ];

    public function workLogs()
    {
        return $this->hasMany(GareyManWorkLog::class);
    }
}
