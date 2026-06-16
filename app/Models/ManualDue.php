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

    public static function generateReference(): string
    {
        $last = static::orderByDesc('id')->value('reference');
        $next = $last ? ((int) preg_replace('/\D/', '', $last)) + 1 : 1;

        return 'DUE-' . str_pad($next, 6, '0', STR_PAD_LEFT);
    }
}
