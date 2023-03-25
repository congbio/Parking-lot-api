<?php

namespace App\Http\Controllers\ParKingLot;

use App\Events\CommentEvent;
use App\Events\TimeOutBookingEvent;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Comment;
use App\Models\Notification;
use App\Models\ParkingLot;
use App\Models\ParkingSlot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    /**
     * @OA\Post(
     ** path="/api/comments", tags={"Comments"}, 
     *  summary="create comment", operationId="storeComment",
     *  @OA\Parameter(name="userId",in="query",required=true,example=1000000, @OA\Schema( type="integer" )),
     *  @OA\Parameter(name="parkingId",in="query",required=true,example=1000000, @OA\Schema( type="integer" )),
     *  @OA\Parameter(name="content",in="query",required=false,example="good", @OA\Schema( type="string" )),
     *  @OA\Parameter(name="ranting",in="query",required=true,example=2, @OA\Schema( type="integer" )),

     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userId' => 'required|integer|exists:users,id',
            'parkingId' => 'required|integer|exists:parking_lots,id',
            'content' => 'nullable|string',
            'ranting' => 'required|integer|min:1|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $comment = new Comment();
        $comment->userId = $request->userId;
        $comment->parkingId = $request->parkingId;
        if (isset($request->content)) {
            $comment->content = $request->content;
        }
        $comment->ranting = $request->ranting;
        $comment->save();

        $parkingLotInfo = ParkingLot::findOrFail($comment->parkingId);
        $user = User::findOrFail($comment->userId);
        $owner = $parkingLotInfo->user;
        try {

            event(new CommentEvent($user, $owner, $parkingLotInfo, $comment));
        } catch (\Throwable $th) {
            Log::error('Error sending comment event: ' . $th->getMessage());
        }
        return response()->json($comment, 201);
    }
    /**
     * @OA\Patch(
     *     path="/api/comments/{id}/update",
     *     summary="Update a comment.",
     *          tags={"Comments"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the comment to update.",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="content",
     *         in="query",
     *         description="New content of the comment.",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="ranting",
     *         in="query",
     *         description="New ranting of the comment (1-5).",
     *         @OA\Schema(
     *             type="integer",
     *             minimum=1,
     *             maximum=5
     *         )
     *     ),

     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/
    public function editComment(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'string',
            'ranting' => 'integer|min:1|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $comment = Comment::find($id);

        if (!$comment) {
            return response()->json(['message' => 'Comment not found'], 404);
        }

        if ($request->has('content')) {
            $comment->content = $request->content;
        }

        if ($request->has('ranting')) {
            $comment->ranting = $request->ranting;
        }

        $comment->save();

        return response()->json($comment, 200);
    }
    /**
     * @OA\get(
     *     path="/api/comments/{idUser}/{idParkingLot}",
     *     summary="get comment with idUser and id parking lot.",
     *          tags={"Comments"},
     *     @OA\Parameter(
     *         name="idUser",
     *         in="path",
     *         description="id user.",
     *         required=true,
     *         example = 1000000,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="idParkingLot",
     *         in="query",
     *         example = 1000000,
     *         description="id parking lot.",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/
    public function getComment(Request $request, $idUser, $idParkingLot)
    {
        // $validator = Validator::make($request->all(),[
        //     'idUser'=>'required|int',
        //     'idParkingLot'=>'required|int',
        // ]);

        // if($validator->fails()){
        //     return response()->json($validator->errors(),400);
        // }
        $comments = Comment::where('userId', $request->idUser)
            ->where("parkingId", $request->idParkingLot)
            ->join('users', 'users.id', '=', 'comments.userId')
            ->select('comments.id as idComment','comments.created_at','comments.updated_at', 'comments.content', 'comments.parkingId', 'comments.ranting', 'users.*', 'comments.created_at')
            ->get()
            ->map(function ($comment) {
                $comment->time_ago = $comment->created_at->diffForHumans();
                return $comment;
            });


        return response()->json($comments, 200);
    }

    /**
     * Update the user's profile.
     *
     * @OA\Delete(
     *     path="/api/comments/{id}/delete",
     *     summary="Delete Comment",
     *     tags={"Comments"},
     *     operationId="deleteComment",
     *     @OA\Parameter(
     *         name="id",
     *         description="Id of comment",
     *         in="path",
     *         example=1000000,
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
    public function deleteComment($id)
    {
        $comment = Comment::findOrFail($id);
        $comment->delete();
        return response()->json(['message' => 'Comment deleted successfully'], 204);
    }

    public function getBookTimeout()
    {  // Define the time max
        define('MAX_BOOKING_MiNUTE', 60);
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
        return $bookings;
    }
}
