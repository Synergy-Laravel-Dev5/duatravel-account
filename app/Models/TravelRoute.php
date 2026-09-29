<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TravelRoute extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'arrival_date',
        'arrival_time',
        'vehicle_id',
        'sharing_type',
        'status',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function routes()
    {
        return $this->belongsToMany(Route::class, 'travel_route_routes');
    }
}
