<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotationMealPlan extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function accommodation()
    {
        return $this->belongsTo(QuotationAccommodation::class, 'quotation_accommodation_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Company::class, 'supplier_id');
    }
}
