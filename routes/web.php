<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Session;


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

Route::get('/', function () {
    return view('welcome');
});


Route::group(['middleware'=> 'auth'], function () {
    Route::get('/home', [ChatController::class, 'index'])->name('home');
    Route::get('/dashboard', [ChatController::class, 'index'])->name('dashboard');
    Route::post('/dashboard', [ChatController::class, 'store'])->name('dashboard');
    Route::post('/chat', [ChatController::class, 'getChat'])->name('chat');
    Route::post('/update-status', [ChatController::class, 'updateStatus'])->name('update-status');
    Route::get('/get-old-messages/{userId}/{offset}/{limit}', [ChatController::class, 'getOldMessages']);

    Route::get('broadcast', [ChatController::class,'loadBroadcastChats'])->name('broadcast');
    Route::post('broadcast', [ChatController::class,'sendMsgToChannel'])->name('broadcast');

});

require __DIR__ . '/auth.php';

Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
