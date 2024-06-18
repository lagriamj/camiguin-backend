<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DestinationTourTypePrices extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'destination_tour_type_id',
        'tourist_type_id',
        'price',
        'type',
        'limit'
    ];

    public function destinationTourType(): BelongsTo
    {
        return $this->belongsTo(DestinationTourType::class, 'destination_tour_type_id');
    }

    public function touristType(): BelongsTo
    {
        return $this->belongsTo(TouristType::class, 'tourist_type_id');
    }

    public function destinationTourTypePrice(): HasMany
    {
        return $this->hasMany(DestinationOrderItems::class, 'destination_tour_type_price_id', 'id');
    }
}
