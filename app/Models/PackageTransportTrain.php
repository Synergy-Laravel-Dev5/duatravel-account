<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageTransportTrain extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'departure_date' => 'date',
        'arrival_date' => 'date',
    ];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
