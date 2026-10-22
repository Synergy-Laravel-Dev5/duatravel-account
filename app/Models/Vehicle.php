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

    public function getTitleAttribute()
    {
        $parts = array_filter([$this->brand_name, $this->vehicle_type, $this->plate_number]);
        return !empty($parts) ? implode(' - ', $parts) : ('Vehicle #' . $this->id);
    }
}
