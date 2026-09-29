<?php

namespace App\Models;

use App\Enums\Place;
use App\Enums\AccommodationType;
use App\Enums\AccommodationCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hotel extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'hotel_number',
        'code',
        'name',
        'address',
        'contact',
        'email',
        'place',
        'accommodation_type',
        'accommodation_category',
        'logo',
        'status',
    ];

    protected $casts = [
        'place' => Place::class,
        'accommodation_type' => AccommodationType::class,
        'accommodation_category' => AccommodationCategory::class,
    ];

    public function roomInventories()
    {
        return $this->hasMany(RoomInventory::class);
    }

    public function getRoomSummaryAttribute()
    {
        return RoomInventory::getHotelSummary($this->id);
    }
}
