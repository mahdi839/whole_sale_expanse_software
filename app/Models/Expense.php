<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'reference',
        'category',
        'sales_man_id',
        'amount',
        'date',
        'note',
        'document',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    public static function generateReference(): string
    {
        $last = self::latest('id')->first();
        $nextId = $last ? $last->id + 1 : 1;

        return 'EXP-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
    }

    public function salesMan()
    {
        return $this->belongsTo(SalesMan::class);
    }
}
