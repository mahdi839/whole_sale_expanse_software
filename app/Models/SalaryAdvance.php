<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryAdvance extends Model
{
    protected $fillable = [
        'employee_id',
        'advance_month',
        'amount',
    ];

    protected $casts = [
        'advance_month' => 'date',
        'amount' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
