<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariantItems extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_variant_id',
        'variant_item_name',
        'price',
        'stock',
    ];

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariants::class, 'product_variant_id', 'id');
    }
}