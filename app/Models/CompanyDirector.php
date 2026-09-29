<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyDirector extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'cnic',
        'cnic_expiry',
        'cnic_front',
        'cnic_back',
        'shares_percent',
        'photo',
        'detail',
    ];

    protected $casts = [
        'cnic_expiry' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
