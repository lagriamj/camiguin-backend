<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class KioskCashier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'kiosk_id',
        'user_id'
    ];

    public function kiosk(): hasOne
    {
        return $this->hasOne(Kiosk::class, 'id', 'kiosk_id');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
