<?php

namespace App\Http\Controllers;

use App\Events\MessageStatus;
use App\Events\NewChatMessage;
use App\Events\UnreadMessagesEvent;
use App\Events\UserStatus;
use App\Models\GroupChat;
use Carbon\Traits\Timestamp;
use Illuminate\Http\Request;
use App\Models\Chat;
use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Session;

class ChatController extends Controller
{
    public function index()
    {
        $userId = Auth::user()->id;
        // $users = User::where('id','!=',Auth::user()->id)->get(['id','name','status']);
        // $msgCount = Chat::where("receiver", Auth::user()->id)->where('receiver', $recepeint)->where('receipt','!=','read')->count();
        $users = User::withCount([
            'sendChats' => function ($query) use ($userId) {
                $query->where('receiver', $userId)
                    ->where('receipt', '!=', 'read');
            },
        ])->where('id', '!=', Auth::user()->id)->get(['id', 'name', 'status']);

        $onlineUsers = User::where('status', 'online')->count();
        // toastr()->addSuccess('Your account has been restored.');

        $userGroups = Group::whereHas('users', function ($query) use ($userId) {
            $query->where('users.id', $userId);
        })->get();

        return view("home", compact("users", "onlineUsers", "userGroups"));
    }

    public function getChat(Request $request)
    {
        $recepeint = $request->uid;
        $loginUser = Auth::user()->id;

        Chat::where('sender', $recepeint)->where('receiver', $loginUser)->where('receipt', '!=', 'read')->update(['receipt' => 'read']);
        User::where('id', $loginUser)->update(['active_chat' => $recepeint]);
        event(new MessageStatus($loginUser, $recepeint, 'read'));


        $chats =  Chat::with('sender:id,name')->with('receiver:id,name')->where(function ($query) use ($recepeint, $loginUser) {
            $query->where("sender", $loginUser)->where('receiver', $recepeint);
        })->orWhere(function ($query) use ($recepeint, $loginUser) {
            $query->where("sender", $recepeint)->where('receiver', $loginUser);
        })
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json(['chats' => $chats]);
    }


    public function getOldMessages($userId, $offset, $limit)
    {
        if ($userId == 0) {
            //---------------data for channel chats--------------------//
            $chats = Chat::with('sender:id,name')->where(function ($query) {
                $query->where('receiver', 0);
            })
                ->orderBy('created_at', 'desc')
                ->offset($offset)
                ->limit($limit)
                ->get();
        } else {
            //---------------data for users one to one chat----------------//
            $loginUser = Auth::user()->id;
            $chats = Chat::with('sender:id,name')->with('receiver:id,name')->where(function ($query) use ($userId, $loginUser) {
                $query->where("sender", $loginUser)->where('receiver', $userId);
            })->orWhere(function ($query) use ($userId, $loginUser) {
                $query->where("sender", $userId)->where('receiver', $loginUser);
            })
                ->orderBy('created_at', 'desc')
                ->offset($offset)
                ->limit($limit)
                ->get();
        }

        return response()->json(['chats' => $chats]);
    }

    public function store(Request $request)
    {
        $request->validate([
            "receiver" => "required",

        ]);

        $loginUser = Auth::user()->id;
        $recepeint = $request->receiver;

        $file = $request->file('mediaInput');

        if ($request->message && $file) {
            $filename = time() . '_chat.' . $file->extension();
            $file->move(public_path('uploads'), $filename);
            event(new NewChatMessage([$request->message, $filename], $loginUser, $recepeint, Auth::user()->name, now(), 'both'));
        } elseif ($file) {
            $filename = time() . '_chat.' . $file->extension();
            $file->move(public_path('uploads'), $filename);
            event(new NewChatMessage($filename, $loginUser, $recepeint, Auth::user()->name, now(), 'media'));
        } elseif ($request->message) {
            $filename = null;
            event(new NewChatMessage($request->message, $loginUser, $recepeint, Auth::user()->name, now(), 'text'));
        }

        $activeChatId = User::where("id", $recepeint)->get(['active_chat', 'status'])[0];

        if ($activeChatId->active_chat == $loginUser) {
            $messageStatus = 'read';
        } elseif ($activeChatId->status == 'online') {
            $messageStatus = 'delivered';
        } else {
            $messageStatus = 'notdelivered';
        }

        $newMsg = new Chat;
        $newMsg->sender = $loginUser;
        $newMsg->receiver = $recepeint;
        $newMsg->message = $request->message;
        $newMsg->media = $filename;
        $newMsg->receipt = $messageStatus;
        $newMsg->save();

        $msgCount = Chat::where("sender", $loginUser)->where('receiver', $recepeint)->where('receipt', '!=', 'read')->count();

        if ($msgCount > 0) {
            event(new UnreadMessagesEvent($loginUser, $recepeint, $msgCount));
        }

        $chats =  Chat::with('sender:id,name')->with('receiver:id,name')->where(function ($query) use ($recepeint, $loginUser) {
            $query->where("sender", $loginUser)->where('receiver', $recepeint);
        })->orWhere(function ($query) use ($recepeint, $loginUser) {
            $query->where("sender", $recepeint)->where('receiver', $loginUser);
        })
            ->latest()
            ->limit(1)
            ->get();

        return response()->json(['chats' => $chats]);
    }

