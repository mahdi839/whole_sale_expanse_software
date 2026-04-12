<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
     protected $fillable = [
        'reference',
        'seller_store_name',
        'purchased_by',
        'product_name',
        'product_code',
        'qty',
        'price',
        'cash_memo',
        'date',
        'payment_method',
        'other_cost',
        'document',
        'purchase_status',
        'payment_status',
        'note',
        'subtotal',
        'grand_total',
    ];

    protected $casts = [
        'date' => 'date',
        'qty' => 'decimal:2',
        'price' => 'decimal:2',
        'other_cost' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function ($purchase) {
            if (empty($purchase->reference)) {
                $purchase->reference = self::generateReference();
            }
        });
    }

    public static function generateReference(): string
    {
        $lastId = static::max('id') ?? 0;
        $next = $lastId + 1;

        return 'PUR-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }
}
