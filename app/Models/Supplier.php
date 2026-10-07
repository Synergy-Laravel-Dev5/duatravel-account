<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'company_name',
        'service_type',
        'contact_person',
        'phone',
        'email',
        'address',
        'city',
        'country',
        'opening_balance',
        'status',
    ];

    public function quotations()
    {
        return $this->hasMany(QuotationAccommodation::class, 'supplier_id');
    }

    public function roomInventories()
    {
        return $this->hasMany(RoomInventory::class, 'supplier_id');
    }

    public function bookingHotels()
    {
        return $this->hasMany(BookingHotel::class, 'supplier_id')->whereHas('booking');
    }

    public function bookingVisas()
    {
        return $this->hasMany(BookingVisa::class, 'supplier_id')->whereHas('booking');
    }

    public function bookingTransports()
    {
        return $this->hasMany(BookingTransport::class, 'supplier_id')->whereHas('booking');
    }

    public function bookingsAsFlightSupplier()
    {
        return $this->hasMany(Booking::class, 'flight_supplier_id');
    }

    public function expenseTransactions()
    {
        return $this->hasMany(ExpenseTransaction::class, 'supplier_id');
    }

    public function getTotalPayableAttribute()
    {
        $hotelCost = $this->bookingHotels()->sum('cost_amount');
        $visaCost = $this->bookingVisas()->sum('cost_amount');
        $transportCost = $this->bookingTransports()->sum('cost_amount');
        $flightCost = $this->bookingsAsFlightSupplier()->sum('flight_cost');
        $opening = (float) $this->opening_balance;

        return $opening + $hotelCost + $visaCost + $transportCost + $flightCost;
    }

    public function getTotalPaidAttribute()
    {
        // Total payments made to this supplier
        return $this->expenseTransactions()->sum('amount');
    }

    public function getBalanceDueAttribute()
    {
        return $this->total_payable - $this->total_paid;
    }
}
