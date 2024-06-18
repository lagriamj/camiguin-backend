<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class DestinationOrderItems extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'orders_id',
        'destination_id',
        'destination_tour_type_price_id',
        'price',
        'destination_tour_type_id',
        'used',
        'qr_code',
    ];

    public function order(): HasOne
    {
        return $this->hasOne(DestinationOrder::class, 'id', 'orders_id');
    }
    public function destination(): HasOne
    {
        return $this->hasOne(Destination::class, 'id', 'destination_id');
    }
    public function destinationTourType(): belongsTo
    {
        return $this->belongsTo(DestinationTourType::class, 'destination_tour_type_id', 'id');
    }

    public function destinationTourTypePrice(): BelongsTo
    {
        return $this->belongsTo(DestinationTourTypePrices::class, 'destination_tour_type_price_id', 'id');
    }

    public function tourTypePrice(): BelongsTo
    {
        return $this->belongsTo(DestinationTourTypePrices::class, 'destination_tour_type_price_id', 'id');
    }
}