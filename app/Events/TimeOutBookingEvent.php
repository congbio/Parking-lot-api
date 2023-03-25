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

class TimeOutBookingEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $message;
    public $data;
 
 


    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($user,$parkingInfo,$time)
    {
        $this->user = $user;
        $this->data = $parkingInfo;
        $this->message = " {$time}  minutes left until you finish parking  {$parkingInfo->parkingName}";
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('time-outs' . '.' . $this->user->id);
    }

    public function broadcastAs()
    {
        return 'time-out';
    }

    public function broadcastWith()
    {
         
          
        $userId = $this->user->id;
        $message = $this->message;

        DB::table('notifications')->insert([
            'nameUserSend' => $this->user->fullName,
            'userId' => $userId,
            'title' => 'Time Out Booking',
            'type' => 'timeOutBooking',
            'image' => $this->user->avatar,
            'message' => $message,
            'data' => json_encode($this->data),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return [
            'name' => $this->user->fullName,
            'title' => 'Time Out Booking',
            'type' => 'timeOutBooking',
            'message' => $message,
            'avatar'=>$this->user->avatar,  
            'data' => $this->data,
        ];
    }
}
