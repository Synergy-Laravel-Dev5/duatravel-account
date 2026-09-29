<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlightPnr extends Model
{
    protected $fillable = [
        'flight_id',
        'pnr_type',
        'pnr_name',
        'capacity',
    ];

    public function flight()
    {
        return $this->belongsTo(Flight::class, 'flight_id');
    }
}
