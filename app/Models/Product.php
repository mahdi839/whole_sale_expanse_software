<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Stock;
class Product extends Model
{
     use HasFactory;
 
    protected $fillable = [
        'product_name',
        'sku',
        'image',
    ];

    public function stock()
    {
        return $this->hasOne(Stock::class);
    }
}
