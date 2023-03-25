<?php

namespace App\Http\Controllers\ParKingLot;

use App\Events\BookingEvent;
use App\Events\CancelBookingEvent;
use App\Events\NotificationBooking;
use App\Events\QrEvent;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ParkingLot;
use App\Models\ParkingSlot;
use App\Models\User;
use App\Notifications\BookingNotification;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    /**
     * @OA\Get(
     ** path="/api/booking/slots", tags={"Booking"}, 
     *  summary="show detail booking", operationId="getSlotsByIdWithBlockName",
     *     @OA\Parameter(
     *         name="ids",
     *         in="query",
     *         example="[100000000,100000001]",
     *         description="An array of integers.",
     *         required=true,
     *         @OA\Schema(
     *             type="array",
     *             @OA\Items(
     *                 type="integer",
     *              
     *              )
     *         )
     *     ),
     *  @OA\Parameter(name="start_datetime",in="query",required=true,example="2023-03-01 14:30:00", @OA\Schema( type="string" )),
     *  @OA\Parameter(name="end_datetime",in="query",required=true,example="2023-04-01 14:30:00", @OA\Schema( type="string" )),
     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/
    public function getSlotsByIdWithBlockName(Request $request)
    {
        // get all slots with the specified IDs
        // Convert $slotIds to an array if it's a string
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'start_datetime' => 'required|date_format:Y-m-d H:i:s',
            'end_datetime' => 'required|date_format:Y-m-d H:i:s|after:start_datetime',
        ]);
        if ($validator->fails()) {
            return $validator->errors()->toArray();
        }
        $dateData = $validator->validated();
        $ids = $dateData['ids'];
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }
        $slots = ParkingSlot::whereIn('id', $ids)->with('block')->get();
        // create an array to store the output slots
        $output = [];
        $total = 0;
        // loop through the input slot IDs
        foreach ($ids as $slotId) {
            // find the slot object with the current ID in the $slots array
            $slot = $slots->firstWhere('id', $slotId);
            // if a matching slot object was found, add it to the output array
            if ($slot) {
                $block = $slot->block;
                $price = $block->price;
                $startDatetime = Carbon::parse($dateData['start_datetime']);
                $endDatetime = Carbon::parse($dateData['end_datetime']);
                $durationHours = $endDatetime->diffInHours($startDatetime);
                $total_price = 0;
                $total_price = $durationHours * $price;
                // Get difference in hours
                switch (true) {
                    case ($durationHours < 24):
                        $total_price -=  $durationHours * $price * 5 / 100;
                        break;
                    case ($durationHours >= 24 && $durationHours < 24 * 7):
                        $total_price -=  $durationHours * $price * 10 / 100;
                        break;
                    case ($durationHours >= 24 * 7 && $durationHours < 24 * 30):
                        $total_price -=  $durationHours * $price * 20 / 100;
                        break;

                    case ($durationHours >= 24 * 30 && $durationHours < 24 * 365):
                        $total_price -=  $durationHours * $price * 30 / 100;
                        break;

                    case ($durationHours >= 24 * 365):
                        $total_price -=  $durationHours * $price * 40 / 100;
                        break;
                }

                $output['slots'][] = [
                    'slotId' => $slot->id,
                    'blockName' => $block->nameBlock,
                    'blockDesc' => $block->desc,
                    'carType' => $block->carType,
                    'price' => $price,
                    'durationHours' => $durationHours,
                    'total_price' => $total_price,
                ];
                $total += $total_price;
            }
        }
        $output['total'] = $total;
        $output['date'] = [
            "start_datetime" => $dateData['start_datetime'],
            "end_datetime" => $dateData['end_datetime'],

        ];
        return $output;
    }
    /**
     * @OA\POST(
     ** path="/api/booking", tags={"Booking"}, summary="booking now",
     * operationId="bookParkingLot",
     *     @OA\Parameter(
     *         name="slot_ids[]",
     *         in="query",
     *         required=true,
     *         description="Array of booking IDs",
     *         @OA\Schema(type="array", @OA\Items(type="integer"))
     *     ),
     *  @OA\Parameter(name="user_id",in="query",required=true,example=1000000, @OA\Schema( type="integer" )),
     *      @OA\Parameter(name="start_datetime",in="query",required=true,example="2023-01-27 14:50:00", @OA\Schema( type="string" )),
     *      @OA\Parameter(name="end_datetime",in="query",required=true,example="2023-02-01 14:50:00", @OA\Schema( type="string" )),
     *    @OA\Parameter(
     *         name="licensePlate[]",
     *         in="query",
     *         required=true,
     *         description="Array of booking IDs",
     *         @OA\Schema(type="array", @OA\Items(type="string"))
     *     ),
     * @OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/
    public function bookParkingLot(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'slot_ids' => 'required|array',
            'user_id' =>   'required',
            'licensePlate' => 'required|array',
            'licensePlate.*' => 'regex:/^[0-9]{2}[A-Z]{1,2}[-]?[0-9A-Z]{4,5}$/',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
        ]);
        if ($validator->fails()) {
            return $validator->errors()->toArray();
        }
        $dateData = $validator->validated();
        $slotIds = $dateData['slot_ids'];


        $idSpaceOwner = ParkingSlot::find($slotIds[0])->block->parkingLot->user ?: null;
        $userId = $dateData['user_id'];
        $licensePlate = $dateData['licensePlate'];
        $startDatetime = $dateData['start_datetime'];
        $endDatetime = $dateData['end_datetime'];

        $bookedSlots = Booking::where(function ($query) use ($startDatetime, $endDatetime) {
            $query->where('bookDate', '<', $endDatetime)
                ->where('returnDate', '>', $startDatetime);
        })
            ->whereIn('slotId', $slotIds)
            ->pluck('slotId')
            ->toArray();
        $emptySlots = array_diff($slotIds, $bookedSlots);
        // If all requested slots are empty, create a new booking
        if (count($emptySlots) === count($slotIds)) {
            $number = 0;
            $output = [];
            $total = 0;
            $prices = ParkingSlot::whereIn('parking_slots.id', $emptySlots)
                ->select('blocks.price')
                ->join('blocks', 'blocks.id', '=', 'parking_slots.blockId')
                ->get()
                ->pluck('price')
                ->toArray();

            $startDatetime = Carbon::parse($dateData['start_datetime']);
            $endDatetime = Carbon::parse($dateData['end_datetime']);
            $durationHours = $endDatetime->diffInHours($startDatetime);
            // Get difference in hours
            $bookingIds = [];
            foreach ($emptySlots as $slot) {
                $total_price = $prices[$number] * $durationHours;
                $discount = 0;
                switch (true) {
                    case ($durationHours < 24):
                        $discount = 0;
                        break;
                    case ($durationHours >= 24 && $durationHours < 24 * 7):
                        $discount = 10;
                        break;
                    case ($durationHours >= 24 * 7 && $durationHours < 24 * 30):
                        $discount = 20;
                        break;
                    case ($durationHours >= 24 * 30 && $durationHours < 24 * 365):
                        $discount = 30;
                        break;
                    case ($durationHours >= 24 * 365):
                        $discount = 40;
                        break;
                    default:
                        $discount = 0;
                        break;
                }

                $total_price -= $durationHours * $prices[$number] * $discount / 100;

                $booking = new Booking();
                $booking->licensePlate = $licensePlate[$number];
                $booking->userId = $userId;
                $booking->slotId = $slot;
                $booking->payment = $total_price;
                $booking->bookDate = $startDatetime;
                $booking->returnDate = $endDatetime;
                $booking->save();
                $bookingIds[] = $booking->id;
                $number += 1;
                $output['booking'][] = $booking;
                $total += $total_price;
            }
            $output["total"] = $total;
            $output["idBookings"] = $bookingIds;

            $parkingInfo =ParkingSlot::find($slotIds[0])->block->parkingLot;
            $user = User::find($userId);
            $owner = User::find($idSpaceOwner->id);
            $userNotify = [$user, $owner];
            $idInfo = [ $owner->id,$user->id];
            $outputNotify=$output;
            $outputNotify['inFoParking']=$parkingInfo;
            $title = [ " booked parking lot {$parkingInfo->nameParkingLot}","You booked successfully!"];
            $messageSave=[$user->fullName, " booked parking lot {$parkingInfo->nameParkingLot}"];
            try {
                for ($i=0; $i < sizeof($userNotify); $i++) { 
                    event(new BookingEvent($idInfo[$i],$outputNotify, $userNotify[$i],$title[$i],$messageSave));
                }
               
            } catch (\Throwable $th) {
                Log::error('Error sending BookingEvent: ' . $th->getMessage());
            }
            $output["idSpaceOwner"] = $idSpaceOwner->id;
            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully',
                'data' => $output,
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => 'One or more slots are already booked during the requested time period',
            'data' => $bookedSlots,

        ]);
    }

    /**
     * @OA\Get(
     ** path="/api/booking/{userId}/history", tags={"History"}, summary="get history booking of user",
     * operationId="historyBookingSummary",
     *   @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *         description="id user booking",
     *         example=1000000,
     *         @OA\Schema(type="integer"),
     *     ),
     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/
    function historyBookingSummary($userId)
    {
        $bookings = Booking::select(
            'bookings.id',
            'bookings.bookDate',
            'bookings.returnDate',
            'bookings.payment',
            'parking_lots.nameParkingLot as parking_lot_name',
            'parking_lots.address',
            'parking_lots.id as idParkingLot',
            'user_parking_lots.userId',
            'bookings.created_at',
        )
            ->leftJoin('parking_slots', 'bookings.slotId', '=', 'parking_slots.id')
            ->leftJoin('blocks', 'parking_slots.blockId', '=', 'blocks.id')
            ->leftJoin('parking_lots', 'blocks.parkingLotId', '=', 'parking_lots.id')
            ->join('user_parking_lots', 'user_parking_lots.parkingId', '=', 'parking_lots.id')
            ->where('bookings.userId', '=', $userId)
            ->orderBy('bookings.created_at', 'desc')
            ->get()
            ->groupBy('bookDate')->take(10);

        $response = [];

        foreach ($bookings as $date => $bookingsByDate) {
            $totalPayment = $bookingsByDate->sum('payment');
            $parkingLotName = $bookingsByDate->isNotEmpty() ? $bookingsByDate->first()->parking_lot_name : null;
            $bookingIds = $bookingsByDate->pluck('id');
            $bookDate = $bookingsByDate[0]['bookDate'];

            $returnDate = $bookingsByDate[0]['returnDate'];
            $idParking = $bookingsByDate[0]['idParkingLot'];
            $now = Carbon::now();
            $statusBooking = 'Completed';
            if ($bookDate >= $now->toDateTimeString()) {
                $statusBooking = "Pending";
            }
            if ($bookDate >= $returnDate) {
                $statusBooking = 'Cancelled';
            }
            if ($bookDate <= $now && $now < $returnDate) {
                $statusBooking = "parked";
            }
            $address = $bookingsByDate[0]['address'];
            $idSpaceOwner = $bookingsByDate[0]['userId'];
            $created_at = $bookingsByDate[0]['created_at'];
            $response[] = [
                'bookDate' => $bookDate,
                'returnDate' => $returnDate,
                'statusBooking' => $statusBooking,
                'address' => $address,
                'total_payment' => $totalPayment,
                'parking_lot_name' => $parkingLotName,
                'booking_count' => $bookingsByDate->count(),
                'booking_ids' => $bookingIds,
                'idSpaceOwner' => $idSpaceOwner ?: null,
                'idParking' => $idParking,
                'created_at' => $created_at ?: null,
            ];
        }

        return response()->json([
            'message' => 'Booking history summary',
            'data' => $response,
        ], 200);
    }
    /**
     * @OA\Get(
     ** path="/api/booking/history/details", tags={"History"}, summary="get history detail booking of user",
     * operationId="historyBookingDetail",
     *     @OA\Parameter(
     *         name="bookingIds[]",
     *         in="query",
     *         required=true,
     *         description="Array of booking IDs",
     *         @OA\Schema(type="array", @OA\Items(type="integer"))
     *     ),
     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/
    public function historyBookingDetail(Request $request)
    {
        $bookingIds = $request->input('bookingIds');
        if (!is_array($bookingIds)) {
            return response()->json([
                'message' => 'Invalid input: bookingIds must be an array',
            ], 400);
        }

        $bookings = Booking::select(
            'bookings.id as booking_id',
            'bookings.bookDate',
            'bookings.licensePlate',
            'bookings.payment',
            'parking_slots.slotName',
            'blocks.nameBlock',
            'blocks.carType',
            'parking_lots.nameParkingLot as parking_lot_name'
        )
            ->leftJoin('parking_slots', 'bookings.slotId', '=', 'parking_slots.id')
            ->leftJoin('blocks', 'parking_slots.blockId', '=', 'blocks.id')
            ->leftJoin('parking_lots', 'blocks.parkingLotId', '=', 'parking_lots.id')
            ->whereIn('bookings.id', $bookingIds)
            ->orderBy('bookings.bookDate', 'desc')
            ->get();



        return response()->json([
            'message' => 'Booking history summary',
            'data' => [

                'bookings' => $bookings,
            ],
        ], 200);
    }
    /**
     * @OA\Get(
     ** path="/api/booking/show", tags={"QrCode"}, 
     *  summary="Scan QRcode to get detail booking", operationId="getDetailQRcode",
     *   @OA\Parameter(
     *         name="bookingIds[]",
     *         in="query",
     *         required=true,
     *         description="Array of booking IDs",
     *         @OA\Schema(type="array", @OA\Items(type="integer"))
     *     ),
     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/

    public function getDetailQRcode(Request $request)
    {

        $validator = validator::make($request->all(), [
            'bookingIds' => 'required|array',
            'bookingIds.*' => 'required|integer',

        ]);
        if ($validator->fails()) {
            return $validator->errors()->toArray();
        }

        $bookingIds = $request->input('bookingIds');

        $bookings = Booking::select(
            'bookings.id as booking_id',
            'bookings.bookDate',
            'bookings.returnDate',
            'bookings.licensePlate',
            'bookings.payment',
            'parking_slots.slotName',
            'blocks.nameBlock',
            'blocks.carType',
            'parking_lots.nameParkingLot as parking_lot_name',
            'parking_lots.address',
            'user_parking_lots.userId as idSpaceOwner',
            'users.fullName'
        )

            ->leftJoin('parking_slots', 'bookings.slotId', '=', 'parking_slots.id')
            ->leftJoin('blocks', 'parking_slots.blockId', '=', 'blocks.id')
            ->leftJoin('parking_lots', 'blocks.parkingLotId', '=', 'parking_lots.id')
            ->join('user_parking_lots', 'user_parking_lots.parkingId', '=', 'parking_lots.id')
            ->join('users', 'bookings.userId', '=', 'users.id')
            ->whereIn('bookings.id', $bookingIds)
            ->orderBy('bookings.bookDate', 'desc')
            ->get();

        $totalPayment = $bookings->sum('payment');


        return response()->json([
            'message' => 'Booking history summary',
            'data' => [

                'bookings' => $bookings,
                'totalPayment' => $totalPayment,
            ],
        ], 200);
    }

    /**
     * @OA\Patch(
     ** path="/api/booking/update", tags={"QrCode"}, summary="Qr Code to confirm complete finish booking",
     * operationId="completeBooking",
     *     @OA\Parameter(
     *         name="bookingIds[]",
     *         in="query",
     *         required=true,
     *         description="Array of booking IDs",
     *         @OA\Schema(type="array", @OA\Items(type="integer"))
     *     ),
     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/
    public function completeBooking(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bookingIds' => 'required|array',
            'bookingIds.*' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return $validator->errors()->toArray();
        }

        $dateData = $validator->validated();
        $bookingIds = $dateData['bookingIds'];
        $now = Carbon::now();

        $updatedBookings = [];
        foreach ($bookingIds as &$value) {
            $booking = Booking::findOrFail($value);

            if (Carbon::parse($booking->returnDate)->lt($now)) {
                // The booking has expired
                return response()->json([
                    'error' => 'Booking has expired!',
                ], 400);
            }

            if (Carbon::parse($booking->bookDate)->gt($now)) {
                // The booking has not yet started
                return response()->json([
                    'error' => 'Booking has not yet started!',
                ], 400);
            }

            // Update the booking with the return date
            $booking->returnDate = $now;
            $booking->save();

            // Add the updated booking to the array of updated bookings
            $updatedBookings[] = $booking;
        }
        $totalPayment = 0;
        foreach ($updatedBookings as $booking) {
            $totalPayment += $booking['payment'];
        }
        $userInfo = User::find($updatedBookings[0]['userId']);
        $parkingInfo = $parkingInfo =ParkingSlot::find($updatedBookings[0]['slotId'])->block->parkingLot;
        $owner=$parkingInfo->user;
        $output = [
            'totalPrice' => $totalPayment,
            'bookDate' => $updatedBookings[0]['bookDate'],
            'returnDate' => $now->toDateTimeString(),
            'userName' => $userInfo->fullName,
            'parkingInfo' => $parkingInfo,

        ];
        
        try {
            event(new QrEvent($userInfo,$output,$owner,$parkingInfo->nameParkingLot));
          
        } catch (\Throwable $th) {
            Log::error('Error QRcode event: ' . $th->getMessage());
        }
        return response()->json([
            'message' => 'Completed this booking!',
            'updatedBookings' => $output,
        ], 200);
    }

    /**
     * @OA\Put(
     ** path="/api/booking/cancel", tags={"Booking"}, summary="cancel booking",
     * operationId="cancelBooking",
     *   @OA\Parameter(
     *         name="bookingIds[]",
     *         in="query",
     *         required=true,
     *         description="Array of booking IDs",
     *         @OA\Schema(type="array", @OA\Items(type="integer"))
     *     ),
     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/
    public function cancelBooking(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bookingIds' => 'required|array',
            'bookingIds.*' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $bookingIds = $request->input('bookingIds');
        DB::beginTransaction();

        try {
            $output=[];
            foreach ($bookingIds as $bookingId) {
                $booking = Booking::find($bookingId);

                if (!$booking) {
                    throw new Exception("Booking with ID $bookingId not found");
                }

                if ($booking->bookDate <= Carbon::now()->toDateString()) {
                    throw new Exception("Cannot cancel booking with ID $bookingId as it has already started");
                }
                $output[]=$booking;
                $booking->returnDate = Carbon::now()->toDateString();
                $booking->save();
            }

            DB::commit();
            try {
                $booking = Booking::find($bookingIds[0]);
                $parkingLotInfo = ParkingSlot::findOrFail($booking->slotId)->block->parkingLot;
                $user= User::findOrFail($booking->userId);
                $owner= $parkingLotInfo->user;

                event(new CancelBookingEvent($user,$owner,$parkingLotInfo,$output));
            } catch (\Throwable $th) {
                Log::error('Error sending comment event: ' . $th->getMessage());
            }
            return response()->json(['message' => 'Bookings cancelled successfully']);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
