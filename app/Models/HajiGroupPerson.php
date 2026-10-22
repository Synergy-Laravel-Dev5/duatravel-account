<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HajiGroupPerson extends Model
{
    use HasFactory;

    protected $table = 'haji_group_persons';

    protected $guarded = ['id'];

    public function group()
    {
        return $this->belongsTo(HajiGroup::class, 'haji_group_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function person()
    {
        return $this->belongsTo(BookingPerson::class, 'booking_person_id');
    }
}
