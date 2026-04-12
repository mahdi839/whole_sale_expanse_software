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
        'product_id',
        'product_name',
        'product_code',
        'purchased_by',
        'qty',
        'price',
        'other_cost',
        'subtotal',
        'grand_total',
        'due_amount',
        'paid_amount',
        'cash_memo',
        'date',
        'payment_method',
        'document',
        'note',
        'purchase_status',
        'payment_status',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    // ── Relationships ──────────────────────────────────────
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // ── Auto-generate reference ────────────────────────────
    public static function generateReference(): string
    {
        $prefix = 'PUR-' . date('Ymd') . '-';
        $last   = static::where('reference', 'like', $prefix . '%')
                        ->orderByDesc('id')
                        ->value('reference');

        $next = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}