<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'phone',
        'address',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function cashTransactions()
    {
        return $this->hasMany(CashTransaction::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
}
