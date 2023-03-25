<?php

namespace App\Http\Controllers\ParKingLot\Owner;

use App\Http\Controllers\Controller;
use App\Models\Block;
use App\Models\Booking;
use App\Models\ParkingLot;
use App\Models\ParkingSlot;
use App\Rules\UniqueBlockNameInParkingLot;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BlockController extends Controller
{
    /**
     * @OA\Get(
     ** path="/api/parking-lot/{idParking}/blocks", tags={"Block"}, summary="get all block with id parking lot",
     * operationId="getAllBlock",
     *   @OA\Parameter(
     *         name="idParking",
     *         in="path",
     *         required=true,
     *         description="ID of parking lot",
     *         example=1000000,
     *         @OA\Schema(type="integer"),
     *     ),
     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/
    public function getAllBlock($idParking)
    {
        $data = Block::where('parkingLotId', $idParking)
            ->withCount('slots')
            ->get();

        return response()->json([
            'message' => 'Successfully',
            'blocks' => $data,
        ], 200);
    }
    /**
     * @OA\Post(
     ** path="/api/parking-lot/block/create", tags={"Block"}, 
     *  summary="create block ,slot", operationId="createBlockSlot",
     *      @OA\Parameter(name="parkingLotId",in="query",required=true,example="1000000", @OA\Schema( type="integer" )),
     *      @OA\Parameter(name="nameBlock",in="query",required=true,example="Khu a", @OA\Schema( type="string" )),
     *      @OA\Parameter(name="carType",in="query",required=true,example="4-16SLOT", @OA\Schema( type="string" )),
     *      @OA\Parameter(name="desc",in="query",required=true,example="an toan cao", @OA\Schema( type="string" )),
     *      @OA\Parameter(name="price",in="query",required=true,example=14000, @OA\Schema( type="integer" )),
     *      @OA\Parameter(name="numberOfSlot",in="query",required=true,example=50, @OA\Schema( type="integer" )),
     * 
     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/
    public function createBlockSlot(Request $request)
    {
        $parkingLotId = $request->input("parkingLotId");
        $parkingLot = ParkingLot::findOrFail($parkingLotId);
        $validator = Validator::make($request->all(), [
            "parkingLotId" => 'required',
            'nameBlock' => [
                'required',
                'string',
                new UniqueBlockNameInParkingLot($parkingLot)
            ],
            "carType" => 'required|in:4-16SLOT,16-34SLOT',
            "desc" => 'string',
            "price" => 'required|digits_between:1,99999999999999',
            "numberOfSlot" => 'required|integer|min:1|max:50',
        ]);
        if ($validator->fails()) {
            return $validator->errors()->toArray();
        }
        $dateData = $validator->validated();
        $block = new Block();
        $block->parkingLotId = $dateData["parkingLotId"];
        $block->nameBlock = $dateData["nameBlock"];
        $block->desc = $dateData["desc"];
        $block->carType = $dateData["carType"];
        $block->price = $dateData["price"];
        $block->save();

        $numberOfSlot = $dateData["numberOfSlot"];
        $blockNameLastChar = strtoupper(substr($block->nameBlock, -1));

        for ($i = 1; $i <= $numberOfSlot; $i++) {
            $slot = new ParkingSlot();
            $slot->slotName = $blockNameLastChar . $i;
            $block->slots()->save($slot);
        }
        return response()->json([
            'message' => 'Block created successfully',
            'block' => $block,
        ], 201);
    }

    /**
     * @OA\Get(
     ** path="/api/parking-lot/block/{id}", tags={"Block"}, summary="show detail info of block",
     * operationId="showDetailBlock",
     *   @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of parking lot",
     *         example=1000000,
     *         @OA\Schema(type="integer"),
     *     ),
     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/
    public function showDetailBlock($id)
    {
        $block = Block::findOrFail($id);
        return response()->json([
            'message' => 'Success!',
            'data' => $block
        ], 200);
    }
    /**
     * Update the user's profile.
     *
     * @OA\put(
     *     path="/api/parking-lot/block/{id}/update",
     *     summary="Update block",
     *     tags={"Block"},
     *     operationId="updateBlock",
     *     @OA\Parameter(
     *         name="id",
     *         description="Id of block parking lot",
     *         in="path",
     *         example=1000000,
     *         required=true,
     *     ),
     *     @OA\RequestBody(
     *          required=true,
     *          description="block object that needs to be updated.",
     *          @OA\JsonContent(
     *              @OA\Property(property="nameBlock", type="string"),
     *              @OA\Property(property="price", type="number", format="float",example=2000000),
     *              @OA\Property(property="desc", type="string",example="16-34SLOT"),
     *              @OA\Property(property="carType", type="string", example="16-34SLOT")
     *          )
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated successfully"
     *     ),
     *      security={ {"passport":{}}}
     * 
     * )
     */
    public function updateBlock(Request $request, $id)
    {
        $block = Block::findOrFail($id);
        $parkingLot = $block->parkingLot;
      
        $validatedData = Validator::make($request->all(), [
            "carType" => 'nullable|in:4-16SLOT,16-34SLOT',
            "nameBlock" => [
                'nullable',
                'string',
                new UniqueBlockNameInParkingLot($parkingLot)
            ],
            "desc" => 'nullable|string',
            "price" => 'nullable|digits_between:1,99999999999999',
        ]);
        if ($validatedData->fails()) {
            return $validatedData->errors()->toArray();
        }
        if (isset($validatedData["nameBlock"])) {
            $block->nameBlock = $validatedData["nameBlock"];
        }
        if (isset($validatedData["desc"])) {
            $block->desc = $validatedData["desc"];
        }
        if (isset($validatedData["carType"])) {
            $block->carType = $validatedData["carType"];
        }
        if (isset($validatedData["price"])) {
            $block->price = $validatedData["price"];
        }
        $block->save();
        return response()->json([
            'message' => 'Block updated successfully',
            'data' => $block
        ], 200);
    }
    /**
     * Update the user's profile.
     *
     * @OA\Delete(
     *     path="/api/parking-lot/block/{id}/delete",
     *     summary="Delete block",
     *     tags={"Block"},
     *     operationId="deleteBlock",
     *     @OA\Parameter(
     *         name="id",
     *         description="Id of block parking lot",
     *         in="path",
     *         example=1000041,
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64",
     *         )
     *     ),@OA\Response(
     *         response=200,
     *         description="Profile updated successfully"
     *     ),
     *      security={ {"passport":{}}}
     * 
     * )
     */
    public function deleteBlock($id)
    {
        $block = Block::findOrFail($id);

        // Check if there are any active bookings for the block's slots
        $activeBookings = Booking::whereIn('slotId', $block->slots()->pluck('id')->toArray())
            ->where('bookDate', '<=', Carbon::now())
            ->where('returnDate', '>=', Carbon::now())
            ->count();
            
        if ($activeBookings > 0) {
            return response()->json(['message' => 'Unable to delete block as it is currently in use.'], 409);
        }
    
        $block->slots()->delete();
        $block->delete();
        return response()->json(['message' => 'Block and all slots deleted successfully'],204);
    }
}
