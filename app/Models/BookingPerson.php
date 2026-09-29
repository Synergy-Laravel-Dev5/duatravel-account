<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingPerson extends Model
{
    protected $table = 'booking_persons';

    protected $fillable = [
        'booking_id',
        'full_name',
        'surname',
        'given_name',
        'father_name',
        'dob',
        'gender',
        'city',
        'blood_group',
        'passport_number',
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
