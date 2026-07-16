<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Service;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;

class PublicBookingPageController extends Controller
{
    public function __invoke(Business $business): View
    {
        abort_unless($business->is_booking_enabled, 404);

        $services = Service::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'duration_min', 'buffer_min']);

        $staffMembers = Staff::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('booking.public-booking', [
            'business' => $business,
            'services' => $services,
            'staffMembers' => $staffMembers,
            'availabilityUrlTemplate' => route('api.public.availability', ['business' => $business->slug]),
            'bookingUrlTemplate' => route('api.public.book', ['business' => $business->slug]),
            'bookingMinDate' => Carbon::now($business->timezone)->toDateString(),
            'bookingMaxDate' => Carbon::now($business->timezone)
                ->addDays($business->booking_max_advance_days)
                ->toDateString(),
        ]);
    }
}
