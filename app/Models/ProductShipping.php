<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductShipping extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'weight',
        'shipping_fee'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Products::class, 'id');
    }
}
