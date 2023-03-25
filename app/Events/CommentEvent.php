<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class CommentEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $owner;
    public $message;
    public $parkingLot;
    public $data;


    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($user,$owner,$parkingLot,$comment)
    {
        $this->user = $user;
        $this->owner = $owner;
        $this->message = " comment parkingLot {$parkingLot->nameParkingLot}";
        $this->parkingLot = $parkingLot;
        $this->data = $comment;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('comments' . '.' . $this->owner->id);
    }

    public function broadcastAs()
    {
        return 'comment';
    }

    public function broadcastWith()
    {
         
          
        $userId = $this->owner->id;
        $message = $this->message;

        DB::table('notifications')->insert([
            'nameUserSend' => $this->user->fullName,
            'userId' => $userId,
            'title' => 'New comment',
            'type' => 'comment',
            'image' => $this->user->avatar,
            'message' => $message,
            'data' => json_encode($this->data),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return [
            'name' => $this->user->fullName,
            'title' => 'New Comment',
            'type' => 'comment',
            'message' => $message,
            'data' => $this->data,
            'avatar'=>$this->user->avatar,  
        ];
    }
}
