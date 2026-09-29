<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageAccommodation extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'azizia_date' => 'date',
        'actual_check_in_time' => 'datetime',
        'actual_check_out_time' => 'datetime',
    ];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}
