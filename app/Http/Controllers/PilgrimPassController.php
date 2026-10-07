<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingPerson;
use Illuminate\Http\Request;

class PilgrimPassController extends Controller
{
    /**
     * Public Live Mobile E-Pass / Itinerary view.
     * Anyone scanning the QR code on the badge will open this mobile page.
     *
     * @param int|string $id BookingPerson ID or Booking ID
     */
    public function show($id)
    {
        // Try to locate by person ID first, then by booking ID fallback
        $person = BookingPerson::with([
            'booking.client',
            'booking.company',
            'booking.package',
            'booking.hotels.supplier',
            'booking.transports',
            'booking.visas',
            'booking.flightSupplier'
        ])->find($id);

        $booking = null;
        if ($person) {
            $booking = $person->booking;
        } else {
            // Fallback: maybe $id is a Booking ID
            $booking = Booking::with([
                'client',
                'company',
                'package',
                'persons',
                'hotels.supplier',
                'transports',
                'visas',
                'flightSupplier'
            ])->find($id);

            if ($booking) {
                $person = $booking->persons->first();
            }
        }

        if (!$booking) {
            abort(404, 'Pilgrim / Booking record not found.');
        }

        // Categorize Hotels
        $makkahHotels = $booking->hotels->filter(function ($h) {
            return str_contains(strtolower($h->location ?? ''), 'makkah') || str_contains(strtolower($h->hotel_name ?? ''), 'makkah');
        });
        $madinahHotels = $booking->hotels->filter(function ($h) {
            return str_contains(strtolower($h->location ?? ''), 'madinah') || str_contains(strtolower($h->hotel_name ?? ''), 'madinah') || str_contains(strtolower($h->location ?? ''), 'medina');
        });
        $otherHotels = $booking->hotels->filter(function ($h) use ($makkahHotels, $madinahHotels) {
            return !$makkahHotels->contains('id', $h->id) && !$madinahHotels->contains('id', $h->id);
        });

        return view('pilgrim.epass', compact('person', 'booking', 'makkahHotels', 'madinahHotels', 'otherHotels'));
    }

    /**
     * Single Printable Lanyard ID Badge.
     */
    public function badge($id)
    {
        $person = BookingPerson::with([
            'booking.client',
            'booking.company',
            'booking.package',
            'booking.hotels',
            'booking.transports',
            'booking.visas'
        ])->findOrFail($id);

        $booking = $person->booking;
        $makkahHotel = $booking->hotels->first(function ($h) {
            return str_contains(strtolower($h->location ?? ''), 'makkah') || str_contains(strtolower($h->hotel_name ?? ''), 'makkah');
        }) ?? $booking->hotels->first();

        $madinahHotel = $booking->hotels->first(function ($h) {
            return str_contains(strtolower($h->location ?? ''), 'madinah') || str_contains(strtolower($h->hotel_name ?? ''), 'madinah') || str_contains(strtolower($h->location ?? ''), 'medina');
        });

        return view('pilgrim.badge', compact('person', 'booking', 'makkahHotel', 'madinahHotel'));
    }

    /**
     * Bulk Printable Lanyard ID Badges Sheet for all pilgrims in a booking.
     */
    public function bulkBadges($bookingId)
    {
        $booking = Booking::with([
            'client',
            'company',
            'package',
            'persons',
            'hotels',
            'transports',
            'visas'
        ])->findOrFail($bookingId);

        $makkahHotel = $booking->hotels->first(function ($h) {
            return str_contains(strtolower($h->location ?? ''), 'makkah') || str_contains(strtolower($h->hotel_name ?? ''), 'makkah');
        }) ?? $booking->hotels->first();

        $madinahHotel = $booking->hotels->first(function ($h) {
            return str_contains(strtolower($h->location ?? ''), 'madinah') || str_contains(strtolower($h->hotel_name ?? ''), 'madinah') || str_contains(strtolower($h->location ?? ''), 'medina');
        });

        return view('pilgrim.bulk_badges', compact('booking', 'makkahHotel', 'madinahHotel'));
    }
}
