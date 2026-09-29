<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Airline extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'iata_code',
        'icao_code',
        'country',
        'call_sign',
        'logo',
        'ffnumber',
        'status',
    ];
}


