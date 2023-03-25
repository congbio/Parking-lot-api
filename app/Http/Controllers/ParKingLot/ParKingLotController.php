<?php

namespace App\Http\Controllers\ParKingLot;

use App\Http\Controllers\Clound\CloudinaryStorage;
use App\Http\Controllers\Controller;
use App\Models\Block;
use App\Models\Booking;
use App\Models\ParkingLot;
use App\Models\ParkingSlot;
use App\Models\User;
use App\Models\UserParkingLot;
use App\Services\Interfaces\IParKingLotService;
use Carbon\Carbon;
use GrahamCampbell\ResultType\Success;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;

class ParKingLotController extends Controller
{
    public function __construct(private readonly IParKingLotService $parKingLot)
    {
    }
    /**
     * @OA\Get(
     ** path="/api/parking-lot", tags={"Parking Lot"}, 
     *  summary="admin get all parking lot", operationId="index",
     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/

    public function index()
    {
        return $this->parKingLot->getAllParkingLot(true);
    }
    /**
     * @OA\Get(
     ** path="/api/parking-lot/{idParking}", tags={"Parking Lot"}, 
     *  summary="detail parking lot with id", operationId="showParkingLot",
     *   @OA\Parameter(
     *         name="idParking",
     *         in="path",
     *         required=true,
     *          example=1000000,
     *         description="ID of the parking lot to retrieve",
     *         @OA\Schema(type="integer")
     *     ),
     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/

    public function showParkingLot($id)
    {
        try {
            $parking_lot = ParkingLot::findOrFail($id);
            $response_data = $parking_lot->toArray(); // convert the model to an array
            $response_data['images'] = json_decode($parking_lot->images); // add the images attribute to the response array

            return response()->json($response_data, Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Parking lot not found'], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @OA\Get(
     ** path="/api/parking-lot/{id}/info/{userId}", tags={"Parking Lot"}, 
     *  summary="detail parking lot with id", operationId="getInfoParkingLot",
     *   @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *          example=1000000,
     *         description="ID of the parking lot to retrieve",
     *         @OA\Schema(type="integer")
     *     ), @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *          example=1000000,
     *         description="IDuser to see status wishlist",
     *         @OA\Schema(type="integer")
     *     ),
     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/
    public function getInfoParkingLot($id, $userId)
    {
        // Check if the parking lot exists
        $parkingLot = ParkingLot::find($id);
        if (!$parkingLot) {
            return response()->json(['error' => 'Parking lot not found'], 404);
        }

        $wishlistId = DB::table('wishlists')
            ->join('parking_lots', 'wishlists.parkingLotId', '=', 'parking_lots.id')
            ->where('wishlists.userId', '=', $userId)
            ->where('wishlists.parkingLotId', '=', $id)
            ->value('wishlists.id');

        $arrayPrice = ParkingLot::find($id)->blocks()->orderBy('price')->pluck('price')->toArray();
        $avgPrice = intval(array_sum($arrayPrice) / sizeof($arrayPrice));

        $data = [
            'id' => $parkingLot->id,
            'nameParkingLot' => $parkingLot->nameParkingLot,
            'address' => $parkingLot->address,
            'address_latitude' => $parkingLot->address_latitude,
            'address_longitude' => $parkingLot->address_longitude,
            'openTime' => $parkingLot->openTime,
            'endTime' => $parkingLot->endTime,
            'desc' => $parkingLot->desc,
            'statusWishlist' => ($wishlistId) ? 1 : 0,
            'images' => json_decode($parkingLot->images),
            'avgPrice' => $avgPrice ?? null,

        ];

        return response()->json([$data], 200);
    }

    /**
     * @OA\Get(
     ** path="/api/parking-lot/{id}/info/comment", tags={"Parking Lot"}, 
     *  summary="detail comment of parking lot", operationId="showCommentOfParking",
     *   @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *          example=1000000,
     *         description="ID of the parking lot to retrieve",
     *         @OA\Schema(type="integer")
     *     ),
     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/
    public function showCommentOfParking($id)
    {
        $data = ParkingLot::join('comments', 'parking_lots.id', '=', 'comments.parkingId')
            ->join('users', 'users.id', '=', 'comments.userId')->where('parking_lots.id', $id)
            ->orderBy('created_at', 'DESC')
            ->get(['comments.*', 'users.fullName', 'users.avatar']);
        return $data;
    }
    /**
     * @OA\Get(
     ** path="/api/parking-lot/location", tags={"Parking Lot"}, 
     *  summary="show location near user ", operationId="showParkingLotNearLocation",
     *  @OA\Parameter(name="latitude",in="query",required=true,example=16.060832, @OA\Schema( type="string" )),
     *  @OA\Parameter(name="longitude",in="query",required=true,example=108.24149, @OA\Schema( type="string" )),
     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/
    public function showParkingLotNearLocation(Request $request)
    {
        $validatedData = $request->validate([
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        $lat = $request->latitude;
        $lon = $request->longitude;

        $data = DB::table("parking_lots")
            ->leftJoin('blocks', 'parking_lots.id', '=', 'blocks.parkingLotId')
            ->select(
                "parking_lots.*",
                DB::raw("6371 * acos(cos(radians(" . $lat . "))
            * cos(radians(parking_lots.address_latitude))
            * cos(radians(parking_lots.address_longitude) - radians(" . $lon . "))
            + sin(radians(" . $lat . "))
            * sin(radians(parking_lots.address_latitude))) AS distance")
            )
            ->having('distance', '<', 1.5)
            ->groupBy('parking_lots.id')
            ->orderBy('distance', 'asc')
            ->whereNotNull('blocks.id')
            ->get();

        foreach ($data as $parking_lot) {
            $parking_lot->images = json_decode($parking_lot->images);
        }

        return $data;
    }

    /**
     * @OA\Post(
     *     path="/api/parking-lot/create",
     *     tags={"Parking Lot"},
     *     summary="Create a new parking lot",
     *     description="Create a new parking lot with the specified details",
     *     operationId="createParkingLot",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="userId",
     *                     type="integer",
     *                     example=1000000
     *                 ),
     *                 @OA\Property(
     *                     property="images[]",
     *                     type="array",
     *                     @OA\Items(
     *                         type="file"
     *                     ),
     *                     description="Array of images"
     *                 ),
     *                 @OA\Property(
     *                     property="openTime",
     *                     type="string",
     *                     format="time",
     *                     example="20:08"
     *                 ),
     *                 @OA\Property(
     *                     property="endTime",
     *                     type="string",
     *                     format="time",
     *                     example="21:08"
     *                 ),
     *                 @OA\Property(
     *                     property="nameParkingLot",
     *                     type="string",
     *                     example="Parking Lot Cong"
     *                 ),
     *                 @OA\Property(
     *                     property="address_latitude",
     *                     type="string",
     *                     example="16.060832"
     *                 ),
     *                 @OA\Property(
     *                     property="address_longitude",
     *                     type="string",
     *                     example="108.241491"
     *                 ),
     *                 @OA\Property(
     *                     property="address",
     *                     type="string",
     *                     example="101B Le Huu Tra"
     *                 ),
     *                 @OA\Property(
     *                     property="desc",
     *                     type="string",
     *                     example="gia ra dat an ninh coa"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="Parking lot created successfully",
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Validation error",
     *     ),
     *     security={ {"passport":{}}}
     * )
     */

