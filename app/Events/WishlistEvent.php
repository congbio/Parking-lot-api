<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class WishlistEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $message;
    public $parkingLotName;
    public $ownerId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($user, $ownerId,$nameParkingLot)
    {
        $this->user = $user;
        $this->message = " add wishlist parkingLot {$nameParkingLot}";
        $this->ownerId = $ownerId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('wishlists' . '.' . $this->ownerId);
    }

    public function broadcastAs()
    {
        return 'wishlist';
    }

    public function broadcastWith()
    {
         
          
        $userId = $this->ownerId;
        $message = $this->message;
        $data = ['parking_lot_name' => $this->parkingLotName];

        DB::table('notifications')->insert([
            'nameUserSend' => $this->user->fullName,
            'userId' => $userId,
            'title' => 'New Wishlist',
            'type' => 'wishlist',
            'image' => $this->user->avatar,
            'message' => $message,
            'data' => json_encode($data),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return [
            'name' => $this->user->fullName,
            'title' => 'New Wishlist',
            'type' => 'wishlist',
            'message' => $message,
            'data' => null,
            'avatar'=>$this->user->avatar,  
        ];
    }
   
    
}