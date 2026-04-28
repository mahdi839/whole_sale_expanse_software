<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'supplier_id',
        'seller_store_name',
        'bill_no',
        'purchased_by',
        'discount',
        'other_cost',
        'grand_total',
        'paid_amount',
        'due_amount',
        'cash_memo',
        'date',
        'payment_method',
        'document',
        'note',
        'purchase_status',
        'payment_status',
    ];

    protected $casts = [
        'date'         => 'date',
        'discount'     => 'decimal:2',
        'other_cost'   => 'decimal:2',
        'grand_total'  => 'decimal:2',
        'paid_amount'  => 'decimal:2',
        'due_amount'   => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function returns()
{
    return $this->hasMany(PurchaseReturn::class);
}

    public static function generateReference(): string
    {
        $last = static::orderByDesc('id')->value('reference');
        $next = $last ? ((int) preg_replace('/\D/', '', $last)) + 1 : 1;

        return 'PUR-' . str_pad($next, 6, '0', STR_PAD_LEFT);
    }
}