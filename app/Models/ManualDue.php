<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManualDue extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'party_type',
        'adjustment_type',
        'customer_id',
        'supplier_id',
        'tailor_id',
        'carry_man_id',
        'computer_man_id',
        'garey_man_id',
        'amount',
        'date',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (ManualDue $due) {
            if (empty($due->reference)) {
                $due->reference = static::generateReference();
            }
        });
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function tailor()
    {
        return $this->belongsTo(Tailor::class);
    }

    public function carryMan()
    {
        return $this->belongsTo(CarryMan::class);
    }

    public function computerMan()
    {
        return $this->belongsTo(ComputerMan::class);
    }

    public function gareyMan()
    {
        return $this->belongsTo(GareyMan::class);
    }

    public function partyName(): string
    {
        return match ($this->party_type) {
            'customer' => $this->customer?->full_name ?? '-',
            'supplier' => $this->supplier?->name ?? '-',
            'tailor' => $this->tailor?->name ?? '-',
            'carry_man' => $this->carryMan?->name ?? '-',
            'computer_man' => $this->computerMan?->name ?? '-',
            'garey_man' => $this->gareyMan?->name ?? '-',
            default => '-',
        };
    }

    public static function generateReference(): string
    {
        $last = static::orderByDesc('id')->value('reference');
        $next = $last ? ((int) preg_replace('/\D/', '', $last)) + 1 : 1;

        return 'DUE-' . str_pad($next, 6, '0', STR_PAD_LEFT);
    }
}
