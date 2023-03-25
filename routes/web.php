<?php

use App\Events\PusherTestEvent;
use App\Events\WishlistEvent;
use App\Http\Controllers\Admin\Auth\AuthController;
use App\Http\Controllers\Admin\Dashboard\DashboardController;
use App\Http\Controllers\Chat\ChatController;
use App\Http\Controllers\ParKingLot\BlockParkingCarController;
use App\Http\Controllers\ParKingLot\ParKingLotController;
use Illuminate\Support\Facades\Route;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/admin/login', [AuthController::class,'showLoginForm'])->name('login');
// Route::post('/admin/login',[AuthController::class,'login'] )->name('admin.login.submit');

// // Đăng xuất
// Route::post('/admin/logout', [AuthController::class,'login'])->name('admin.logout');

// Route::middleware(['auth', 'admin'])->group(function () {
//     Route::get('/admin/dashboard', [DashboardController::class,'index'])->name('admin.dashboard');
//     Route::get('/admin/users', 'App\Http\Controllers\Admin\UserController@index')->name('admin.users');
//     // các route khác cho trang quản trị
//     Route::get('/', function () {return view('welcome');
//     });
// });
Route::get("/", fn () => view("welcome"));
// Route::get('/', [ChatController::class, 'getChatHistory'])->name('chat');
// Route::get('/chat/history/{user1Id}/{user2Id}', [ChatController::class, 'getChatHistory'])->name('chat.history');
// Route::post('/chat/send-message', [ChatController::class, 'sendMessage'])->name('send-message');

Route::get('/pusher-test', function () {
    $ownerId= 1000000;
    // event(new WishlistEvent('cong',$ownerId));

  
    return 'Notification sent!';
});

  // $data = ['message' => 'Hello, world!'];
    // $channel = 'my-channel';
    // $users = User::whereIn('id', [1000008,1000001])->get();
    // event(new PusherTestEvent($data, $channel, $users));