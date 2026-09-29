<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageTransportFlight extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'departure_date' => 'date',
        'arrival_date' => 'date',
        'is_preferred' => 'boolean',
    ];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
