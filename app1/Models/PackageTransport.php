<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageTransport extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'arrival_date' => 'date',
    ];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
