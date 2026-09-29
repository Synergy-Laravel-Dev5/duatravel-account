<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageTerm extends Model
{
    protected $table = 'package_terms';
    protected $guarded = ['id'];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
