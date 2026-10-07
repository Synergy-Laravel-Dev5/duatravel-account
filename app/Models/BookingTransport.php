<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingTransport extends Model
{
    protected $fillable = [
        'booking_id',
        'supplier_id',
        'roe',
        'route',
        'transport_type',
        'cost_rate',
        'cost_amount',
        'selling_rate',
        'selling_amount',
        'notes',
        'transport_ticket'
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
