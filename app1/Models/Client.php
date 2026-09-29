<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'lead_id',
        'package_id',
        'name',
        'company_name',
        'passport_number',
        'cnic',
        'phone',
        'email',
        'type',
        'status',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }
}
