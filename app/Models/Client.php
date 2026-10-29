<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'lead_id',
        'package_id',
        'package_type',
        'name',
        'company_name',
        'passport_number',
        'passport_document',
        'cnic',
        'cnic_document',
        'phone',
        'email',
        'type',
        'status',
        'opening_balance_pkr',
        'opening_balance_sar',
        'opening_balance_pkr_type',
        'opening_balance_sar_type',
        'opening_balance_roe',
    ];

    public function getCnicDocumentUrlAttribute()
    {
        if (empty($this->cnic_document)) {
            return null;
        }

        if (str_starts_with($this->cnic_document, 'http://') || str_starts_with($this->cnic_document, 'https://')) {
            return $this->cnic_document;
        }

        $base = basename($this->cnic_document);
        if (file_exists(public_path('assets/images/client_documents/' . $base))) {
            return asset('assets/images/client_documents/' . $base);
        }

        if (file_exists(public_path($this->cnic_document))) {
            return asset($this->cnic_document);
        }

        return asset('assets/images/client_documents/' . $base);
    }

    public function getPassportDocumentUrlAttribute()
    {
        if (empty($this->passport_document)) {
            return null;
        }

        if (str_starts_with($this->passport_document, 'http://') || str_starts_with($this->passport_document, 'https://')) {
            return $this->passport_document;
        }

        $base = basename($this->passport_document);
        if (file_exists(public_path('assets/images/client_documents/' . $base))) {
            return asset('assets/images/client_documents/' . $base);
        }

        if (file_exists(public_path($this->passport_document))) {
            return asset($this->passport_document);
        }

        return asset('assets/images/client_documents/' . $base);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function getClientNameAttribute()
    {
        return $this->name;
    }
}
