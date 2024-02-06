<?php

namespace App\Http\Controllers;

use App\Events\CallConnectionEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Events\VoiceCallEvent;


class CallController extends Controller
{
    public function connectVoiceCall(Request $request)
    {
        $callerId = Auth::user()->id;
        $callerName = Auth::user()->name;
        $receiverId = $request->receiverId;
        $receiverName = User::select('name','status')->where('id',$receiverId)->first();

        $payload = [
            'callerId' => $callerId,
            'callerName' => $callerName,
            'status' => 'ringing',
        ];

        $connectionPayload = [
            'type' => 'offer',
            'data' =>$request->offer,
        ];

        event(new VoiceCallEvent($receiverId, $payload));
        event(new CallConnectionEvent($receiverId, $connectionPayload));


        return response()->json(['receiverName' => $receiverName->name, 'receiverStatus' => $receiverName->status]);

    }
    public function rejectedVoiceCall(Request $request)
    {
        $callerId = $request->callerId;

        $payload = [
            'callerId' => null,
            'callerName' => null,
            'status' => 'rejected',
        ];

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

        event(new VoiceCallEvent($callerId, $payload));

        return response()->json(['status' => 'rejected']);

    }
    public function acceptVoiceCall(Request $request)
    {
        $callerId = $request->callerId;
        $payload = [
            'callerId' => null,
            'callerName' => null,
            'status' => 'accepted',
        ];

        event(new VoiceCallEvent($callerId, $payload));
        return response()->json(['status' => 'accepted']);

    }

    public function callConnection(Request $request){


            $receiverId = $request->uid;
            $connectionPayload = [
                'type' => $request->type,
                'data' =>$request->payload,
            ];
            event(new CallConnectionEvent($receiverId, $connectionPayload));
        

    }
}
