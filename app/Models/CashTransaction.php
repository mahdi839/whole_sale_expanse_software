<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'direction',
        'type',
        'amount',
        'date',
        'payment_method',
        'source_type',
        'source_id',
        'customer_id',
        'supplier_id',
        'sales_man_id',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (CashTransaction $transaction) {
            if (empty($transaction->reference)) {
                $transaction->reference = static::generateReference();
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

    public function salesMan()
    {
        return $this->belongsTo(SalesMan::class);
    }

    public static function generateReference(): string
    {
        $last = static::orderByDesc('id')->value('reference');
        $next = $last ? ((int) preg_replace('/\D/', '', $last)) + 1 : 1;

        return 'CASH-' . str_pad($next, 6, '0', STR_PAD_LEFT);
    }
}
