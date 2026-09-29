<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingVisa extends Model
{
    protected $table = 'booking_visas';

    protected $fillable = [
        'booking_id',
        'passport_number',
        'given_name',
        'date_of_birth',
        'company',
        'send_to',
        'status',
        'visa_attachment',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
