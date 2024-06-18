<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DestinationUserCart extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'individuals_id',
        'destination_id',
        'destination_tour_type_id',
        'destination_tour_type_price_id',
        'quantity',
        'departure_time'
    ];

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class, 'destination_id');
    }
    public function destinationTourType(): BelongsTo
    {
        return $this->belongsTo(DestinationTourType::class, 'destination_tour_type_id');
    }
    public function destinationTourTypePrice(): BelongsTo
    {
        return $this->belongsTo(DestinationTourTypePrices::class, 'destination_tour_type_price_id');
    }
}
