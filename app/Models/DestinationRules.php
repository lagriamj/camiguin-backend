<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DestinationRules extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'destination_id',
        'rule_id'
    ];

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class, 'destination_id');
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(Rules::class, 'rule_id');
    }
}
