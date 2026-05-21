<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tailor extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'address',
        'profile_picture',
        'document_path',
    ];

    public function clothSewings()
    {
        return $this->hasMany(ClothSewing::class);
    }

    public function cashTransactions()
    {
        return $this->hasMany(CashTransaction::class);
    }

    public function receivedCloths()
    {
        return $this->hasMany(ReceivedCloth::class);
    }
}
