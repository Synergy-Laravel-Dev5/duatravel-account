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
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
