<?php

namespace App\Http\Controllers;

use App\Events\CallConnectionEvent;
use App\Models\User;
use App\Models\Call;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Events\VoiceCallEvent;
use Illuminate\Support\Carbon;


class CallController extends Controller
{
    public function connectVoiceCall(Request $request)
    {
        $callerId = Auth::user()->id;
        $callerName = Auth::user()->name;
        $receiverId = $request->receiverId;
        $receiver = User::select('name', 'status')->where('id', $receiverId)->first();

        $payload = [
            'callerId' => $callerId,
            'callerName' => $callerName,
            'status' => 'ringing',
        ];

        $isReceiverOnCall = Call::where(function ($query) use ($receiverId) {
            $query->where('receiverId', $receiverId)
                ->orWhere('callerId', $receiverId);
        })
            ->where('status', 'pending')
            ->exists();

        if ($isReceiverOnCall) {
            // on call
            $receiverStatus = 'onCall';
        } else {
            $newCall = new Call;
            $newCall->callerId = $callerId;
            $newCall->receiverId = $receiverId;
            if ($receiver->status == 'offline') {
                $receiverStatus = 'offline';
                $newCall->status = 'complete';
            } else {
                $receiverStatus = 'online';
            }
            $newCall->save();
            event(new VoiceCallEvent($receiverId, $payload));
        }

        return response()->json(['receiverName' => $receiver->name, 'receiverStatus' => $receiverStatus]);
    }
    public function rejectedVoiceCall(Request $request)
    {
        $callerId = $request->callerId;

        $payload = [
            'callerId' => null,
            'callerName' => null,
            'status' => 'rejected',
        ];

        Call::where('receiverId', Auth::id())->where('status','pending')
            ->update(['status' => 'complete','type' => 'rejected']);

        event(new VoiceCallEvent($callerId, $payload));

        return response()->json(['status' => 'rejected']);
    }
    public function endVoiceCall(Request $request)
    {
        $callerId = $request->callerId;

        $payload = [
            'callerId' => null,
            'callerName' => null,
            'status' => 'ended',
        ];

        if($request->buttonType == 'callEndBeforeConnect')
        {
            Call::where('callerId', Auth::id())->where('status','pending')
            ->update(['status' => 'complete']);

        }elseif($request->buttonType == 'callEndAfterConnect')
        {
            $currentTimestamp = date('Y-m-d H:i:s', strtotime('now'));
            Call::where(function ($query) use ($callerId) {
                $query->where('callerId', $callerId)
                      ->orWhere('receiverId', $callerId);
            })
            ->where('status', 'pending')
            ->update(['status' => 'complete', 'type' => 'accepted', 'endTime' => $currentTimestamp]);
        }
        event(new VoiceCallEvent($callerId, $payload));

        return response()->json(['status' => 'rejected']);
    }
    public function acceptVoiceCall(Request $request)
    {
        $callerId = $request->callerId;
        $callerName = User::select('name', 'status')->where('id', $callerId)->first();

        $payload = [
            'callerId' => $callerId,
            'callerName' => $callerName->name,
            'loginUserName' => Auth::user()->name,
            'status' => 'accepted',
        ];

        event(new VoiceCallEvent($callerId, $payload));
        return response()->json(['status' => 'accepted', 'payload' => $payload]);
    }

    public function callConnection(Request $request)
    {
        $receiverId = $request->uid;
        $connectionPayload = [
            'type' => $request->type,
            'eventSender' => Auth::user()->id,
            'data' => $request->payload,
        ];
        event(new CallConnectionEvent($receiverId, $connectionPayload));
    }
}
