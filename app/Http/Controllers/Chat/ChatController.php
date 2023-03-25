<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Pusher\Pusher;

class ChatController extends Controller
{
 
    public function getChatHistory($user1Id, $user2Id)
    {
        $messages = Message::where(function ($query) use ($user1Id, $user2Id) {
            $query->where('sender_id', $user1Id)
                ->where('receiver_id', $user2Id);
        })
            ->orWhere(function ($query) use ($user1Id, $user2Id) {
                $query->where('sender_id', $user2Id)
                    ->where('receiver_id', $user1Id);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        $chatHistory = [];

        foreach ($messages as $message) {
            $chatHistory[] = [
                'sender_id' => $message->sender_id,
                'receiver_id' => $message->receiver_id,
                'content' => $message->content,
                'created_at' => $message->created_at
            ];
        }

        return response()->json(['chat_history' => $chatHistory]);
    }

    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:255',
            'senderId' => 'required',
            'receiverId' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            [
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'useTLS' => true
            ]
        );
        $content = $request->input('content');
        $senderId = $request->input('senderId');
        $receiverId = $request->input('receiverId');

        $data = [
            'content' => $content,
            'senderId' => $senderId,
            'receiverId' => $receiverId,
        ];

        $pusher->trigger('chat', 'message', $data);

        // Store the message in the database
        $message = new Message();
        $message->content = $content;
        $message->sender_id = $senderId;
        $message->receiver_id = $receiverId;
        $message->save();

        return response()->json(['status' => 'success']);
    }
}
