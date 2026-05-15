<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesMan extends Model
{
    protected $table = 'sales_men';

    protected $fillable = [
        'name',
        'address',
        'phone',
        'joining_date',
        'total_expense',
    ];

    protected $casts = [
        'joining_date' => 'date',
        'total_expense' => 'decimal:2',
    ];

    public function cashTransactions()
    {
        return $this->hasMany(CashTransaction::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}
