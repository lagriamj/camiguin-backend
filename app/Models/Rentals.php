<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rentals extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'price',
        'limit'
    ];

    public function destinationRentals(): HasMany
    {
        return $this->hasMany(DestinationRentals::class, 'rental_id', 'id');
    }

    public function order(): HasMany
    {
        return $this->hasMany(DestinationOrderRentals::class, 'destination_rental_id', 'id');
    }
}
