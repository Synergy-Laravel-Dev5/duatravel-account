<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HajiGroup extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'haji_groups';

    protected $guarded = ['id'];

    protected $casts = [
        'flight_date' => 'date',
        'bus_capacity' => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function airline()
    {
        return $this->belongsTo(Airline::class);
    }

    public function flight()
    {
        return $this->belongsTo(Flight::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function travelRoute()
    {
        return $this->belongsTo(TravelRoute::class);
    }

    public function groupPersons()
    {
        return $this->hasMany(HajiGroupPerson::class, 'haji_group_id');
    }

    public function persons()
    {
        return $this->belongsToMany(
            BookingPerson::class,
            'haji_group_persons',
            'haji_group_id',
            'booking_person_id'
        )->withPivot('id', 'booking_id', 'seat_number', 'room_number', 'bed_number')->withTimestamps();
    }
}
