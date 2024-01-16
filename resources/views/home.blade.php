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
        const msgerForm = get(".msger-inputarea");
        const msgerInput = get(".msger-input");
        const msgerChat = get(".msger-chat");
        var userId;
        var offset = 5; // Initial offset
        var limit = 5; // Number of messages to fetch per request
        var stopLoadMsg = true;

        const BOT_IMG = "https://img.icons8.com/color/48/user.png";
        const PERSON_IMG = "https://img.icons8.com/color/48/guest-male--v1.png";

        loginUserId = {{ auth()->id() }};


        //---------------------------------------------sharing multiple types of media----------------------//
        // Media sharing button click events
        $('#photoBtn').on('click', function() {
            $('#mediaInput').attr('accept', 'image/*');
            $('#mediaInput').click();
        });

        $('#videoBtn').on('click', function() {
            $('#mediaInput').attr('accept', 'video/*');
            $('#mediaInput').click();
        });

        $('#audioBtn').on('click', function() {
            $('#mediaInput').attr('accept', 'audio/*');
            $('#mediaInput').click();
        });

        $('#documentBtn').on('click', function() {
            $('#mediaInput').attr('accept', '.doc, .docx, .pdf, .ppt, .pptx');
            $('#mediaInput').click();
        });

        $('#mediaToggle').on('click', function() {
            $('#mediaDiv').toggle();
        })

        // Handle file selection
        $('#mediaInput').on('change', function() {
            $('#mediaPreview').hide();
            $('#meidaSelected').show();

        });

        $('#discartMedia').on('click', function() {
            $('#mediaPreview').show();
            $('#meidaSelected').hide();
            $('#mediaInput').val(null);

        })


        $(document).ready(function() {


            // ----------------------------------ajax request to open chat of a particular user ----------------------------------------//
            $('.user-row').click(function() {
                userId = $(this).data('user-id');
                $('span[data-uid="' + userId + '"]').text('')
                // console.log(userId);
                // return;
                $.ajax({
                    type: "POST",
                    url: '/chat',
                    data: {
                        uid: userId,
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        offset = 5;
                        stopLoadMsg = true;
                        $('#msger-chat').empty();
                        $('#uid').val(userId);
                        $('#chatBox').show();
                        $('#noChat').hide();
                        $('.user-row').css('background-color', 'white');
                        $('#broadcast').css('background-color', 'white');
                        $('.user-row[data-user-id="' + userId + '"]').css('background-color',
                            '#579ffb5c');

                        if (response.chats && response.chats.length > 0) {
                            response.chats.reverse();
                            $('#noMsgFound').hide();

                            $.each(response.chats, function(index, chat) {
                                var msgType;
                                var msg;
                                if (chat.message == null) {
                                    msgType = 'media';
                                    msg = chat.media;

                                } else if (chat.media == null) {
                                    msgType = 'text';
                                    msg = chat.message;

                                } else {
                                    msgType = 'both';
                                    msg = [chat.message, chat.media];

                                }

                                if (chat.sender.id !== userId) {
                                    appendMessage(chat.sender.name, PERSON_IMG, "right",
                                        msg, chat.created_at, chat.receipt,
                                        "beforeend", msgType);
                                } else {
                                    appendMessage(chat.sender.name, BOT_IMG, "left",
                                        msg, chat.created_at, chat.receipt,
                                        "beforeend", msgType);
                                }
                            });
                            msgerChat.scrollTop = msgerChat.scrollHeight;

                        } else {
                            $('#noMsgFound').show();
                            $('#noMsgFound').text('no previous chats found!!')

                        }
                    },
                    error: function(err) {
                        alert(err.statusText);
                    }
                });
            });

            window.Echo.channel('chat').listen('NewChatMessage', (event) => {
                // console.log(userId);
                $('#noMsgFound').hide();
                if (event.receiver == loginUserId && event.sender == userId) {
                    var notificationMsg;
                    if (event.msgType == 'text') {
                        notificationMsg = event.message;
                    } else if (event.msgType == 'media') {
                        notificationMsg = 'Media File Received!!';

                    } else if (event.msgType == 'both') {
                        notificationMsg = event.message[0];
                    }

                    appendMessage(event.senderName, BOT_IMG, "left",
                        event.message, event.msgTime, 'read', "beforeend", event.msgType);
                    msgerChat.scrollTop = msgerChat.scrollHeight;
                }
                if (event.receiver == loginUserId) {
                    toastr.info(notificationMsg, 'From ' + event.senderName);
                }

            });



            //----------------------------------ajax request to send message to a particular user ----------------------------------------//
            $('#sendBtn').on('click', function(e) {
                e.preventDefault();
                var message = $('#message').val();
                var receiver = $('#uid').val();
                var fileInput = $("#mediaInput")[0].files[0];

                if ((fileInput || message !== '') && receiver !== '') {

                    $('#noMsgFound').hide();
                    $('#mediaPreview').show();
                    $('#meidaSelected').hide();
                    $('#mediaDiv').hide();

                    const formData = new FormData();
                    formData.append('message', message);
                    formData.append('receiver', receiver);
                    formData.append('mediaInput', fileInput);

                    $.ajax({
                        type: "POST",
                        url: '/dashboard',
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            $('#uid').val(receiver);
                            $('#mediaForm')[0].reset();
                            $.each(response.chats, function(index, chat) {
                                if (chat.message == null) {
                                    appendMessage(chat.sender.name, PERSON_IMG, "right",
                                        chat.media, chat.created_at, chat.receipt,
                                        "beforeend", 'media');
                                } else if (chat.media == null) {
                                    appendMessage(chat.sender.name, PERSON_IMG, "right",
                                        chat.message, chat.created_at, chat.receipt,
                                        "beforeend", 'text');
                                } else {
                                    appendMessage(chat.sender.name, PERSON_IMG, "right",
                                        [chat.message, chat.media], chat.created_at,
                                        chat.receipt,
                                        "beforeend", 'both');
                                }
                                msgerChat.scrollTop = msgerChat.scrollHeight;
                            });
                        },
                        error: function(err) {
                            alert(err.statusText);
                        }
                    });
                }

            });
        });

        // -----------------------------------Infinite scroll to check old messages------------------------------//
        var previousY = 0;
        var currentScrollPosition = 0;

        var chatElement = document.getElementById('msger-chat');
        chatElement.addEventListener('scroll', function(e) {
            var currentY = chatElement.scrollTop;
            if (currentY < previousY && currentY == 0) {
                // console.log('load more!!!!');
                currentScrollPosition = chatElement.scrollHeight - currentY;
                if (stopLoadMsg) {
                    loadOldMessages()
                }

            }
            previousY = currentY;
        });


        function loadOldMessages() {
            var isLoading = false;
            $('#loadingMsg').show();

            if (isLoading) {
                return;
            }
            isLoading = true;

            userId = $('#uid').val();

            // Make an AJAX request to fetch old messages
            $.ajax({
                url: '/get-old-messages/' + userId + '/' + offset + '/' + limit,
                method: 'GET',
                success: function(response) {
                    if (response.chats && response.chats.length > 0) {
                        // response.chats.reverse();
                        // $('#noMsgFound').hide();
                        $.each(response.chats, function(index, chat) {
                            var msgType;
                            var msg;
                            if (chat.message == null) {
                                msgType = 'media';
                                msg = chat.media;

                            } else if (chat.media == null) {
                                msgType = 'text';
                                msg = chat.message;

                            } else {
                                msgType = 'both';
                                msg = [chat.message, chat.media];

                            }
                            if (chat.sender.id != userId) {
                                appendMessage(chat.sender.name, PERSON_IMG, "right",
                                    msg, chat.created_at, chat.receipt,
                                    "afterbegin", msgType);
                            } else {
                                appendMessage(chat.sender.name, BOT_IMG, "left",
                                    msg, chat.created_at, chat.receipt,
                                    "afterbegin", msgType);
                            }
                        });
                        offset += limit; // Increment offset for the next request
                        msgerChat.scrollTop = msgerChat.scrollHeight - currentScrollPosition;

                    } else {
                        // $('#noMsgFound').show();
                        // $('#noMsgFound').text('no previous chats found!!')
                        stopLoadMsg = false;
                        msgerChat.insertAdjacentHTML('afterbegin',
                            '<h5 class="text-center text-danger py-4">No More Old Messages Found !!</h5>');

                    }

                },
                complete: function() {
                    isLoading = false;
                    $('#loadingMsg').hide();
                }
            });

        }

        function appendMessage(name, img, side, text, chatTime, status, position, type) {
            var tick = '';
            var msg = '';
            var filetype;
            if (side === 'right') {
                if (status === 'read') {
                    tick =
                        '<span class="d-flex justify-content-end tick" style="color: #a8ff21;"> <i class="fa-solid fa-check-double"></i> </span>';
                } else if (status === 'delivered') {
                    tick =
                        '<span class="d-flex justify-content-end tick" style="color: #cdcdcd;"> <i class="fa-solid fa-check-double"></i> </span>';
                } else {
                    tick =
                        '<span class="d-flex justify-content-end tick" style="color: #cdcdcd;"> <i class="fa-solid fa-check"></i> </span>';
                }
            }

            if (type == 'text') {
                msg = '<div class="msg-text">' + text + '</div>';

            } else if (type == 'media') {
                msg = '<div class="msg-text mb-2">' + showMedia(text) + '</div>';

            } else if (type == 'both') {
                msg = '<div class="msg-text mb-2">' + showMedia(text[1]) + '<div class="my-2">' + text[0] + '</div></div>';
            }

            const msgHTML = `
                <div class="msg ${side}-msg">
                <div class="msg-img" style="background-image: url(${img})"></div>

                <div class="msg-bubble">
                    <div class="msg-info">
                    <div class="msg-info-name">${name}</div>
                    <div class="msg-info-time">${formatDate(chatTime)}</div>
                    </div>
                    ${msg}
                    ${tick}
                </div>
                </div>
            `;

            msgerChat.insertAdjacentHTML(position, msgHTML);
            // msgerChat.scrollTop += 500;
        }

        // get extension from string
        function getFileExtension(filename) {
            return filename.slice((filename.lastIndexOf(".") - 1 >>> 0) + 2);
        }

        //show media to that chat message
        function showMedia(mediaFileName) {
            var extension = getFileExtension(mediaFileName).toLowerCase();
            if (["jpg", "jpeg", "png", "gif", "webp"].includes(extension)) {
                return `<img class="rounded" src="{{ asset('uploads/`+ mediaFileName +`') }}" alt="Image Chat" width="300" height="200">`;

            } else if (["mp4", "mov", "wmv", "mkv"].includes(extension)) {
                var videoFormats = {
                    "mp4": "mp4",
                    "mov": "quicktime",
                    "wmv": "x-ms-wmv",
                    "mkv": "x-matroska"
                };
                return ` <video width="300" height="200" controls><source src="{{ asset('uploads/`+ mediaFileName +`') }}" type="video/` +
                    videoFormats[extension] + `">
                        Your browser does not support the video tag.
                        </video> `;

            } else if (["mp3", "wav", "aac"].includes(extension)) {
                var audioFormats = {
                    "mp3": "mpeg",
                    "wav": "wav",
                    "aac": "aac",
                };
                return ` <audio controls><source src="{{ asset('uploads/`+ mediaFileName +`') }}" type="audio/` +
                    audioFormats[extension] + `">
                        Your browser does not support the video tag.
                        </audio> `;

            } else if (["doc", "docx", "pdf", "ppt", "pptx", "xls", "xlsx"].includes(extension)) {
                return ` <h4><a class="text-dark" href="{{ asset('uploads/`+ mediaFileName +`') }}" target="_blank" style="text-decoration:none"><i class="fa-solid fa-file-arrow-down"></i> Click To Download Document</a></h4>`;
            }
        }

        // Utils
        function get(selector, root = document) {
            return root.querySelector(selector);
        }

        function formatDate(datetimeString) {
            const dateTime = new Date(datetimeString);
            const hours = dateTime.getHours().toString().padStart(2, '0');
            const minutes = dateTime.getMinutes().toString().padStart(2, '0');

            return `${hours}:${minutes}`;
        }

        // -----------------------------------change user status online/offline------------------------------//

        addEventListener('beforeunload', function(event) {
            updateStatus('offline')
            // event.returnValue = 'You have unsaved changes.';
        });
        // window.onbeforeunload = function(e) {
        //     updateStatus('offline')
        // };

        window.addEventListener('load', function() {
            updateStatus('online')
        })


        function updateStatus(state) {
            $.ajax({
                type: "POST",
                url: '/update-status',
                data: {
                    status: state,
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {}
            });
        }

        window.Echo.channel('userStatusChannel').listen('UserStatus', (event) => {
            if (event.status == 'online') {
                $('#user_' + event.userId).css('color', 'green');
            } else {
                $('#user_' + event.userId).css('color', 'grey');
            }
        });

        // -----------------------------------change user status online/offline------------------------------//

        window.Echo.channel('messageReceipt').listen('MessageStatus', (event) => {
            if (event.receiver == loginUserId && event.sender == userId) {
                if (event.status == 'read') {
                    $('.tick').css('color', '#a8ff21').html(' <i class="fa-solid fa-check-double"></i> ');
                } else {
                    $('.tick').css('color', '#cdcdcd').html(' <i class="fa-solid fa-check-double"></i> ');
                }
            } else if (event.receiver == 'all' && event.sender == userId) {
                $('.fa-check').removeClass('fa-check').addClass('fa-check-double');
            }
        });


        // -----------------------------------Unread messages count------------------------------//

        window.Echo.channel('unreadMessagesChannel').listen('UnreadMessagesEvent', (event) => {
            if (event.receiver == loginUserId) {
                // console.log('msg received');
                $('span[data-uid="' + event.sender + '"]').text(event.messageCount)
            }
        });


        //==================================== Broadcast Channel Code =================================//

        $('#broadcast').click(function() {
            var uidd =$('#uid').val();
            $.ajax({
                type: "GET",
                url: '/broadcast',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    offset = 5;
                    stopLoadMsg = true;
                    $('#msger-chat').empty();
                    $('#chatBox').show();
                    $('#noChat').hide();
                    $('#uid').val(0);
                    $('.user-row').css('background-color', 'white');
                    $('#broadcast').css('background-color',
                        '#579ffb5c');

                    if (response.chats && response.chats.length > 0) {
                        response.chats.reverse();
                        $('#noMsgFound').hide();

                        $.each(response.chats, function(index, chat) {
                            var msgType;
                            var msg;
                            if (chat.message == null) {
                                msgType = 'media';
                                msg = chat.media;

                            } else if (chat.media == null) {
                                msgType = 'text';
                                msg = chat.message;

                            } else {
                                msgType = 'both';
                                msg = [chat.message, chat.media];

                            }

                            if (chat.sender.id == loginUserId) {
                                appendMessage(chat.sender.name, PERSON_IMG, "right",
                                    msg, chat.created_at, chat.receipt,
                                    "beforeend", msgType);
                            } else {
                                appendMessage(chat.sender.name, BOT_IMG, "left",
                                    msg, chat.created_at, chat.receipt,
                                    "beforeend", msgType);
                            }
                        });
                        msgerChat.scrollTop = msgerChat.scrollHeight;

                    } else {
                        $('#noMsgFound').show();
                        $('#noMsgFound').text('no previous chats found!!')

                    }
                },
                error: function(err) {
                    alert(err.statusText);
                }
            });
        });
    </script>
@endpush
