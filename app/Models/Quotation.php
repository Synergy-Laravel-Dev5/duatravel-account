<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function accommodations()
    {
        return $this->hasMany(QuotationAccommodation::class);
    }

    public function mealPlans()
    {
        return $this->hasMany(QuotationMealPlan::class);
    }

    public function transfers()
    {
        return $this->hasMany(QuotationTransfer::class);
    }

    public function tours()
    {
        return $this->hasMany(QuotationTour::class);
    }

    public function flightsTrains()
    {
        return $this->hasMany(QuotationFlightTrain::class);
    }

    public function visas()
    {
        return $this->hasMany(QuotationVisa::class);
    }
}
