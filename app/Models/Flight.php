<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Flight extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'airline_id',
        'name',
        'outbound_flight_no',
        'outbound_departure',
        'outbound_arrival',
        'inbound_flight_no',
        'inbound_departure',
        'inbound_arrival',
        'economy_seats',
        'business_seats',
        'status',
    ];

    protected $casts = [
        'outbound_departure' => 'datetime',
        'outbound_arrival' => 'datetime',
        'inbound_departure' => 'datetime',
        'inbound_arrival' => 'datetime',
    ];

    public function airline()
    {
        return $this->belongsTo(Airline::class);
    }

    public function sectors()
    {
        return $this->hasMany(FlightSector::class);
    }

    public function pnrs()
    {
        return $this->hasMany(FlightPnr::class);
    }
}