    public function createParkingLot(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userId' => 'required|integer',
            'images' => 'required|array|min:1',
            'images.*' => 'required|image',
            'openTime' => [
                'required',
                'date_format:H:i',
                'before:endTime',
            ],
            'endTime' => [
                'required',
                'date_format:H:i',
                'after:openTime',
            ],
            'nameParkingLot' => 'required|string|max:255|unique:parking_lots,nameParkingLot',
            'address_latitude' => 'required|unique:parking_lots,address_latitude',
            'address_longitude' => 'required|unique:parking_lots,address_longitude',
            'address' => 'required|string|max:255',
            'desc' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        $parkingLot = new ParkingLot([
            'openTime' => $data['openTime'],
            'endTime' => $data['endTime'],
            'nameParkingLot' => $data['nameParkingLot'],
            'address_latitude' => $data['address_latitude'],
            'address_longitude' => $data['address_longitude'],
            'address' => $data['address'],
            'desc' => $data['desc'],
        ]);

        $imageLinks = [];

        foreach ($request->file('images') as $image) {
            $linkImage = CloudinaryStorage::upload($image->getRealPath(), $image->getClientOriginalName(), 'parkingLot/images');
            $imageLinks[] = $linkImage;
        }

        $parkingLot->images = json_encode($imageLinks);

        $parkingLot->save();

        $userParkingLot = new UserParkingLot([
            'userId' => $data['userId'],
            'parkingId' => $parkingLot->id,
        ]);

        $userParkingLot->save();

        return response()->json([
            'message' => 'Parking lot created successfully.',
            'data' => $parkingLot
        ], 201);
    }
    /**
     * @OA\POST(
     *     path="/api/parking-lot/update/{idParkingLot}",
     *     tags={"Parking Lot"},
     *     summary="update  parking lot",
     *     description="update parking lot with the specified details",
     *     operationId="updateParkingLot",
     *   *   @OA\Parameter(
     *         name="idParkingLot",
     *         in="path",
     *         required=true,
     *          example=1000002,
     *         description="ID of the parking lot to update",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                
     *                 @OA\Property(
     *                     property="images[]",
     *                     type="array",
     *                     @OA\Items(
     *                         type="file"
     *                     ),
     *                     description="Array of images"
     *                 ),
     *                     @OA\Property(
     *                     property="imageUpdates[]",
     *                     type="array",
     *                     @OA\Items(
     *                         type="string"
     *                     ),
     *                     description="Array of images"
     *                 ),
     *                 @OA\Property(
     *                     property="openTime",
     *                     type="string",
     *                     format="time",
     *                     example="20:08"
     *                 ),
     *                 @OA\Property(
     *                     property="endTime",
     *                     type="string",
     *                     format="time",
     *                     example="21:08"
     *                 ),
     *                 @OA\Property(
     *                     property="nameParkingLot",
     *                     type="string",
     *                     example="Parking Lot Cong"
     *                 ),
     *                 @OA\Property(
     *                     property="address_latitude",
     *                     type="string",
     *                     example="16.060832"
     *                 ),
     *                 @OA\Property(
     *                     property="address_longitude",
     *                     type="string",
     *                     example="108.241491"
     *                 ),
     *                 @OA\Property(
     *                     property="address",
     *                     type="string",
     *                     example="101B Le Huu Tra"
     *                 ),
     *                 @OA\Property(
     *                     property="desc",
     *                     type="string",
     *                     example="gia ra dat an ninh coa"
     *                 ),
     *                 @OA\Property(
     *                     property="_method",
     *                     type="string",
     *                     example="PUT"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="Parking lot created successfully",
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Validation error",
     *     ),
     *     security={ {"passport":{}}}
     * )
     */

