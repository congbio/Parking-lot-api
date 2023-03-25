<?php

namespace App\Services\Implements;

use App\Events\TimeOutBookingEvent;
use App\Models\Booking;
use App\Models\ParkingSlot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ScheduleService
{
    public function setScheduleExpiredTime()
    {
        // Define the time max
        define('MAX_BOOKING_MiNUTE', 30);
        $now = Carbon::now();
        // Calculate the end time for the time frame
       

        $end_time = $now->copy()->addMinutes(MAX_BOOKING_MiNUTE);
        $bookings = Booking::whereBetween('returnDate', [$now, $end_time])
            ->groupBy('returnDate', 'bookDate', 'userId')
            ->select('returnDate', 'bookDate', 'userId')
            ->distinct()
            ->get();


        foreach ($bookings as $booking) {
            // Calculate the time difference between the current time and the returnDateTime for the booking
            $time_diff = $now->diffInMinutes($booking->returnDate);
            $user = User::findOrFail($booking->userId);
            $slotId = Booking::where('returnDate', $booking->returnDate)
                ->where('bookDate', $booking->bookDate)->where('userId', $booking->userId)->first()->slotId;
            $parkingInfo = ParkingSlot::find($slotId)->block->parkingLot;
            // Check if the booking is almost time out
            if ($time_diff <= MAX_BOOKING_MiNUTE) {

                try {
                    event(new TimeOutBookingEvent($user, $parkingInfo, $time_diff));
                } catch (\Throwable $th) {
                    Log::error('Error QRcode event: ' . $th->getMessage());
                }
            }
        }
    }
}
