<?php

namespace App\Http\Controllers\DashBoard;

use App\Http\Controllers\Controller;
use App\Models\Block;
use App\Models\Booking;
use App\Models\ParkingLot;
use App\Models\ParkingSlot;
use App\Models\User;
use App\Models\UserParkingLot;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManagerController extends Controller
{
    /**
     * @OA\Get(
     ** path="/api/dashboard/parkingLots/{parkingLotId}/{period}", tags={"DashBoard"}, summary="get statics of one parkingLot with a Id",
     * operationId="parkingLotBookingStats",
     *   @OA\Parameter(
     *         name="parkingLotId",
     *         in="path",
     *         required=true,
     *         description="id parkingLot management",
     *         example=1000000,
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\Parameter(
     *         name="period",
     *         in="path",
     *         required=true,
     *         description="period day or week or month or year",
     *         example="day",
     *         @OA\Schema(type="string"),
     *     ),
     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/
    public function parkingLotBookingStats($parkingLotId, $period)
    {
        $now = Carbon::now()->locale('en');


        if ($period == 'day') {
            $end = $now->toDateTimeString();
            $start = $now->startOfMonth()->toDateTimeString();
            $groupBy = DB::raw('Date(bookDate)');
            $format = 'Y-m-d';
        } elseif ($period == 'week') {
            $end = $now->toDateTimeString();
            $start = $now->startOfMonth()->toDateTimeString();
            // return $start."............".$end;
            $groupBy = DB::raw('WEEK(bookDate)');
            $format = 'W';
        } elseif ($period == 'month') {
            $end = $now->toDateString();
            $start = $now->startOfYear()->toDateString();
            $groupBy = DB::raw('DATE_FORMAT(bookDate, "%Y-%m")');
            $format = 'Y-m';
        } elseif ($period == 'year') {
            $end = $now->endOfYear()->format('Y-m-d');
            $start = $now->subYears(5)->format('Y-m-d');
            // return $start. ' .........'.$end;
            $groupBy = DB::raw('YEAR(bookDate)');
            $format = 'Y';
        } else {
            return response()->json([
                'message' => "Invalid period specified.",
                'data' => null
            ], 400);
        }
        // Get the parking lot ids for the given user
        $parkingLotInfo = ParkingLot::where('id', $parkingLotId)->exists();

        if (!$parkingLotInfo) {
            return response()->json([
                'message' => "No parkingLot with this Id.",
                'data' => null
            ], 404);
        }
        // Get the block ids in the parking lots for the given user
        $blockIdsInParkingLots = Block::where('parkingLotId', $parkingLotId)->pluck('id');

        // Get the slot ids in the blocks for the given user
        $slotIdsInBlocks = ParkingSlot::whereIn('blockId', $blockIdsInParkingLots)->pluck('id');


        $bookings = Booking::whereIn('slotId', $slotIdsInBlocks)
            ->select(
                DB::raw("{$groupBy} as period"),
                DB::raw('SUM(payment) as total_sales'),
                DB::raw('COUNT(DISTINCT  userId) as total_users'),
                DB::raw('COUNT(DISTINCT bookDate) as total_bookings'),

            )
            ->whereBetween('bookDate', [$start, $end])
            ->groupBy($groupBy)
            ->get();
        if ($bookings->isEmpty()) {
            return response()->json([
                'message' => "No bookings data available for the specified period.",
                'data' => null
            ], 404);
        }

        $start = Carbon::parse($bookings->first()->period)->startOf($period);
        $end = Carbon::parse($bookings->last()->period)->endOf($period);
        $periods = $this->getPeriods($start, $end, $format, $period);
        $bookings = $this->fillMissingPeriods($periods, $bookings, $groupBy->getValue());

        $periodLabels = $bookings->pluck('period')->toArray();
        $salesTotals = $bookings->pluck('total_sales')->toArray();
        $uniqueUsers = $bookings->pluck('total_users')->toArray();
        foreach ($uniqueUsers as &$value) {
            if (is_null($value)) {
                $value = 0;
            }
        }
        $bookingCounts = $bookings->pluck('total_bookings')->toArray();
        foreach ($bookingCounts as &$value) {
            if (is_null($value)) {
                $value = 0;
            }
        }
        return response()->json([
            'message' => "Success!",
            'data' => [
                'periodLabels' => $periodLabels,
                'salesTotals' => $salesTotals,
                'uniqueUsers' => $uniqueUsers,
                'bookingCounts' => $bookingCounts,
            ]
        ], 200); // Return a JSON response with the sales data
    }
    /**
     * @OA\Get(
     ** path="/api/dashboard/parkingLots/{userId}", tags={"DashBoard"}, summary="get all parkinglot by user management",
     * operationId="getParkingUserManage",
     *   @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *         description="id user management",
     *         example=1000000,
     *         @OA\Schema(type="integer"),
     *     ),
     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/
    public function getParkingUserManage($userId)
    {
        // Get the user with the specified ID
        $user = User::findOrFail($userId);

        // Get the user parking lots
        $userParkingLots = $user->userParkingLots;

        // Initialize an array to store the parking lots
        $parkingLots = [];

        // Set the start and end datetimes to the current date and time
        $currentTime = now();
        $nextDayTime = now();
        // Loop through the user parking lots and add the associated parking lots to the array
        foreach ($userParkingLots as $userParkingLot) {

            $parkingLot = [
                'idParking' => $userParkingLot->parkingLot->id,
                'nameParkingLot' => $userParkingLot->parkingLot->nameParkingLot,
                'available' => 0,
                'booked' => 0,
                'totalRevenue' => 0,
                'numberOfBlocks' => $userParkingLot->parkingLot->blocks->count()
            ];

            // Get the slots for the parking lot
            $slots = $userParkingLot->parkingLot->blocks->flatMap(function ($block) {
                return $block->slots;
            });

            // Loop through the slots and check their availability for the current time period
            foreach ($slots as $slot) {
                $bookings = $slot->bookings()->where(function ($query) use ($currentTime, $nextDayTime) {
                    $query->where(function ($query) use ($currentTime, $nextDayTime) {
                        $query->where('bookDate', '>=', $currentTime)
                            ->where('bookDate', '<', $nextDayTime);
                    })->orWhere(function ($query) use ($currentTime, $nextDayTime) {
                        $query->where('returnDate', '>', $currentTime)
                            ->where('returnDate', '<=', $nextDayTime);
                    })->orWhere(function ($query) use ($currentTime, $nextDayTime) {
                        $query->where('bookDate', '<', $currentTime)
                            ->where('returnDate', '>', $nextDayTime);
                    });
                })->get();

                if ($bookings->isEmpty()) {
                    $parkingLot['available']++;
                } else {
                    $parkingLot['booked']++;
                    foreach ($bookings as $booking) {
                        $parkingLot['totalRevenue'] += $booking->payment;
                    }
                }
            }

            $parkingLots[] = $parkingLot;
        }

        // Return a response with the parking lots and their availability counts
        return response()->json([
            'message' => 'Success!',
            'data' => $parkingLots
        ]);
    }

    /**
     * @OA\Get(
     ** path="/api/dashboard/{userId}/revenue/{period}", tags={"DashBoard"}, summary="Statistics parking lot block, revenue,userBooking by number",
     * operationId="getRevenueDetails",
     *   @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *         description="id user manage parking lto lot",
     *         example=1000000,
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\Parameter(
     *         name="period",
     *         in="path",
     *         required=true,
     *         description="period day or week or month or year",
     *         example="day",
     *         @OA\Schema(type="string"),
     *     ),
     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/
    public function getRevenueDetails($userId, $period)
    {

        $now = Carbon::now();


        if ($period == 'day') {
            $end = $now->toDateTimeString();
            $start = $now->startOfMonth()->toDateTimeString();
            $groupBy = DB::raw('Date(bookDate)');
            $format = 'Y-m-d';
        } elseif ($period == 'week') {
            $end = $now->toDateString();
            $start = $now->startOfMonth()->toDateTimeString();
            // return $start."............".$end;
            $groupBy = DB::raw('WEEK(bookDate)');
            $format = 'W';
        } elseif ($period == 'month') {
            $end = $now->toDateTimeString();
            $start = $now->startOfYear()->toDateTimeString();
            $groupBy = DB::raw('DATE_FORMAT(bookDate, "%Y-%m")');
            $format = 'Y-m';
        } elseif ($period == 'year') {
            $end = $now->endOfYear()->format('Y-m-d');
            $start = $now->subYears(5)->format('Y-m-d');
            // return $start. ' .........'.$end;
            $groupBy = DB::raw('YEAR(bookDate)');
            $format = 'Y';
        } else {
            return response()->json([
                'message' => "Invalid period specified.",
                'data' => null
            ], 400);
        }
        // Get the parking lot ids for the given user
        $parkingLotIdsForUser = UserParkingLot::where('userId', $userId)->pluck('parkingId');

        // Get the block ids in the parking lots for the given user
        $blockIdsInParkingLots = Block::whereIn('parkingLotId', $parkingLotIdsForUser)->pluck('id');

        // Get the slot ids in the blocks for the given user
        $slotIdsInBlocks = ParkingSlot::whereIn('blockId', $blockIdsInParkingLots)->pluck('id');


        $bookings = Booking::whereIn('slotId', $slotIdsInBlocks)
            ->select(
                DB::raw("{$groupBy} as period"),
                DB::raw('SUM(payment) as total_sales'),
                DB::raw('COUNT(DISTINCT  userId) as total_users'),
                DB::raw('COUNT(DISTINCT bookDate) as total_bookings'),

            )
            ->whereBetween('bookDate', [$start, $end])
            ->groupBy($groupBy)
            ->get();



        if ($bookings->isEmpty()) {
            return response()->json([
                'message' => "No bookings data available for the specified period.",
                'data' => null
            ], 404);
        }

        $start = Carbon::parse($bookings->first()->period)->startOf($period);
        $end = Carbon::parse($bookings->last()->period)->endOf($period);
        $periods = $this->getPeriods($start, $end, $format, $period);
        $bookings = $this->fillMissingPeriods($periods, $bookings, $groupBy->getValue());

        $periodLabels = $bookings->pluck('period')->toArray();
        $salesTotals = $bookings->pluck('total_sales')->toArray();
        $uniqueUsers = $bookings->pluck('total_users')->toArray();
        foreach ($uniqueUsers as &$value) {
            if (is_null($value)) {
                $value = 0;
            }
        }
        $bookingCounts = $bookings->pluck('total_bookings')->toArray();
        foreach ($bookingCounts as &$value) {
            if (is_null($value)) {
                $value = 0;
            }
        }
        return response()->json([
            'message' => "Success!",
            'data' => [
                'periodLabels' => $periodLabels,
                'salesTotals' => $salesTotals,
                'uniqueUsers' => $uniqueUsers,
                'bookingCounts' => $bookingCounts,
            ]
        ], 200); // Return a JSON response with the sales data
    }


    private function getPeriods($start, $end, $format, $period)
    {
        $periods = [];
        $interval = CarbonInterval::day(); // Set interval to one day

        if ($period == 'day') {
            $currentMonth = Carbon::now()->month;
            $start = Carbon::createFromDate(null, $currentMonth, 1);
            $end = Carbon::now();
        } elseif ($period == 'year') {
            $now = Carbon::now();
            $end = $now->startOfYear()->format('Y');
            $start = $now->subYears(5)->format('Y');

            $start = Carbon::createFromDate($end, 1, 1);
            $end = Carbon::createFromDate($start, 12, 31);
        }
        $period = Carbon::parse($start); // Parse the start date into Carbon object
        while ($period <= $end) { // Loop through each day until the end date is reached
            $periods[] = $period->format($format); // Format the period according to the specified format and add it to the array
            $period = $period->addDays(1)->startOf('day'); // Move to the next day and start from the beginning of the day
        }
        return $periods; // Return the array of periods
    }

    private function fillMissingPeriods($periods, $sales, $format)
    {
        $missingPeriods = array_diff($periods, $sales->pluck('period')->toArray()); // Find the missing periods between the specified period array and the sales period array

        foreach ($missingPeriods as $missingPeriod) { // Loop through each missing period
            $sales->push((object) ['period' => $missingPeriod, 'total_sales' => 0]); // Push an object with the missing period and zero sales into the sales array
        }

        return $sales->sortBy('period'); // Sort the sales array by period and return it
    }
}
