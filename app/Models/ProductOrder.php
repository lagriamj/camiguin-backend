<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductOrder extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'product_id',
        'individuals_id',
        'access',
        'payment_method',
        'reference_number',
        'transaction_number',
        'status',
        'hash'
    ];

    public function cqrcode(): BelongsTo
    {
        return $this->belongsTo('App\Models\Cqrcode', 'individuals_id');
    }

    public function productOrderItems(): HasMany
    {
        return $this->hasMany(ProductOrderItems::class, 'product_order_id', 'id');
    }
}
