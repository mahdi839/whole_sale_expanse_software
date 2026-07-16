<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarryMan extends Model
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
        return $this->hasMany(CarryManWorkLog::class);
    }

    public function cashTransactions()
    {
        return $this->hasMany(CashTransaction::class);
    }

    public function manualDues()
    {
        return $this->hasMany(ManualDue::class);
    }

    public function recalculateFinancials(): void
    {
        $totalRate = (float) $this->workLogs()->sum('total_rate');
        $manualDue = (float) $this->manualDues()
            ->selectRaw('COALESCE(SUM(CASE WHEN adjustment_type = "subtract" THEN -amount ELSE amount END), 0) as total')
            ->value('total');
        $totalPayable = $totalRate + $manualDue;
        $totalPaid = (float) $this->total_paid;

        $this->updateQuietly([
            'total_due' => max(0, $totalPayable - $totalPaid),
            'advance' => max(0, $totalPaid - $totalPayable),
        ]);
    }
}
