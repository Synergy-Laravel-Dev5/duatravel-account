<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyBank extends Model
{
    protected $fillable = [
        'company_id',
        'bank_name',
        'account_title',
        'branch',
        'account_number',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
