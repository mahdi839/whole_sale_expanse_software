<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'customer_id',
        'product_id',
        'product_name',
        'product_code',
        'qty',
        'price_on_sale',
        'discount',
        'subtotal',
        'grand_total',
        'paid',
        'due',
        'cash_memo',
        'payment_method',
        'purchase_status',
        'payment_status',
        'document',
        'note',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
        'qty' => 'decimal:2',
        'price_on_sale' => 'decimal:2',
        'discount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid' => 'decimal:2',
        'due' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Auto-generate reference: SALE-000001, SALE-000002, …
     */
    public static function generateReference(): string
    {
        $last = static::orderByDesc('id')->value('reference');

        if ($last) {
            $number = (int) preg_replace('/\D/', '', $last);
            $next = $number + 1;
        } else {
            $next = 1;
        }

        return 'SALE-'.str_pad($next, 6, '0', STR_PAD_LEFT);
    }
}
