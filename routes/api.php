<?php

use App\Http\Controllers\Account\AuthController;
use App\Http\Controllers\Account\RegisterController;
use App\Http\Controllers\Account\ForgotPasswordController;
use App\Http\Controllers\Chat\ChatController;
use App\Http\Controllers\DashBoard\ManagerController;
use App\Http\Controllers\ParKingLot\BlockParkingCarController;
use App\Http\Controllers\ParKingLot\BookingController;
use App\Http\Controllers\ParKingLot\CommentController;
use App\Http\Controllers\ParKingLot\NotificationController;
use App\Http\Controllers\ParKingLot\Owner\BlockController;
use App\Http\Controllers\ParKingLot\Owner\SlotController;
use App\Http\Controllers\ParKingLot\ParKingLotController;
use App\Http\Controllers\ParKingLot\WishlistController;
use App\Http\Controllers\Profile\OwnerController;
use App\Http\Controllers\Profile\UserController;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Telescope\Http\Controllers\NotificationsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post("/test", function (Request $request) {
    return $request->all();
});
Route::controller(AuthController::class)->prefix("/auth/")->group(function () {
    Route::post("login", "login");
});

Route::controller(RegisterController::class)->prefix("/account/")->group(function () {
    Route::post("register", "register");
    Route::post("confirm-registration", "confirmRegistration");
});

## reset password
Route::controller(ForgotPasswordController::class)->prefix("/password/")->group(function () {
    Route::post("email", "sendCode");
    Route::post("confirm-reset", "checkCode");
    Route::post("reset", "resetPassword");
});
## Profile
Route::controller(UserController::class)->prefix("/user/")->group(function () {
    Route::get("{id}/info", "showProfile");
    Route::get("{id}/role", "getRole");
    Route::get("", "getAllUser");
    Route::put("update/{id}", "updateProfile");

});

Route::controller(ParKingLotController::class)->prefix("/parking-lot/")->group(function () {
    Route::get("", "index");
    Route::get("location", "showParkingLotNearLocation");
    Route::post("create", "createParkingLot");
    Route::put("update/{idParkingLot}", "updateParkingLot");
    Route::delete("delete/{idParkingLot}", "deleteParkingLot");
    Route::get("{idParking}","showParkingLot");
    Route::get("{id}/info/comment", "showCommentOfParking");
    Route::get("{id}/info/{userId}", "getInfoParkingLot");
});
Route::controller(BlockParkingCarController::class)->prefix("/parking-lot/")->group(function () {
    Route::get("{id}/slots", "getSlotStatusByBookingDateTime");
    Route::post("block/create", "createBlockSlot");
});

Route::controller(BookingController::class)->prefix("/booking/")->group(function () {
    Route::get("slots", "getSlotsByIdWithBlockName");
    Route::post("", "bookParkingLot");
    Route::get("{userId}/history", "historyBookingSummary");
    Route::get("history/details", "historyBookingDetail");
    Route::get("show", "getDetailQRcode");
    Route::patch("update", "completeBooking");
    Route::get("check-date","checkReturnDAte");
    Route::put("cancel","cancelBooking");
});

// Space owner


Route::controller(OwnerController::class)->prefix("/owner/")->group(function () {
    Route::put("create/{id}", "becomeSpaceOwner");

});


Route::controller(BlockController::class)->prefix("/parking-lot/")->group(function () {
    Route::post("block/create", "createBlockSlot");
    Route::put("block/{id}/update", "updateBlock");
    Route::get("block/{id}", "showDetailBlock");
    Route::get("{idParking}/blocks", "getAllBlock");
    Route::delete("block/{id}/delete", "deleteBlock");
});
Route::controller(SlotController::class)->prefix("/parking-lot/")->group(function () {
    Route::get("block/{blockId}/slots", "getAllSlot");
    Route::put("block/slots/update/{slotId}", "updateSlot");
    Route::get("block/slots/{slotId}", "getDetailSlot");
    Route::post("block/slots/create", "createSlot");
    Route::post("block/slots/delete", "deleteSlots");

   
});
Route::controller(ManagerController::class)->prefix("/dashboard/")->group(function () {
 
    Route::get("parkingLots/{userId}", "getParkingUserManage");
    Route::get("parkingLots/{parkingLotId}/{period}", "parkingLotBookingStats");
    Route::get("{userId}/revenue/{period}", "getRevenueDetails");

});
// Chat
Route::controller(ChatController::class)->prefix("/chat/")->group(function () {
    Route::get("history/{user1Id}/{user2Id}", "getChatHistory");
    Route::post("send", "sendMessage");
});
// wilisht
Route::controller(WishlistController::class)->prefix("/user/")->group(function () {
    Route::get("{userId}/wishlist", "getWishlist");
    Route::post("wishlist/add", "addWishList");
});

// Notification
Route::controller(NotificationController::class)->prefix("/notifications/")->group(function () {
    Route::get("{userId}", "index");
    Route::patch("{id}/read","markAsRead");
});
Route::controller(CommentController::class)->prefix("/comments/")->group(function () {
    Route::post("", "store");
    Route::get("test", "getBookTimeout");
    Route::patch("{id}/update","editComment");
    Route::delete("{id}/delete","deleteComment");
    Route::get("{idUser}/{idParkingLot}","getComment");
});