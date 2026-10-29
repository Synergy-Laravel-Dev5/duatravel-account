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
        'currency',
        'cost_roe',
        'sale_roe',
        'roe',
        'no_of_nights',
        'check_in',
        'check_out',
        'option_date',
        'booking_date',
        'room_type',
        'no_of_rooms',
        'room_number',
        'meal_plan',
        'room_view',
        'gender',
        'cost_per_night',
        'cost_amount',
        'selling_per_night',
        'selling_amount',
        'person_rates',
        'hotel_voucher',
        'voucher_no',
        'additional_rooms'
    ];

    protected $casts = [
        'person_rates' => 'array',
        'additional_rooms' => 'array',
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
