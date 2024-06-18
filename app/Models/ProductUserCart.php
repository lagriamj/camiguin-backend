<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductUserCart extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'individuals_id',
        'product_id',
        'product_price_id',
        'quantity'
    ];
    public function product():HasOne
    {
        return $this->hasOne(Products::class,'id', 'product_id');
    }
    public function productPrice():HasOne
    {
        return $this->hasOne(ProductPrice::class,'id', 'product_id');
    }
}
