<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;
 
    protected $fillable = [
        'code',
        'name',
        'phone',
        'email',
        'address',
        'total_purchase',
        'total_paid',
        'due',
    ];
 
    protected $casts = [
        'total_purchase' => 'decimal:2',
        'total_paid'     => 'decimal:2',
        'due'            => 'decimal:2',
    ];
 
    /**
     * Auto-generate supplier code on create.
     * Format: SUP-000001, SUP-000002, …
     */
    protected static function booted(): void
    {
        static::creating(function (Supplier $supplier) {
            if (empty($supplier->code)) {
                $supplier->code = static::generateCode();
            }
        });
    }
 
    public static function generateCode(): string
    {
        $last = static::orderByDesc('id')->value('code');
 
        $next = $last
            ? (int) preg_replace('/\D/', '', $last) + 1
            : 1;
 
        return 'SUP-' . str_pad($next, 6, '0', STR_PAD_LEFT);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function purchaseReturns()
    {
        return $this->hasMany(PurchaseReturn::class);
    }

    public function cashTransactions()
    {
        return $this->hasMany(CashTransaction::class);
    }

    public function manualDues()
    {
        return $this->hasMany(ManualDue::class);
    }
}
