<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingHotel extends Model
{
    protected $fillable = [
        'booking_id',
        'location',
        'hotel_name',
        'no_of_nights',
        'check_in',
        'check_out',
        'room_type',
        'no_of_rooms',
        'hotel_voucher'
    ];
}
