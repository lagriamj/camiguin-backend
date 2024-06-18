<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kiosk extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'destination_id',
    ];

    public function destination(): HasOne
    {
        return $this->hasOne(Destination::class, 'id', 'destination_id');
    }

    public function kioskCashier(): HasMany
    {
        return $this->hasMany(KioskCashier::class, 'kiosk_id', 'id');
    }
}