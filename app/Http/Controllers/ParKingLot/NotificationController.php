<?php

namespace App\Http\Controllers\ParKingLot;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * @OA\Get(
     ** path="/api/notifications/{userId}", tags={"Notification"}, 
     *  summary="get all notification with id user",  
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
    public function index($userId)
    {
        $user = User::findOrFail($userId);
        $notifications = $user->notifications()->orderBy('created_at', 'desc')->paginate(15)
        ->map(function ($notification) {
            $notification->data = json_decode($notification->data, true);
            return $notification;
        });
        return response()->json($notifications);
    }
      /**
     * 
     *
     * @OA\patch(
     *     path="/api/notifications/{id}/read",
     *     summary="update read",
     *     tags={"Notification"},
     *     operationId="markAsRead",
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
     *         description="comment updated successfully"
     *     ),
     *      security={ {"passport":{}}}
     * 
     * )
     */
    public function markAsRead(Request $request, $id)
    {
        $notification = Notification::findOrFail($id);
        $notification->read = $request->input('read', true);
        $notification->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ]);
    }    
}
