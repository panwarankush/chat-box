<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('voice-call.{eventReceiverId}', function (User $user, int $eventReceiverId)
{
    if($user->id === $eventReceiverId){
        return true; // Return true if authorized, false otherwise
    }else{
        return false;
    }
    // Add your authorization logic here
    // For example, you might check if $user->id is one of $callerUserId or $receiverUserId
});

Broadcast::channel('call-connection.{eventReceiverId}', function (User $user, int $eventReceiverId)
{
    if($user->id === $eventReceiverId){
        return true; // Return true if authorized, false otherwise
    }else{
        return false;
    }
});
