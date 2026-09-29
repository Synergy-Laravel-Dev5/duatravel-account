<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'vehicle_type',
        'brand_name',
        'model_year',
        'plate_number',
        'supplier_name',
        'status',
    ];
}
