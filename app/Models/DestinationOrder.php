<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class DestinationOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected  $fillable = [
        'individuals_id',
        'payment_method',
        'departure_time',
        'reference_number',
        'transaction_number',
        'departure_time',
        'status',
        'check-in',
        'order_type',
        'kiosk_id',
        'or_number'
    ];

    public function cqrcode(): BelongsTo
    {
        return $this->belongsTo('App\Models\Cqrcode', 'individuals_id');
    }

    public function destination(): HasOne
    {
        return $this->hasOne(Destination::class, 'id');
    }

    public function destinationOrderItems(): HasMany
    {
        return $this->hasMany(DestinationOrderItems::class, 'orders_id', 'id');
    }

    public function kiosk(): BelongsTo
    {
        return $this->belongsTo(Kiosk::class, 'kiosk_id', 'id');
    }

    public function destinationOrderRentals(): HasMany
    {
        return $this->hasMany(DestinationOrderRentals::class, 'destination_order_id');
    }
}
