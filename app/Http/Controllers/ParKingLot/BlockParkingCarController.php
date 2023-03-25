<?php

namespace App\Http\Controllers\ParKingLot;

use App\Http\Controllers\Controller;
use App\Models\Block;
use App\Models\Booking;
use App\Models\ParkingLot;
use App\Models\ParkingSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BlockParkingCarController extends Controller
{

    /**
     * @OA\Get(
     ** path="/api/parking-lot/{id}/slots", tags={"Block"}, 
     *  summary="get all slot in this block with detail status", operationId="getSlotStatusByBookingDateTime2",
     *   @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *          example=1000000,
     *         description="ID of the parking lot to retrieve",
     *         @OA\Schema(type="integer")
     *     ),
     *      @OA\Parameter(name="start_datetime",in="query",required=true,example="2023-03-01 14:30:00", @OA\Schema( type="string" )),
     *      @OA\Parameter(name="end_datetime",in="query",required=true,example="2023-04-01 14:30:00", @OA\Schema( type="string" )),
     * 
     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/
    public function getSlotStatusByBookingDateTime(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'start_datetime' => 'required|date_format:Y-m-d H:i:s',
            'end_datetime' => 'required|date_format:Y-m-d H:i:s|after:start_datetime',
        ]);
        if ($validator->fails()) {
            return $validator->errors()->toArray();
        }
        $dateData = $validator->validated();
        $startDate = $dateData["start_datetime"];
        $endDate = $dateData["end_datetime"];
        //  give block of parkinglot id
        $blocks = Block::where('parkingLotId', $id)->get();

        // create array to get status slot
        $status = array();

        // foreach block
        foreach ($blocks as $block) {

            // get all slot of block
            $slots = $block->slots;

            // check blcok is null or not
            if (count($slots) === 0) {
                continue;
            }
            // create array to get detail status slot
            $blockStatus = array();

            // foreach slot
            foreach ($slots as $slot) {

                // get all booking in this slot
                $bookings = Booking::where('slotId', $slot->id)
                    ->where('bookDate', '<=', $endDate)
                    ->where('returnDate', '>=', $startDate)
                    ->get();

                // if slot >0 it mean slot have booking in date time
                
                if (count($bookings) > 0) {
                    $blockStatus[] = array(
                        'idSlot' => $slot->id,
                        'slotName' => $slot->slotName,
                        'status' => 0
                    );
                } else {
                    $blockStatus[] = array(
                        'idSlot' => $slot->id,
                        'slotName' => $slot->slotName,
                        'status' => 1
                    );
                }
            }
            $status[] = array(
                'block_id' => $block->id,
                'carType' => $block->carType,
                'price' => $block->price,
                'desc' => $block->desc,
                'status' => $blockStatus
            );
        }

        return
            response()->json([
                'data' => $status,
            ]);
    }
}
