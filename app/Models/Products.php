<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Products extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'product_category_id',
        'product_condition_id',
        'name',
        'quantity',
        'description',
        'vendor',
        'weight',
        'storage_condition',
        'pre_order',
        'status',
        'draft',
        'user_id'
    ];

    public function productPrice(): HasMany
    {
        return $this->hasMany(ProductPrice::class, 'product_id');
    }
    public function productCategory(): BelongsTo
    {
        return $this->belongsTo(ProductsCategory::class, 'product_category_id', 'id');
    }
    public function productImages(): HasMany
    {
        return $this->hasMany(ProductImages::class, 'product_id');
    }
    public function productVideos(): HasMany
    {
        return $this->hasMany(ProductVideo::class, 'product_id');
    }
    public function productShipping(): HasMany
    {
        return $this->hasMany(ProductShipping::class, 'product_id');
    }
    public function productCondition(): hasOne
    {
        return $this->hasOne(ProductCondition::class, 'id');
    }
    public function productOrders(): HasMany
    {
        return $this->hasMany(ProductOrder::class, 'product_id');
    }

    public function productUserCart(): HasMany
    {
        return $this->hasMany(ProductUserCart::class, 'product_id');
    }

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function productVariants(): HasMany
    {
        return $this->hasMany(ProductVariants::class, 'product_id');
    }
}