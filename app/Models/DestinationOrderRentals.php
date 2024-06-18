<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DestinationOrderRentals extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'destination_order_id',
        'destination_rental_id',
        'quantity'
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(DestinationOrder::class, 'destination_order_id', 'id');
    }

    public function destinationRental(): BelongsTo
    {
        return $this->belongsTo(DestinationRentals::class, 'destination_rental_id', 'id');
    }

    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rentals::class, 'destination_rental_id', 'id');
    }
}
