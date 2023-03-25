<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;
    protected $table = 'notifications';
    protected $fillable = ['userId','title','message','data','type','read'];

    public function user()
    {
        return $this->belongsTo(User::class,'userId');
    }
}
