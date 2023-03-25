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

class QrEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;   
    public $user;
    public $owner;
    public $nameParking;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($user, $data,$owner,$nameParking)
    {
        $this->user = $user;
        $this->data = $data;
        $this->owner = $owner;
        $this->nameParking = $nameParking;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('qr-codes' . '.' . $this->user->id);
    }

    public function broadcastAs()
    {
        return 'qr-code';
    }

    public function broadcastWith()
    {
        $userId = $this->user->id;
        $message = " have completely finished parking lot {$this->nameParking}";
        $data = $this->data;

        DB::table('notifications')->insert([
            'userId' => $userId,
            'nameUserSend' => $this->owner->fullName,
            'title' => 'Completed parking lot',
            'type' => 'QRCode',
            'image' => $this->owner->avatar,
            'message' => $message,
            'data' => json_encode($data),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return [
            'name' => $this->owner->fullName,
            'title' => 'Completed parking lot',
            'type' => 'QRCode',
            'message' => $message,
            'avatar' => $this->owner->avatar,
            'data' => $this->data,
        ];
    }
}
