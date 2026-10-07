<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingVisa extends Model
{
    protected $table = 'booking_visas';

    protected $fillable = [
        'booking_id',
        'supplier_id',
        'roe',
        'passport_number',
        'given_name',
        'date_of_birth',
        'company',
        'send_to',
        'cost_rate',
        'cost_amount',
        'selling_rate',
        'selling_amount',
        'status',
        'visa_attachment',
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
