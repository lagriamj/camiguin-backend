<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TourTypes extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'name'
    ];
    public function destinationTourType():HasMany
    {
        return $this->hasMany(DestinationTourType::class,'tour_type_id');
    }
}
