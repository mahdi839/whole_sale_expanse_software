<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tailor extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'address',
        'profile_picture',
        'document_path',
        'total_paid',
        'total_due',
        'advance',
    ];

    protected $casts = [
        'total_paid' => 'decimal:2',
        'total_due' => 'decimal:2',
        'advance' => 'decimal:2',
    ];

    public function clothSewings()
    {
        return $this->hasMany(ClothSewing::class);
    }

    public function cashTransactions()
    {
        return $this->hasMany(CashTransaction::class);
    }

    public function receivedCloths()
    {
        return $this->hasMany(ReceivedCloth::class);
    }

    public function manualDues()
    {
        return $this->hasMany(ManualDue::class);
    }

    public function recalculateFinancials(): void
    {
        $manualDue = (float) $this->manualDues()
            ->selectRaw('COALESCE(SUM(CASE WHEN adjustment_type = "subtract" THEN -amount ELSE amount END), 0) as total')
            ->value('total');

        $this->updateQuietly([
            'total_due' => max(0, $manualDue - (float) $this->total_paid),
        ]);
    }
}
