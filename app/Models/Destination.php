<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Destination extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'destination_category_id',
        'name',
        'description',
        'address',
        'limit',
        'draft',
        'status'
    ];
    public function destinationPrice(): HasMany
    {
        return $this->hasMany(DestinationPrices::class, 'destination_id');
    }
    public function destinationCategory(): BelongsTo
    {
        return $this->belongsTo(DestinationCategory::class, 'destination_category_id', 'id');
    }
    public function destinationImages(): HasMany
    {
        return $this->hasMany(DestinationImages::class, 'destination_id');
    }
    public function destinationRules(): HasMany
    {
        return $this->hasMany(DestinationRules::class, 'destination_id');
    }
    public function destinationTourType(): HasMany
    {
        return $this->hasMany(DestinationTourType::class, 'destination_id', 'id');
    }

    public function destinationUserCarts(): HasMany
    {
        return $this->hasMany(DestinationUserCart::class, 'destination_id', 'id');
    }

    public function destinationOrderItems(): HasMany
    {
        return $this->hasMany(DestinationOrderItems::class, 'destination_id', 'id');
    }

    public function kiosk(): HasOne
    {
        return $this->hasOne(Kiosk::class, 'destination_id', 'id');
    }

    public function destinationRentals(): HasMany
    {
        return $this->hasMany(DestinationRentals::class, 'destination_id', 'id');
    }

    public function destinationMaintenance(): HasOne
    {
        return $this->hasOne(DestinationMaintenance::class, 'destination_id', 'id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(DestinationOrderItems::class, 'destination_id', 'id');
    }
}
