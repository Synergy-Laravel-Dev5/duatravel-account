<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingTransport extends Model
{
    protected $fillable = ['booking_id', 'route', 'transport_type', 'notes', 'transport_ticket'];
}
