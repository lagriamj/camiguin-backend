<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DestinationRentals extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'rental_id',
        'destination_id'
    ];

    public function rentals(): BelongsTo
    {
        return $this->belongsTo(Rentals::class, 'rental_id', 'id');
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class, 'destination_id', 'id');
    }

    public function destinationOrderRentals(): BelongsTo
    {
        return $this->belongsTo(DestinationOrderRentals::class, 'destination_rental_id', 'id');
    }
}
