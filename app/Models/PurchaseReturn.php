<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'purchase_id',
        'supplier_id',
        'bill_no',
        'discount',
        'subtotal',
        'return_amount',
        'return_type',
        'return_status',
        'payment_method',
        'cash_memo',
        'date',
        'document',
        'note',
    ];

    protected $casts = [
        'discount'      => 'decimal:2',
        'subtotal'      => 'decimal:2',
        'return_amount' => 'decimal:2',
        'date'          => 'date',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }

    public static function generateReference(): string
    {
        $last = static::orderByDesc('id')->value('reference');
        $next = $last ? ((int) preg_replace('/\D/', '', $last)) + 1 : 1;

        return 'PRT-' . str_pad($next, 6, '0', STR_PAD_LEFT);
    }
}