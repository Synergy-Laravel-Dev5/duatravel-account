<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingHotel extends Model
{
    protected $fillable = [
        'booking_id',
        'location',
        'hotel_name',
        'supplier_id',
        'roe',
        'no_of_nights',
        'check_in',
        'check_out',
        'room_type',
        'no_of_rooms',
        'room_number',
        'gender',
        'cost_per_night',
        'cost_amount',
        'selling_per_night',
        'selling_amount',
        'hotel_voucher'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
