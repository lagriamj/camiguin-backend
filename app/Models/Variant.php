<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Variant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name'
    ];

    public function productVariants(): HasMany
    {
        return $this->hasMany(ProductVariants::class, 'variant_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Products::class, 'variant_id');
    }
}