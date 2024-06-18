<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class DestinationTourType extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'destination_id',
        'tour_type_id',
        'limit',
        'time_in',
        'time_out',
    ];
    public function destinationTourTypePrice(): HasMany
    {
        return $this->hasMany(DestinationTourTypePrices::class, 'destination_tour_type_id');
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class, 'destination_id', 'id');
    }

    public function tourType(): BelongsTo
    {
        return $this->belongsTo(TourTypes::class, 'tour_type_id', 'id');
    }
}
