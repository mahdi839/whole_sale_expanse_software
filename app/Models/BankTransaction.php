<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'shop_id',
        'direction',
        'type',
        'entry_type',
        'bank_name',
        'bank_details',
        'amount',
        'date',
        'source_type',
        'source_id',
        'customer_id',
        'supplier_id',
        'note',
        'document',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (BankTransaction $transaction) {
            if (empty($transaction->reference)) {
                $transaction->reference = static::generateReference();
            }
        });
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function partyName(): string
    {
        return $this->customer?->full_name
            ?? $this->supplier?->name
            ?? '—';
    }

    public static function generateReference(): string
    {
        $last = static::orderByDesc('id')->value('reference');
        $next = $last ? ((int) preg_replace('/\D/', '', $last)) + 1 : 1;

        return 'BANK-'.str_pad($next, 6, '0', STR_PAD_LEFT);
    }
}
