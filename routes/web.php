<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;

Auth::routes();
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/logout')->middleware('redirectGetRequests');

// Группировка роутов для зарегестрированных пользователей
Route::group(['middleware' => 'auth',
    'namespace' => 'App\Http\Controllers'], function() {
    
    // Роуты друзей
    Route::group(['namespace' => 'Friends'], function() {
        Route::post('/friends', 'GetFriendController');
        Route::post('/add-friend', 'AddFriendController');
        Route::post('/delete-friend', 'DeleteFriendController');
        Route::post('/reject-app', 'RejectController');
        Route::post('/cancel-app', 'CancelAppController');
        
        Route::get('/friends')->middleware('redirectGetRequests');
        Route::get('/add-friend')->middleware('redirectGetRequests');
        Route::get('/delete-friend')->middleware('redirectGetRequests');
        Route::get('/reject-app')->middleware('redirectGetRequests');
        Route::get('/cancel-app')->middleware('redirectGetRequests');
    });

    // Роуты чата
    Route::group(['namespace' => 'Chat'], function() {
        Route::post('/load-chat', 'LoadChatController');
        Route::post('/sendMsg', 'SendMsgController');
        Route::post('/update-chat', 'UpdateChatController');
        Route::post('/delete-chat', 'DeleteChatController');

        Route::get('/load-chat')->middleware('redirectGetRequests');
        Route::get('/sendMsg')->middleware('redirectGetRequests');
        Route::get('/update-chat')->middleware('redirectGetRequests');
        Route::get('/delete-chat')->middleware('redirectGetRequests');
    });
});