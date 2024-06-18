<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActionAuthorization extends Model
{
    use HasFactory;
    protected $fillable = [
        'auth_key',
        'user_id',
        'date_expired'
    ];
}
