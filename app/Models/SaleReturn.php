<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'sale_id',
        'applied_sale_id',
        'customer_id',
        'discount',
        'subtotal',
        'return_amount',
        'return_type',
        'return_status',
        'payment_method',
        'cash_memo',
        'note',
    ];

    protected $casts = [
        'discount'      => 'decimal:2',
        'subtotal'      => 'decimal:2',
        'return_amount' => 'decimal:2',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function appliedSale()
    {
        return $this->belongsTo(Sale::class, 'applied_sale_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(SaleReturnItem::class);
    }

    public static function generateReference(): string
    {
        $last = static::orderByDesc('id')->value('reference');
        $next = $last ? ((int) preg_replace('/\D/', '', $last)) + 1 : 1;

        return 'RET-' . str_pad($next, 6, '0', STR_PAD_LEFT);
    }
}
