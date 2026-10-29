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
        'opening_balance_pkr',
        'opening_balance_sar',
        'opening_balance_pkr_type',
        'opening_balance_sar_type',
        'opening_balance_roe',
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

    public function flightPersons()
    {
        return $this->hasMany(BookingPerson::class, 'flight_supplier_id');
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

        // Flight cost: individual passenger flight costs assigned to this supplier
        $personFlightCost = BookingPerson::where('flight_supplier_id', $this->id)
            ->where('flight_cost', '>', 0)
            ->sum('flight_cost');

        // Plus booking-level flight cost for bookings with this supplier where persons have no individual flight_supplier_id
        $bookingFlightCost = Booking::where('flight_supplier_id', $this->id)
            ->where('flight_cost', '>', 0)
            ->where(function ($q) {
                $q->doesntHave('persons')
                  ->orWhereDoesntHave('persons', function ($pq) {
                      $pq->whereNotNull('flight_supplier_id');
                  });
            })
            ->sum('flight_cost');

        $flightCost = (float) $personFlightCost + (float) $bookingFlightCost;
        $opening = (float) ($this->opening_balance_pkr ?: $this->opening_balance ?: 0);

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
