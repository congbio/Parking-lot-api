<?php

namespace App\Http\Controllers\ParKingLot;

use App\Events\WishlistEvent;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ParkingLot;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WishlistController extends Controller
{
    /**
     * @OA\Get(
     ** path="/api/user/{userId}/wishlist", tags={"Wishlist"}, 
     *  summary="get all parkingLot have wishlist", operationId="getWishlist",
     *   @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *          example=1000000,
     *         description="id user",
     *         @OA\Schema(type="integer")
     *     ),
     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/
    public function getWishlist($userId)
    {

        try {
            // Get user by ID
            $parkingLotIds = Wishlist::select('parkingLotId')
                ->where('userId', $userId)
                ->get()
                ->pluck('parkingLotId');
            $bookingsByParkingLot = ParkingLot::select(
                'parking_lots.id as parkingLotId',
                'parking_lots.nameParkingLot as parking_lot_name',
                'parking_lots.address',
                DB::raw('count(distinct concat(bookings.bookDate, bookings.returnDate)) as booking_count')
            )
                ->leftJoin('blocks', 'blocks.parkingLotId', '=', 'parking_lots.id')
                ->leftJoin('parking_slots', 'parking_slots.blockId', '=', 'blocks.id')
                ->leftJoin('bookings', function ($join) use ($userId) {
                    $join->on('bookings.slotId', '=', 'parking_slots.id')
                        ->where('bookings.userId', '=', $userId);
                })
                ->whereIn('parking_lots.id', $parkingLotIds)
                ->groupBy('parking_lots.id')
                ->orderBy('booking_count', 'desc') 
                ->get();
            return response()->json($bookingsByParkingLot, 200);
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }



    /**
     * @OA\Post(
     ** path="/api/user/wishlist/add", tags={"Wishlist"}, 
     *  summary="add wishlist or Delete wishlist", operationId="addWishList",
     *  @OA\Parameter(name="userId",in="query",required=true,example=1000000, @OA\Schema( type="integer" )),
     *  @OA\Parameter(name="parkingLotId",in="query",required=true,example=1000000, @OA\Schema( type="integer" )),

     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/
    public function addWishList(Request $request)
    {
        $validatedData = $request->validate([
            'userId' => 'required|integer',
            'parkingLotId' => 'required|integer',
        ]);

        $userId = $validatedData['userId'];
        $parkingLotId = $validatedData['parkingLotId'];

        // Check if the user has already added the parking lot to their wishlist
        $existingWishlist = Wishlist::where('userId', $userId)
            ->where('parkingLotId', $parkingLotId)
            ->first();

        if ($existingWishlist) {
            $existingWishlist->delete();
            return response()->json([
                'message' => 'Delete wishlist success!'
            ], 200);
        }

        $ownerId = ParkingLot::find($parkingLotId)->user->id;
        $nameParkingLot = ParkingLot::find($parkingLotId)->nameParkingLot;
        $user = User::find($userId, ['id', 'fullName', 'avatar']);
        try {

            event(new WishlistEvent($user, $ownerId, $nameParkingLot));
        } catch (\Throwable $th) {
            Log::error('Error sending wishlist event: ' . $th->getMessage());
        }
        $wishlist = Wishlist::create([
            'userId' => $userId,
            'parkingLotId' => $parkingLotId
        ]);


        return response()->json([
            'message' => 'Wishlist created successfully',
            'data' => $wishlist
        ]);
    }
}
