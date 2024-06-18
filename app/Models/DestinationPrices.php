<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DestinationPrices extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'destination_id',
        'price_type',
        'price',
    ];
}