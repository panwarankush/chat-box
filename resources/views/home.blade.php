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
    <section class="msger">
        <header class="msger-header">
            <div class="msger-header-title">
                <i class="fas fa-comment-alt"></i> Live Chat Box
            </div>
            <div class="msger-header-options">
                <span><i class="fas fa-cog"></i></span>
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
                        <span class="badge text-bg-danger" data-uid="0"></span>
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

    <script>
        loginUserId = {{ auth()->id() }};
        var dynamicURL = "{{ asset('uploads/') }}";
    </script>

    <script src="{{ asset('js/chat.js') }}"></script>
@endpush
