<?php

namespace App\Models;

use App\Enums\CurrencyType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'companies';

    protected $guarded = [];

    protected $casts = [
        'currency_type' => CurrencyType::class,
        'established_on' => 'date',
        'dts_expiry' => 'date',
        'iata_expiry' => 'date',
        'director_cnic_expiry' => 'date',
    ];

    public function getNameAttribute()
    {
        return $this->attributes['company_name'] ?? $this->attributes['name'] ?? '';
    }

    public function addresses()
    {
        return $this->hasMany(CompanyAddress::class);
    }

    public function directors()
    {
        return $this->hasMany(CompanyDirector::class);
    }

    public function banks()
    {
        return $this->hasMany(CompanyBank::class);
    }

    public function contactNumbers()
    {
        return $this->hasMany(CompanyContactNumber::class);
    }

    public function emails()
    {
        return $this->hasMany(CompanyEmail::class);
    }

    public function licenses()
    {
        return $this->hasMany(CompanyLicense::class);
    }

    public function login()
    {
        return $this->hasOne(CompanyLogin::class);
    }
}
