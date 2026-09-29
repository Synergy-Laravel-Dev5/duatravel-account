<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Giveaway extends Model
{
    protected $guarded = ['id'];

    public function packages()
    {
        return $this->belongsToMany(Package::class, 'package_giveaway');
    }
}
