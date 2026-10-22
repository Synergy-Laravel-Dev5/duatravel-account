<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TravelGroup extends Model
{
    use SoftDeletes;

    protected $table = 'travel_groups';

    protected $fillable = [
        'group_type',
        'group_name',
        'airline_id',
        'flight_id',
        'flight_number',
        'flight_date',
        'flight_time',
        'pnr',
        'departure_city',
        'arrival_city',
        'status',
        'notes',
    ];

    protected $casts = [
        'flight_date' => 'date',
    ];

    public function airline()
    {
        return $this->belongsTo(Airline::class);
    }

    public function flight()
    {
        return $this->belongsTo(Flight::class);
    }

    public function persons()
    {
        return $this->belongsToMany(
            BookingPerson::class,
            'travel_group_persons',
            'travel_group_id',
            'booking_person_id'
        )->withPivot('booking_id')->withTimestamps();
    }
}
