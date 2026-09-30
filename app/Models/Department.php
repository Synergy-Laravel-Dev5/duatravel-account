<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use SoftDeletes;
    protected $guarded = ['id'];

    public function designations()
    {
        return $this->hasMany(Designation::class);
    }
}
