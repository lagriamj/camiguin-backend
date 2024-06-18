<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cqrcode extends Model
{
    use HasFactory;
    protected $connection = 'second_database';
    protected $table  = 'individuals';
}
