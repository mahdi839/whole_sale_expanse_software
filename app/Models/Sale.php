<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'shop_id',
        'user_id',
        'customer_id',
        'discount',
        'grand_total',
        'paid',
        'due',
        'return_amount',
        'cash_memo',
        'bell_no',
        'payment_method',
        'payment_status',
        'status',
        'note',
    ];

    protected $casts = [
        'discount'    => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid'        => 'decimal:2',
        'due'         => 'decimal:2',
        'return_amount' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function returns()
    {
        return $this->hasMany(SaleReturn::class);
    }

    public static function generateReference(): string
    {
        $last = static::orderByDesc('id')->value('reference');
        $next = $last ? ((int) preg_replace('/\D/', '', $last)) + 1 : 1;

        return 'SALE-' . str_pad($next, 6, '0', STR_PAD_LEFT);
    }
}
