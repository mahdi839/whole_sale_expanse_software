<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;
 
    protected $fillable = [
        'code',
        'full_name',
        'phone',
        'total_sale',
        'total_paid',
        'due',
    ];
 
    protected $casts = [
        'total_sale' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'due'        => 'decimal:2',
    ];
 
    /**
     * Auto-generate a unique customer code before creating.
     * Format: CUST-000001, CUST-000002, …
     */
    protected static function booted(): void
    {
        static::creating(function (Customer $customer) {
            if (empty($customer->code)) {
                $customer->code = static::generateCode();
            }
        });
    }
 
    public static function generateCode(): string
    {
        $last = static::orderByDesc('id')->value('code');
 
        if ($last) {
            // Extract numeric part and increment
            $number = (int) preg_replace('/\D/', '', $last);
            $next   = $number + 1;
        } else {
            $next = 1;
        }
 
        return 'CUST-' . str_pad($next, 6, '0', STR_PAD_LEFT);
    }
 
    /**
     * Recalculate 'due' whenever total_sale or total_paid changes.
     */
    public function recalculateDue(): void
    {
        $this->due = max(0, $this->total_sale - $this->total_paid);
        $this->saveQuietly();
    }
}
