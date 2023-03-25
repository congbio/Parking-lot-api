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

class BookingEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $data;   
    private $user;
    private $title;
    private $id;
    private $messageSave;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($id,$data, $userNotify,$title,$messageSave)
    {
        $this->data = $data;
        $this->id = $id;
        $this->user = $userNotify;
        $this->title = $title;
        $this->messageSave = $messageSave;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('bookings' . '.' . $this->id);
    }

    public function broadcastAs()
    {
        return 'booking';
    }

    public function broadcastWith()
    {
        
       
        $message = $this->title;
        $data = $this->data;

        DB::table('notifications')->insert([
            'userId' => $this->id,
            'nameUserSend' => $this->messageSave[0],
            'title' => "New booking",
            'type' => 'booking',
            'image' => $this->user->avatar,
            'message' => $this->messageSave[1],
            'data' => json_encode($data),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return [
            'name' => $this->user->fullName,
            'title' => "New booking",
            'type' => 'booking',
            'message' => $message,
            'avatar' => $this->user->avatar,
            'data' => $this->data,
        ];
    }
}
