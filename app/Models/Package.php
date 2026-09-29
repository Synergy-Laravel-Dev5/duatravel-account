<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    public function accommodations()
    {
        return $this->hasMany(PackageAccommodation::class);
    }

    public function itinerary()
    {
        return $this->hasOne(PackageItinerary::class);
    }

    public function terms()
    {
        return $this->hasOne(PackageTerm::class);
    }

    public function maktabAddress()
    {
        return $this->hasOne(PackageMaktabAddress::class);
    }

    public function transports()
    {
        return $this->hasMany(PackageTransport::class);
    }
    public function transportFlights()
    {
        return $this->hasMany(PackageTransportFlight::class);
    }
    public function transportTrains()
    {
        return $this->hasMany(PackageTransportTrain::class);
    }
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function giveaways()
    {
        return $this->belongsToMany(Giveaway::class, 'package_giveaway');
    }
    public function trainingSessions()
    {
        return $this->belongsToMany(TrainingSession::class, 'package_training_session');
    }
}
