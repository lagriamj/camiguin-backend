<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DestinationMaintenance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'destination_id',
        'maintenance_date',
        'name'
    ];

    public function Destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }
}
