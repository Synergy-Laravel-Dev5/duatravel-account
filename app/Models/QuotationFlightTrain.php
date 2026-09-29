<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotationFlightTrain extends Model
{
    use HasFactory;

    protected $table = 'quotation_flights_trains';

    protected $guarded = ['id'];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Company::class, 'supplier_id');
    }
}
