<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingTransport extends Model
{
    protected $fillable = [
        'booking_id',
        'supplier_id',
        'currency',
        'cost_roe',
        'sale_roe',
        'roe',
        'route',
        'vehicle',
        'quantity',
        'status',
        'pax',
        'travel_date',
        'travel_time',
        'flight_no',
        'pickup_from',
        'destination',
        'brn',
        'airline',
        'transport_type',
        'booking_date',
        'option_date',
        'cost_rate',
        'cost_amount',
        'selling_rate',
        'selling_amount',
        'notes',
        'transport_ticket',
        'voucher_no'
    ];

    protected $casts = [
        'travel_date' => 'date',
        'booking_date' => 'date',
        'option_date' => 'date',
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
