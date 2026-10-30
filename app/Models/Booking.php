<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id',
        'haji_id',
        'company_id',
        'package_id',
        'booking_for',
        'package_type',
        'booking_category',
        'passport_number',
        'room_number',
        'package_year',
        'booking_date',
        'currency',
        'cnic',
        'no_of_pax',
        'pax_adults',
        'pax_children',
        'pax_infants',
        'care_of',
        'voucher_number',
        'flight_voucher_no',
        'hotel_voucher_no',
        'transport_voucher_no',
        'visa_voucher_no',
        'card_number',
        'phone',
        'emergency_phone',
        'cnic_front',
        'cnic_back',
        'passport_photo',
        'photo',
        'medical_certificate',
        'nominee_name',
        'nominee_relation',
        'nominee_cnic',
        'nominee_mobile',
        'departure_date',
        'departure_flight',
        'departure_time',
        'flight_time_format',
        'flight_trip_type',
        'departure_airline',
        'departure_pnr',
        'arrival_date',
        'arrival_flight',
        'arrival_time',
        'arrival_airline',
        'arrival_pnr',
        'flight_attachment',
        'visa_passport',
        'visa_given_name',
        'visa_sur_name',
        'visa_dob',
        'visa_company',
        'visa_send_to',
        'package_name',
        'flight_supplier_id',
        'flight_booking_date',
        'flight_option_date',
        'flight_currency',
        'flight_cost_roe',
        'flight_sale_roe',
        'flight_roe',
        'flight_cost_rate',
        'flight_cost',
        'flight_selling_rate',
        'adults_count',
        'children_count',
        'infants_count',
        'flight_adult_cost',
        'flight_adult_sell',
        'flight_child_cost',
        'flight_child_sell',
        'flight_infant_cost',
        'flight_infant_sell',
        'hotel_charges',
        'package_cost',
        'visa_charges',
        'transport_charges',
        'flight_charges',
        'other_charges',
        'discount',
        'additional_services_amount',
        'additional_services_detail',
        'total_amount',
        'total_received',
        'balance',
        'status',
        'agreement_signature',
    ];

    public function flightSupplier()
    {
        return $this->belongsTo(Supplier::class, 'flight_supplier_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function persons()
    {
        return $this->hasMany(BookingPerson::class);
    }

    public function hotels()
    {
        return $this->hasMany(BookingHotel::class);
    }

    public function transports()
    {
        return $this->hasMany(BookingTransport::class);
    }

    public function visas()
    {
        return $this->hasMany(BookingVisa::class);
    }
}
