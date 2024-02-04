<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CallController;
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


Route::group(['middleware'=> 'auth'], function ()
{
    // for personal chats
    Route::get('/home', [ChatController::class, 'index'])->name('home');
    Route::get('/dashboard', [ChatController::class, 'index'])->name('dashboard');
    Route::post('/dashboard', [ChatController::class, 'store'])->name('dashboard');
    Route::post('/chat', [ChatController::class, 'getChat'])->name('chat');
    Route::post('/update-status', [ChatController::class, 'updateStatus'])->name('update-status');
    Route::get('/get-old-messages/{userId}/{offset}/{limit}', [ChatController::class, 'getOldMessages']);

    // for channel chats
    Route::get('broadcast', [ChatController::class,'loadBroadcastChats'])->name('broadcast');
    Route::post('broadcast', [ChatController::class,'sendMsgToChannel'])->name('broadcast');


    // for group chats
    Route::post('createGroup', [ChatController::class,'createGroup'])->name('createGroup');
    Route::post('groupChats', [ChatController::class,'getGroupChat'])->name('groupChats');
    Route::post('/sendChat', [ChatController::class, 'sendGroupChat'])->name('sendChat');
    Route::get('/get-old-groupChats/{userId}/{offset}/{limit}', [ChatController::class, 'getOldGroupChats']);
    Route::get('updateGroup/{groupId}', [ChatController::class,'editGroup']);
    Route::post('updateGroup', [ChatController::class,'updateGroup'])->name('updateGroup');
    Route::get('deleteGroup/{groupId}', [ChatController::class,'deleteGroup']);
    Route::get('exitGroup/{groupId}', [ChatController::class,'exitGroup']);


    //for voice call
    Route::post('connectVoiceCall', [CallController::class,'connectVoiceCall']);
    Route::post('rejectedVoiceCall', [CallController::class,'rejectedVoiceCall']);
    Route::post('endVoiceCall', [CallController::class,'endVoiceCall']);
    Route::post('acceptVoiceCall', [CallController::class,'acceptVoiceCall']);
    Route::post('callConnection', [CallController::class,'callConnection']);




});

require __DIR__ . '/auth.php';

Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