        public function updateParkingLot(Request $request, $idParkingLot)
        {
            $validator = Validator::make($request->all(), [
                'images' => 'array|min:1',
                'imageUpdates' => 'array|nullable',
                'images.*' => 'image|nullable',
                'openTime' => [
                    'sometimes',
                    'nullable',
                    'date_format:H:i',
                    'before:endTime',
                ],
                'endTime' => [
                    'sometimes',
                    'nullable',
                    'date_format:H:i',
                    'after:openTime',
                ],
                'nameParkingLot' => 'sometimes|nullable|string|max:255',
                'address_latitude' => 'sometimes|nullable',
                'address_longitude' => 'sometimes',
                'address' => 'sometimes|nullable|string|max:255',
                'desc' => 'sometimes|nullable',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();

            $parkingLot = ParkingLot::findOrFail($idParkingLot);

            if (isset($data['nameParkingLot'])) {
                $parkingLot->nameParkingLot = $data['nameParkingLot'];
            }

            if (isset($data['address'])) {
                $parkingLot->address = $data['address'];
            }

            if (isset($data['address_latitude'])) {
                $parkingLot->address_latitude = $data['address_latitude'];
            }

            if (isset($data['address_longitude'])) {
                $parkingLot->address_longitude = $data['address_longitude'];
            }

            if (isset($data['desc'])) {
                $parkingLot->desc = $data['desc'];
            }

            if (isset($data['openTime'])) {
                $parkingLot->openTime = $data['openTime'];
            }

            if (isset($data['endTime'])) {
                $parkingLot->endTime = $data['endTime'];
            }
            $imageUpdates = [];
            
            if (isset($data['imageUpdates'])&& !in_array(null, $data['imageUpdates'], true)) {
                $imageUpdates = $data['imageUpdates'];
            } else {
                $imageUpdates = json_decode($parkingLot->images);
            }
            if (isset($data['images']) && is_array($request->file('images'))) {
                foreach ($request->file('images') as $image) {
                    $linkImage = CloudinaryStorage::upload($image->getRealPath(), $image->getClientOriginalName(), 'parkingLot/images');
                    $imageUpdates[] = $linkImage;
                }
            }
            $parkingLot->images = json_encode($imageUpdates);
         
            $parkingLot->save();

            return response()->json([
                'message' => 'Parking lot updated successfully!',
                'parking_lot' => $parkingLot
            ], 200);
        }

    /**
     * @OA\Delete(
     ** path="/api/parking-lot/delete/{idParkingLot}", tags={"Parking Lot"}, 
     *  summary="Delete parking lot", operationId="deleteParkingLot",
     *   @OA\Parameter(
     *         name="idParkingLot",
     *         in="path",
     *         required=true,
     *          example=1000000,
     *         description="ID of the parking lot to retrieve",
     *         @OA\Schema(type="integer")
     *     ),
     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/
    public function deleteParkingLot($idParkingLot)
    {
        $parkingLot = ParkingLot::find($idParkingLot);
        if (!$parkingLot) {
            return response()->json(['message' => 'Parking lot not found.'], 404);
        }
        $activeBookings = Booking::whereIn('slotId', function ($query) use ($idParkingLot) {
            $query->select('id')->from('parking_slots')->whereIn('blockId', function ($query) use ($idParkingLot) {
                $query->select('id')->from('blocks')->where('parkingLotId', $idParkingLot);
            });
        })
            ->where('bookDate', '<=', Carbon::now())
            ->where('returnDate', '>=', Carbon::now())
            ->count();
        if ($activeBookings > 0) {
            return response()->json(['message' => 'Unable to delete parking lot as it is currently in use.'], 409);
        }

        $parkingLot->delete();
        return response()->json(['message' => 'Parking lot deleted successfully.']);
    }
}
