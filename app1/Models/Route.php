<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Route extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'start_place',
        'end_place',
        'status',
    ];

    public function getFormattedRouteAttribute(): string
    {
        return $this->start_place . ' TO ' . $this->end_place;
    }

    public function travelRoutes()
    {
        return $this->belongsToMany(TravelRoute::class, 'travel_route_routes');
    }
}
