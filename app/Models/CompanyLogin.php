<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class CompanyLogin extends Authenticatable
{
    protected $table = 'company_logins';
    protected $fillable = ['company_id', 'email', 'password'];

    protected $hidden = ['password'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

}
