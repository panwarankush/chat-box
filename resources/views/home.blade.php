@extends('layouts.app')

@section('content')
    {{-- <div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    {{ __('You are logged in!') }}
                </div>
            </div>
        </div>
    </div>
</div> --}}

    {{-- --------- create group modal start -------------- --}}

    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-dark" id="exampleModalLabel">Create New Group</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="createGroup" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="group_name" class="form-label">Group Name</label>
                            <input type="text" name="groupName" class="form-control" id="group_name"
                                placeholder="Enter Group Name" maxlength="20" required>
                        </div>

                        <div class="row mt-4" style="height: 50vh">
                            <div class="col-6 border-end h-100" id="groupMembersDiv" style="overflow-y: auto;">
                                <label for="members" class="form-label text-center mb-1">Group Members</label>
                                <div class="row pe-2 my-3">
                                    <div class="col-2"><i class="fa-solid fa-user fa-xl text-warning"></i></div>
                                    <div class="col-8">You - Admin</div>
                                    <input type="hidden" name="groupMembers[]" value="{{ Auth::user()->id }}">
                                </div>
                            </div>
                            <div class="col-6 h-100" id="addMembersDiv" style="overflow-y: auto;">
                                <label for="members" class="form-label mb-1">New Members</label>
                                @foreach ($users as $user)
                                    <div class="row pe-2 my-3">
                                        <div class="col-2 "><i class="fa-solid fa-user fa-xl text-secondary"></i></div>
                                        <div class="col-8">{{ $user->name }}</div>
                                        <div class="col-2"><i class="fa-solid fa-square-plus fa-xl text-success addMember"
                                                data-user-id="{{ $user->id }}" data-user-name = "{{ $user->name }}"
                                                style="cursor: pointer"></i></div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- --------- create group modal end -------------- --}}


    {{-- --------- update group modal start -------------- --}}
    <div class="modal fade" id="updateGroupModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-dark" id="">Update Group</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="updateGroup" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="group_name" class="form-label">Group Name</label>
                            <input type="text" name="groupName" class="form-control" id="updateGroupName" value=""
                                placeholder="Enter Group Name" maxlength="20" required>
                        </div>

                        <input type="hidden" name="groupId" id="groupIdDiv">

                        <div class="row mt-4" style="height: 50vh">
                            <div class="col-6 border-end h-100" id="updateGroupMembersDiv" style="overflow-y: auto;">
                                <label for="members" class="form-label text-center mb-1">Group Members</label>
                            </div>
                            <div class="col-6 h-100" id="updateGroupaddMembersDiv" style="overflow-y: auto;">
                                <label for="members" class="form-label mb-1">New Members</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" id="deleteGroup">Delete Group</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- --------- update group modal end -------------- --}}


    {{-- --------- Voice Call modal start -------------- --}}
    <div class="modal fade" id="voiceCallModal" tabindex="-1" role="dialog" aria-labelledby="voiceCall"
        aria-hidden="true" style="--bs-modal-width: 700px;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-info-subtle">
                <div class="modal-header">
                    <h5 class="modal-title text-dark" id="">Call</h5>
                </div>
                <div class="modal-body">
                    <div id="beforeConnecting">
                        <div class="row mt-5">
                            <div class="col-12 text-center">
                                <div class="h1" id="voiceCallerName">
                                    {{-- caller or receiver name --}}
                                </div>
                            </div>
                        </div>
                        <div class="row mb-5 mt-2">
                            <div class="col-12 text-center">
                                <div class="h6" id="voiceCallStatus">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 mb-1" id="afterConnecting"   style="display: none">
                        <div class="row">
                            <div class="col-6 text-center mb-2">
                                <span class="h5" id="localPersonName"></span>
                            </div>
                            <div class="col-6 text-center mb-2">
                                <span class="h5" id="remotePersonName"></span>
                            </div>
                            <div class="col-6 text-center">
                                <video class="bg-secondary h-100" style="width:320px" id="localVideo" autoplay playsinline>
                                </video>
                            </div>
                            <div class="col-6">
                                <video class="bg-secondary h-100" style="width:320px" id="remoteVideo" autoplay playsinline>
                                </video>
                            </div>
                            <div class="h5 text-center mt-3 text-dark" id="voiceCallTimer">
                            </div>
                        </div>
                    </div>

                    <div class="row mb-2 pt-5 allButtonsRow" id="ringingCallButtons" style="display: none">
                        <div class="col-12 text-center">
                            <button type="button" class="btn btn-danger rounded-circle btn-lg p-3 voiceEndBtnClass"
                                id="voiceEndedBtn" data-call-status="" data-button-type="callEndBeforeConnect" data-receiver-id="" data-bs-dismiss="modal" aria-label="Close">
                                <i class="fa-solid fa-phone fa-xl p-1" style="transform: rotate(136deg)"></i>
                            </button>
                        </div>
                    </div>

                    <div class="row mb-2 pt-5 allButtonsRow" id="incomeCallButtons" style="display: none">
                        <div class="col-6 text-center">
                            <button type="button" class="btn btn-success rounded-circle btn-lg p-3"
                                id="voiceAcceptedBtn" data-caller-id="">
                                <i class="fa-solid fa-phone fa-xl p-1"></i>
                            </button>
                        </div>
                        <div class="col-6 text-center">
                            <button type="button" class="btn btn-danger rounded-circle btn-lg p-3" id="voiceRejectedBtn"
                                data-caller-id="" data-bs-dismiss="modal" aria-label="Close">
                                <i class="fa-solid fa-phone fa-xl p-1" style="transform: rotate(136deg)"></i>
                            </button>
                        </div>
                    </div>

                    <div class="row mb-2 pt-5 allButtonsRow" id="onCallButtons" style="display: none">
                        <div class="col-5 text-center my-auto">
                            <button class="btn btn-secondary roundded-circle btn-lg" id="micToggleBtn"  style="background-color:rgb(21 119 174)">
                                <i class="fa-solid fa-microphone"></i>
                            </button>
                        </div>
                        <div class="col-2 text-center">
                            <button type="button" class="btn btn-danger rounded-circle btn-lg p-3 voiceEndBtnClass" id="endBtnAfterCall"
                                data-receiver-id="" data-button-type="callEndAfterConnect"  data-bs-dismiss="modal" aria-label="Close">
                                <i class="fa-solid fa-phone fa-xl p-1" style="transform: rotate(136deg)"></i>
                            </button>
                        </div>
                        <div class="col-5 text-center my-auto">
                            <button class="btn btn-secondary roundded-circle btn-lg" id="cameraToggleBtn" style="background-color:rgb(21 119 174);">
                                <i class="fa-solid fa-video"></i>
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- --------- Voice Call modal end -------------- --}}

        {{-- --------- call history modal start -------------- --}}
        <div class="modal fade" id="callHistoryModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true"  style="--bs-modal-width: 400px;">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-dark" id="">Call History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="callHistoryDiv">
                </div>
            </div>
        </div>
    </div>

    {{-- --------- call history modal end -------------- --}}

    <section class="msger">
        <header class="msger-header">
            <div class="row">
                <div class="col-3  border-end border-2">
                    <div class="">
                        <i class="fas fa-comment-alt"></i> Live Chat Box
                        <span class="float-end" data-bs-toggle="modal" data-bs-target="#exampleModal">
                            <i class="fa-solid fa-pen-to-square fa-lg text-secondary"></i>
                        </span>
                    </div>
                </div>
                <div class="col-9 text-center" id="openedChat" style="display: none">
                    <span class="float-start">
                        <i class="fa-solid fa-clock-rotate-left fa-lg text-secondary" id="callHistory" style="cursor: pointer;"></i>
                    </span>
                    <span class="text-dark fw-bold" id="nameSpan"></span>
                    <span class="float-end pe-3" id="settingSpan" style="display:none"><i class="fa-solid fa-gear fa-lg"
                            id="setting-btn" style="cursor: pointer;"></i>
                    </span>
                    <span class="float-end pe-3" id="groupExitBtn" style="display:none">
                        <i class="fa-solid fa-right-from-bracket fa-lg text-danger" id="groupExit"
                            style="cursor:pointer;"></i>
                    </span>
                    <span class="float-end pe-2" id="voiceCallSpan" style="display:none">
                        <i class="fa-solid fa-phone fa-lg text-secondary" id="voiceCallBtn" style="cursor: pointer;"></i>
                    </span>
                </div>
            </div>
        </header>
        <div class="row" style="--bs-gutter-x: 0rem;">
            <div class="col-3 border-end border-3" style="height:400px;">

                <div class="row py-3 text-secondary border-bottom" data-user-id="broadcast"
                    style="cursor: pointer; --bs-gutter-x: 0rem;" id="broadcast">
                    <div class="col-lg-2" id="user_broadcast" style="color:rgb(248, 158, 55)"><i
                            class="fa-solid fa-bullhorn fa-xl ps-2"></i></div>
                    <div class="col-lg-7">
                        Broadcast Channel
                    </div>
                    <div class="col-lg-2 text-center">
                        <span class="badge text-bg-success" id="onlineUsers">{{ $onlineUsers }}</span>
                    </div>
                </div>
                @if (isset($users))
                    @foreach ($users as $user)
                        <div class="row py-3 text-secondary border-bottom user-row" data-user-id="{{ $user->id }}"
                            style="cursor: pointer; --bs-gutter-x: 0rem;">
                            <div class="col-lg-2" id="user_{{ $user->id }}"
                                style="color:@php echo ($user->status == 'online') ? 'green' : 'grey' @endphp"><i
                                    class="fa-solid fa-face-grin-stars fa-xl ps-2"></i></div>
                            <div class="col-lg-7">
                                {{ $user->name }}
                            </div>
                            <div class="col-lg-2 text-center">
                                <span class="badge text-bg-danger"
                                    data-uid="{{ $user->id }}">{{ $user->send_chats_count > 0 ? $user->send_chats_count : '' }}</span>
                            </div>
                        </div>
                    @endforeach
                @endif

                @foreach ($userGroups as $group)
                    <div class="row py-3 text-secondary border-bottom group-chats"
                        style="cursor: pointer; --bs-gutter-x: 0rem;" data-group-id="{{ $group->id }}">
                        <div class="col-lg-2 text-info" id=""><i class="fa-solid fa-user-group fa-lg ps-2"></i>
                        </div>
                        <div class="col-lg-7">
                            {{ $group->name }}
                        </div>
                        <div class="col-lg-2 text-center">
                            <span class="badge text-bg-success" id=""></span>
                        </div>
                    </div>
                @endforeach


            </div>
            <div class="col-9">
                <div id="chatBox" style="display:none;">

                    <div class="text-secondary" id="loadingMsg"
                        style="z-index: 2; background: transparent; position: absolute; left: 56%; top:27%; display:none;">
                        <i class="fa-solid fa-spinner fa-spin fa-2xl"></i>
                    </div>

                    <main class="msger-chat" id="msger-chat"
                        style="position:relative; overflow-y: scroll; height:400px; z-index:1;">
                        <!-- Chat messages will be displayed here -->
                    </main>

                    <div class="collapse card" id="mediaDiv"
                        style="position:absolute; width: 10rem; height:9rem; z-index:2; margin-top:-135px; margin-left:11px;">
                        <div class="card-body" id="mediaPreview">
                            <img class="media-btn" id="photoBtn" width="48" height="48"
                                src="https://img.icons8.com/fluency/48/stack-of-photos.png" alt="stack-of-photos" />
                            <img class="media-btn" id="videoBtn" width="48" height="48"
                                src="https://img.icons8.com/color/48/play-button-circled--v1.png"
                                alt="play-button-circled--v1" />
                            <img class="media-btn" id="audioBtn" width="48" height="48"
                                src="https://img.icons8.com/cute-clipart/64/audio-file.png" alt="audio-file" />
                            <img class="media-btn" id="documentBtn" width="48" height="48"
                                src="https://img.icons8.com/fluency/48/document.png" alt="document" />
                        </div>
                        <div class="h4 text-secondary mt-4 mx-2 text-center" id="meidaSelected" style="display: none;">
                            Media File Selected
                            <br><i class="text-danger fa-solid fa-2xl fa-circle-xmark pt-4" style="cursor: pointer;"
                                id="discartMedia"></i>
                        </div>
                    </div>

                    <div class="text-danger text-center my-4 h3" id="noMsgFound"></div>
                    <input type="hidden" id="uid" value="">
                    <input type="hidden" id="chatType" value="">

                    <form class="msger-inputarea" id="mediaForm" enctype="multipart/form-data">
                        <input type="file" accept="image/*, video/*, audio/*, application/pdf" id="mediaInput"
                            style="display: none;">

                        {{-- <button class="btn btn-primary" type="button" > --}}
                        <i class="fa-solid fa-paperclip" id="mediaToggle" style="font-size: 23px; cursor: pointer;"></i>
                        {{-- </button> --}}



                        <input type="text" class="msger-input" id="message" placeholder="Enter your message...">
                        <button type="submit" class="msger-send-btn" id="sendBtn">Send</button>
                    </form>

                </div>
                <div id="noChat" class="pt-5">
                    <h6 class="text-danger text-center">Select anyone to whom you want to chat!</h6>
                </div>
            </div>

        </div>
    </section>
    <div>
        {{-- <video class="bg-warning h-50" style="width:340px" id="localVideo" autoplay muted playsinline>
        </video>
        <video class="bg-warning h-50" style="width:340px" id="remoteVideo" autoplay playsinline>
        </video> --}}
    </div>
@endsection

@push('styles')
    <link href="{{ asset('css/chat.css') }}" rel="stylesheet">
    <style>
        .media-btn {
            cursor: pointer;
            margin: 5px;
        }
    </style>
@endpush

@push('scripts')
    {{-- <script src="https://js.pusher.com/4.1/pusher.min.js"></script> --}}
    {{-- <script src="https://cdn.jsdelivr.net/npm/simple-peer@16"></script> --}}
    <script src="https://cdn.socket.io/4.3.2/socket.io.min.js"></script>
    <!-- Include Axios via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>



    <script>
        loginUserId = {{ auth()->id() }};
        var dynamicURL = "{{ asset('uploads/') }}";
        const baseUrl = '{{ url('/') }}';
    </script>

    <script src="{{ asset('js/chat.js') }}"></script>
@endpush
