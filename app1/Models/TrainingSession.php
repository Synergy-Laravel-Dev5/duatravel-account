<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingSession extends Model
{
    protected $guarded = ['id'];

    public function packages()
    {
        return $this->belongsToMany(Package::class, 'package_training_session');
    }
}
