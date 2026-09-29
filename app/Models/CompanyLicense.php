<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyLicense extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'is_preferred'   => 'boolean',
        'date_of_issue'  => 'date',
        'valid_upto'     => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
