<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyAddress extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'is_preferred' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
