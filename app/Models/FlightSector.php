<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlightSector extends Model
{
    protected $fillable = [
        'flight_id',
        'flight_no',
        'type',
        'destination',
        'departure',
        'arrival',
    ];

    protected $casts = [
        'departure' => 'datetime',
        'arrival' => 'datetime',
    ];

    public function flight()
    {
        return $this->belongsTo(Flight::class, 'flight_id');
    }
}
