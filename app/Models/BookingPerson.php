<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingPerson extends Model
{
    protected $table = 'booking_persons';

    protected $fillable = [
        'booking_id',
        'full_name',
        'haji_id',
        'hb_number',
        'surname',
        'given_name',
        'father_name',
        'dob',
        'gender',
        'city',
        'blood_group',
        'passport_number',
        'room_number',
        'room_type',
        'hotel_name',
        'location',
        'room_gender',
        'cnic',
        'phone',
        'cnic_front',
        'cnic_back',
        'passport_photo',
        'photo',
        'medical_certificate',
        'nominee_name',
        'nominee_relation',
        'nominee_cnic',
        'nominee_mobile',
        'pax_type',
        'flight_ticket',
        'flight_cost',
        'flight_sell',
        'flight_supplier_id',
        'flight_pnr',
        'flight_date',
        'flight_time',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function flightSupplier()
    {
        return $this->belongsTo(Supplier::class, 'flight_supplier_id');
    }

    public function travelGroups()
    {
        return $this->belongsToMany(
            TravelGroup::class,
            'travel_group_persons',
            'booking_person_id',
            'travel_group_id'
        )->withPivot('booking_id')->withTimestamps();
    }

    public function hajiGroups()
    {
        return $this->belongsToMany(
            HajiGroup::class,
            'haji_group_persons',
            'booking_person_id',
            'haji_group_id'
        )->withPivot('id', 'booking_id', 'seat_number', 'room_number', 'bed_number')->withTimestamps();
    }
}