    public function updateStatus(Request $request)
    {
        event(new UserStatus(Auth::user()->id, $request->status));
        User::where('id', Auth::user()->id)->update(['status' => $request->status]);
        if ($request->status == 'offline') {
            User::where('id', Auth::user()->id)->update(['active_chat' => null]);
        }

        if ($request->status == 'online') {
            event(new MessageStatus(Auth::user()->id, 'all', 'read'));
            Chat::where('receiver', Auth::user()->id)->where('receipt', 'notdelivered')->update(['receipt' => 'delivered']);
        }
    }

    //----------------------------------Broadcast Channel-----------------------------------//

    public function loadBroadcastChats()
    {

        $loginUser = Auth::user()->id;
        $recepeint = 0;

        Chat::where('receiver', $recepeint)->where('receipt', '!=', 'read')->update(['receipt' => 'read']);
        User::where('id', $loginUser)->update(['active_chat' => $recepeint]);
        // event(new MessageStatus($loginUser, $recepeint, 'read'));


        $chats =  Chat::with('sender:id,name')->with('receiver:id,name')->where(function ($query) use ($recepeint) {
            $query->where('receiver', $recepeint);
        })
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json(['chats' => $chats]);
    }

    public function sendMsgToChannel(Request $request)
    {

        $loginUser = Auth::user()->id;
        $recepeint = 0;

        $file = $request->file('mediaInput');

        if ($request->message && $file) {
            $filename = time() . '_chat.' . $file->extension();
            $file->move(public_path('uploads'), $filename);
            event(new NewChatMessage([$request->message, $filename], $loginUser, $recepeint, Auth::user()->name, now(), 'both'));
        } elseif ($file) {
            $filename = time() . '_chat.' . $file->extension();
            $file->move(public_path('uploads'), $filename);
            event(new NewChatMessage($filename, $loginUser, $recepeint, Auth::user()->name, now(), 'media'));
        } elseif ($request->message) {
            $filename = null;
            event(new NewChatMessage($request->message, $loginUser, $recepeint, Auth::user()->name, now(), 'text'));
        }

        $messageStatus = 'read';

        $newMsg = new Chat;
        $newMsg->sender = $loginUser;
        $newMsg->receiver = $recepeint;
        $newMsg->message = $request->message;
        $newMsg->media = $filename;
        $newMsg->receipt = $messageStatus;
        $newMsg->save();

        // $msgCount = Chat::where('receiver', $recepeint)->where('receipt', '!=', 'read')->count();

        // if ($msgCount > 0) {
        //     event(new UnreadMessagesEvent($loginUser, $recepeint, $msgCount));
        // }

        $chats =  Chat::with('sender:id,name')->with('receiver:id,name')->where(function ($query) use ($recepeint, $loginUser) {
            $query->where('receiver', $recepeint);
        })
            ->latest()
            ->limit(1)
            ->get();

        return response()->json(['chats' => $chats]);
    }



    //---------------------------------- Group Feature -----------------------------------//

    public function createGroup(Request $request)
    {

        $validation = $request->validate([
            'groupName' => 'required | max:20',
            'groupMembers' => 'required | array'
        ]);

        $newGroup = new Group;
        $newGroup->name = $request->groupName;
        $newGroup->admin_id = Auth::user()->id;
        $newGroup->image = 'img';
        $newGroup->description = 'description';
        $newGroup->save();

        // Attach users to the group from the 'groupMembers' array
        if ($request->has('groupMembers')) {
            $newGroup->users()->attach($request->groupMembers);
        }

        return redirect()->back();
    }

    public function getGroupChat(Request $request)
    {
        $loginUser = Auth::user()->id;
        $groupId = $request->groupId;

        $chats =  GroupChat::with('sender:id,name')->with('group:id,name')->where(function ($query) use ($groupId) {
            $query->where('group_id', $groupId);
        })
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json(['chats' => $chats]);
    }

    public function sendGroupChat(Request $request)
    {
        $request->validate([
            "groupId" => "required",
        ]);

        $loginUser = Auth::user()->id;
        $groupId = $request->groupId;

        $file = $request->file('mediaInput');

        if ($request->message && $file) {
            $filename = time() . '_chat.' . $file->extension();
            $file->move(public_path('uploads'), $filename);
            event(new NewChatMessage([$request->message, $filename], $loginUser, $groupId, Auth::user()->name, now(), 'both'));
        } elseif ($file) {
            $filename = time() . '_chat.' . $file->extension();
            $file->move(public_path('uploads'), $filename);
            event(new NewChatMessage($filename, $loginUser, $groupId, Auth::user()->name, now(), 'media'));
        } elseif ($request->message) {
            $filename = null;
            event(new NewChatMessage($request->message, $loginUser, $groupId, Auth::user()->name, now(), 'text'));
        }

        $newMsg = new GroupChat;
        $newMsg->sender = $loginUser;
        $newMsg->group_id = $groupId;
        $newMsg->message = $request->message;
        $newMsg->media = $filename;
        $newMsg->receipt = 'read';
        $newMsg->save();

        $chats =  GroupChat::with('sender:id,name')->with('group:id,name')->where(function ($query) use ($groupId) {
            $query->where('group_id', $groupId);
        })
            ->latest()
            ->limit(1)
            ->get();

        return response()->json(['chats' => $chats]);
    }
    public function getOldGroupChats($groupId, $offset, $limit)
    {

        $chats =  GroupChat::with('sender:id,name')->with('group:id,name')->where(function ($query) use ($groupId) {
            $query->where('group_id', $groupId);
        })
            ->orderBy('created_at', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get();



        return response()->json(['chats' => $chats]);
    }
}
