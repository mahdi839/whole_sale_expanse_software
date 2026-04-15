<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SaleReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'sale_id',
        'customer_id',
        'product_id',
        'product_name',
        'product_code',
        'qty',
        'price_on_sale',
        'discount',
        'subtotal',
        'return_amount',
        'return_type',
        'return_status',
        'payment_method',
        'cash_memo',
        'document',
        'reason',
        'note',
        'date',
    ];

    protected $casts = [
        'date'          => 'date',
        'qty'           => 'decimal:2',
        'price_on_sale' => 'decimal:2',
        'discount'      => 'decimal:2',
        'subtotal'      => 'decimal:2',
        'return_amount' => 'decimal:2',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public static function generateReference(): string
    {
        $last = static::orderByDesc('id')->value('reference');

        if ($last) {
            $number = (int) preg_replace('/\D/', '', $last);
            $next   = $number + 1;
        } else {
            $next = 1;
        }

        return 'RET-' . str_pad($next, 6, '0', STR_PAD_LEFT);
    }
}