<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComputerMan extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'address',
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
        return $this->hasMany(ComputerManWorkLog::class);
    }

    public function cashTransactions()
    {
        return $this->hasMany(CashTransaction::class);
    }

    public function recalculateFinancials(): void
    {
        $totalRate = (float) $this->workLogs()->sum('total_rate');

        $this->updateQuietly([
            'total_due' => max(0, $totalRate - (float) $this->total_paid),
        ]);
    }
}
