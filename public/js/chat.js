const msgerForm = get(".msger-inputarea");
const msgerInput = get(".msger-input");
const msgerChat = get(".msger-chat");
var userId;
var offset = 5; // Initial offset
var limit = 5; // Number of messages to fetch per request
var stopLoadMsg = true;
var chatType;

const BOT_IMG = "https://img.icons8.com/color/48/user.png";
const PERSON_IMG = "https://img.icons8.com/color/48/guest-male--v1.png";

//---------------------------------------------sharing multiple types of media----------------------//
// Media sharing button click events
$("#photoBtn").on("click", function () {
    $("#mediaInput").attr("accept", "image/*");
    $("#mediaInput").click();
});

$("#videoBtn").on("click", function () {
    $("#mediaInput").attr("accept", "video/*");
    $("#mediaInput").click();
});

$("#audioBtn").on("click", function () {
    $("#mediaInput").attr("accept", "audio/*");
    $("#mediaInput").click();
});

$("#documentBtn").on("click", function () {
    $("#mediaInput").attr("accept", ".doc, .docx, .pdf, .ppt, .pptx");
    $("#mediaInput").click();
});

$("#mediaToggle").on("click", function () {
    $("#mediaDiv").toggle();
});

// Handle file selection
$("#mediaInput").on("change", function () {
    $("#mediaPreview").hide();
    $("#meidaSelected").show();
});

$("#discartMedia").on("click", function () {
    $("#mediaPreview").show();
    $("#meidaSelected").hide();
    $("#mediaInput").val(null);
});

$(document).ready(function () {
    // ----------------------------------ajax request to open chat of a particular user ----------------------------------------//
    $(".user-row").click(function () {
        userId = $(this).data("user-id");
        $('#chatType').val('personal');
        $('span[data-uid="' + userId + '"]').text("");
        // console.log(userId);
        // return;
        $.ajax({
            type: "POST",
            url: "/chat",
            data: {
                uid: userId,
            },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                offset = 5;
                stopLoadMsg = true;
                $("#msger-chat").empty();
                $("#uid").val(userId);
                $("#chatBox").show();
                $("#noChat").hide();
                $(".user-row").css("background-color", "white");
                $(".group-chats").css("background-color", "white");
                $("#broadcast").css("background-color", "white");
                $('.user-row[data-user-id="' + userId + '"]').css(
                    "background-color",
                    "#579ffb5c"
                );

                if (response.chats && response.chats.length > 0) {
                    response.chats.reverse();
                    $("#noMsgFound").hide();

                    $.each(response.chats, function (index, chat) {
                        var msgType;
                        var msg;
                        if (chat.message == null) {
                            msgType = "media";
                            msg = chat.media;
                        } else if (chat.media == null) {
                            msgType = "text";
                            msg = chat.message;
                        } else {
                            msgType = "both";
                            msg = [chat.message, chat.media];
                        }

                        if (chat.sender.id !== userId) {
                            appendMessage(
                                chat.sender.name,
                                PERSON_IMG,
                                "right",
                                msg,
                                chat.created_at,
                                chat.receipt,
                                "beforeend",
                                msgType
                            );
                        } else {
                            appendMessage(
                                chat.sender.name,
                                BOT_IMG,
                                "left",
                                msg,
                                chat.created_at,
                                chat.receipt,
                                "beforeend",
                                msgType
                            );
                        }
                    });
                    msgerChat.scrollTop = msgerChat.scrollHeight;
                } else {
                    $("#noMsgFound").show();
                    $("#noMsgFound").text("no previous chats found!!");
                }
            },
            error: function (err) {
                alert(err.statusText);
            },
        });
    });
    //------------------------------message toaster and append messages on receiver side---------------------------------//
    window.Echo.channel("chat").listen("NewChatMessage", (event) => {
        // console.log(userId);
        $("#noMsgFound").hide();
        var openGroup = $('#uid').val();
        var notificationMsg;
        if (event.msgType == "text") {
            notificationMsg = event.message;
        } else if (event.msgType == "media") {
            notificationMsg = "Media File Received!!";
        } else if (event.msgType == "both") {
            notificationMsg = event.message[0];
        }
        if (
            (event.receiver == loginUserId && event.sender == userId) ||
            (event.receiver == 0 && userId == 0 && event.sender != loginUserId) ||
            (event.sender != loginUserId && event.receiver == openGroup)
        ) {
            appendMessage(
                event.senderName,
                BOT_IMG,
                "left",
                event.message,
                event.msgTime,
                "read",
                "beforeend",
                event.msgType
            );
            msgerChat.scrollTop = msgerChat.scrollHeight;
        }
        if (
            (event.receiver == loginUserId || event.receiver == 0) &&
            event.sender != loginUserId
        ) {
            toastr.info(notificationMsg, "From " + event.senderName);
        }
    });

    //----------------------------------ajax request to send message to a particular user ----------------------------------------//
    $("#sendBtn").on("click", function (e) {
        e.preventDefault();
        var message = $("#message").val();
        var receiver = $("#uid").val();
        var fileInput = $("#mediaInput")[0].files[0];
        chatType = $('#chatType').val();

        if ((fileInput || message !== "") && receiver !== "" && chatType == 'personal') {
            $("#noMsgFound").hide();
            $("#mediaPreview").show();
            $("#meidaSelected").hide();
            $("#mediaDiv").hide();

            const formData = new FormData();
            formData.append("message", message);
            formData.append("receiver", receiver);
            formData.append("mediaInput", fileInput);

            $.ajax({
                type: "POST",
                url: "/dashboard",
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content"
                    ),
                },
                success: function (response) {
                    $("#uid").val(receiver);
                    $("#mediaForm")[0].reset();
                    $.each(response.chats, function (index, chat) {
                        if (chat.message == null) {
                            appendMessage(
                                chat.sender.name,
                                PERSON_IMG,
                                "right",
                                chat.media,
                                chat.created_at,
                                chat.receipt,
                                "beforeend",
                                "media"
                            );
                        } else if (chat.media == null) {
                            appendMessage(
                                chat.sender.name,
                                PERSON_IMG,
                                "right",
                                chat.message,
                                chat.created_at,
                                chat.receipt,
                                "beforeend",
                                "text"
                            );
                        } else {
                            appendMessage(
                                chat.sender.name,
                                PERSON_IMG,
                                "right",
                                [chat.message, chat.media],
                                chat.created_at,
                                chat.receipt,
                                "beforeend",
                                "both"
                            );
                        }
                        msgerChat.scrollTop = msgerChat.scrollHeight;
                    });
                },
                error: function (err) {
                    alert(err.statusText);
                },
            });
        }
    });
});

// -----------------------------------Infinite scroll to check old messages------------------------------//
var previousY = 0;
var currentScrollPosition = 0;

var chatElement = document.getElementById("msger-chat");
chatElement.addEventListener("scroll", function (e) {
    chatType = $('#chatType').val();
    var currentY = chatElement.scrollTop;
    if (currentY < previousY && currentY == 0) {
        // console.log('load more!!!!');
        currentScrollPosition = chatElement.scrollHeight - currentY;
        if (stopLoadMsg)
        {
            console.log(chatType);
            if(chatType != 'group'){
                loadOldMessages();
            }else if(chatType){
                loadOldGroupChats();
            }
        }
    }
    previousY = currentY;
});

//Load old chats of users and channel
function loadOldMessages() {
    var isLoading = false;
    $("#loadingMsg").show();

    if (isLoading) {
        return;
    }
    isLoading = true;
    userId = $("#uid").val();

    $.ajax({
        url: "/get-old-messages/" + userId + "/" + offset + "/" + limit,
        method: "GET",
        success: function (response) {
            if (response.chats && response.chats.length > 0) {
                // response.chats.reverse();
                // $('#noMsgFound').hide();
                $.each(response.chats, function (index, chat) {
                    var msgType;
                    var msg;
                    if (chat.message == null) {
                        msgType = "media";
                        msg = chat.media;
                    } else if (chat.media == null) {
                        msgType = "text";
                        msg = chat.message;
                    } else {
                        msgType = "both";
                        msg = [chat.message, chat.media];
                    }
                    if (chat.sender.id == loginUserId) {
                        appendMessage(
                            chat.sender.name,
                            PERSON_IMG,
                            "right",
                            msg,
                            chat.created_at,
                            chat.receipt,
                            "afterbegin",
                            msgType
                        );
                    } else {
                        appendMessage(
                            chat.sender.name,
                            BOT_IMG,
                            "left",
                            msg,
                            chat.created_at,
                            chat.receipt,
                            "afterbegin",
                            msgType
                        );
                    }
                });
                offset += limit; // Increment offset for the next request
                msgerChat.scrollTop =
                    msgerChat.scrollHeight - currentScrollPosition;
            } else {
                // $('#noMsgFound').show();
                // $('#noMsgFound').text('no previous chats found!!')
                stopLoadMsg = false;
                msgerChat.insertAdjacentHTML(
                    "afterbegin",
                    '<h5 class="text-center text-danger py-4">No More Old Messages Found !!</h5>'
                );
            }
        },
        complete: function () {
            isLoading = false;
            $("#loadingMsg").hide();
        },
    });
}

function loadOldGroupChats(){
    var isLoading = false;
    $("#loadingMsg").show();

    if (isLoading) {
        return;
    }
    isLoading = true;
    groupId = $("#uid").val();

    $.ajax({
        url: "/get-old-groupChats/" + groupId + "/" + offset + "/" + limit,
        method: "GET",
        success: function (response) {
            if (response.chats && response.chats.length > 0) {
                // response.chats.reverse();
                // $('#noMsgFound').hide();
                $.each(response.chats, function (index, chat) {
                    var msgType;
                    var msg;
                    if (chat.message == null) {
                        msgType = "media";
                        msg = chat.media;
                    } else if (chat.media == null) {
                        msgType = "text";
                        msg = chat.message;
                    } else {
                        msgType = "both";
                        msg = [chat.message, chat.media];
                    }
                    if (chat.sender.id == loginUserId) {
                        appendMessage(
                            chat.sender.name,
                            PERSON_IMG,
                            "right",
                            msg,
                            chat.created_at,
                            chat.receipt,
                            "afterbegin",
                            msgType
                        );
                    } else {
                        appendMessage(
                            chat.sender.name,
                            BOT_IMG,
                            "left",
                            msg,
                            chat.created_at,
                            chat.receipt,
                            "afterbegin",
                            msgType
                        );
                    }
                });
                offset += limit; // Increment offset for the next request
                msgerChat.scrollTop =
                    msgerChat.scrollHeight - currentScrollPosition;
            } else {
                // $('#noMsgFound').show();
                // $('#noMsgFound').text('no previous chats found!!')
                stopLoadMsg = false;
                msgerChat.insertAdjacentHTML(
                    "afterbegin",
                    '<h5 class="text-center text-danger py-4">No More Old Messages Found !!</h5>'
                );
            }
        },
        complete: function () {
            isLoading = false;
            $("#loadingMsg").hide();
        },
    });
}

function appendMessage(
    name,
    img,
    side,
    text,
    chatTime,
    status,
    position,
    type
) {
    var tick = "";
    var msg = "";
    var filetype;
    if (side === "right") {
        if (status === "read") {
            tick =
                '<span class="d-flex justify-content-end tick" style="color: #a8ff21;"> <i class="fa-solid fa-check-double"></i> </span>';
        } else if (status === "delivered") {
            tick =
                '<span class="d-flex justify-content-end tick" style="color: #cdcdcd;"> <i class="fa-solid fa-check-double"></i> </span>';
        } else {
            tick =
                '<span class="d-flex justify-content-end tick" style="color: #cdcdcd;"> <i class="fa-solid fa-check"></i> </span>';
        }
    }

    if (type == "text") {
        msg = '<div class="msg-text">' + text + "</div>";
    } else if (type == "media") {
        msg = '<div class="msg-text mb-2">' + showMedia(text) + "</div>";
    } else if (type == "both") {
        msg =
            '<div class="msg-text mb-2">' +
            showMedia(text[1]) +
            '<div class="my-2">' +
            text[0] +
            "</div></div>";
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
    return filename.slice(((filename.lastIndexOf(".") - 1) >>> 0) + 2);
}

//show media to that chat message
function showMedia(mediaFileName) {
    var extension = getFileExtension(mediaFileName).toLowerCase();
    var fileUrl = dynamicURL + "/" + mediaFileName;
    if (["jpg", "jpeg", "png", "gif", "webp"].includes(extension)) {
        return (
            `<img class="rounded" src="` +
            fileUrl +
            `" alt="Image Chat" width="300" height="200">`
        );
    } else if (["mp4", "mov", "wmv", "mkv"].includes(extension)) {
        var videoFormats = {
            mp4: "mp4",
            mov: "quicktime",
            wmv: "x-ms-wmv",
            mkv: "x-matroska",
        };
        return (
            ` <video width="300" height="200" controls><source src="` +
            fileUrl +
            `" type="video/` +
            videoFormats[extension] +
            `">
                Your browser does not support the video tag.
                </video> `
        );
    } else if (["mp3", "wav", "aac"].includes(extension)) {
        var audioFormats = {
            mp3: "mpeg",
            wav: "wav",
            aac: "aac",
        };
        return (
            ` <audio controls><source src="` +
            fileUrl +
            `" type="audio/` +
            audioFormats[extension] +
            `">
                Your browser does not support the video tag.
                </audio> `
        );
    } else if (
        ["doc", "docx", "pdf", "ppt", "pptx", "xls", "xlsx"].includes(extension)
    ) {
        return (
            ` <h4><a class="text-dark" href="` +
            fileUrl +
            `" target="_blank" style="text-decoration:none"><i class="fa-solid fa-file-arrow-down"></i> Click To Download Document</a></h4>`
        );
    }
}

// Utils
function get(selector, root = document) {
    return root.querySelector(selector);
}

function formatDate(datetimeString) {
    const dateTime = new Date(datetimeString);
    const hours = dateTime.getHours().toString().padStart(2, "0");
    const minutes = dateTime.getMinutes().toString().padStart(2, "0");

    return `${hours}:${minutes}`;
}

// -----------------------------------change user status online/offline------------------------------//

// window.onbeforeunload = function(e) {
//     updateStatus('offline')
// };

addEventListener("beforeunload", function (event) {
    updateStatus("offline");
    // event.returnValue = 'You have unsaved changes.';
});
window.addEventListener("load", function () {
    updateStatus("online");
});

function updateStatus(state) {
    $.ajax({
        type: "POST",
        url: "/update-status",
        data: {
            status: state,
        },
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (response) {},
    });
}

window.Echo.channel("userStatusChannel").listen("UserStatus", (event) => {
    var onlineUsersValue = parseInt($("#onlineUsers").text(), 10);
    if (event.status == "online") {
        onlineUsersValue += 1;
        $("#user_" + event.userId).css("color", "green");
    } else {
        onlineUsersValue -= 1;
        $("#user_" + event.userId).css("color", "grey");
    }
    $("#onlineUsers").text(onlineUsersValue);
});

// -----------------------------------change user status online/offline------------------------------//

window.Echo.channel("messageReceipt").listen("MessageStatus", (event) => {
    if (event.receiver == loginUserId && event.sender == userId) {
        if (event.status == "read") {
            $(".tick")
                .css("color", "#a8ff21")
                .html(' <i class="fa-solid fa-check-double"></i> ');
        } else {
            $(".tick")
                .css("color", "#cdcdcd")
                .html(' <i class="fa-solid fa-check-double"></i> ');
        }
    } else if (event.receiver == "all" && event.sender == userId) {
        $(".fa-check").removeClass("fa-check").addClass("fa-check-double");
    }
});

// -----------------------------------Unread messages count------------------------------//

window.Echo.channel("unreadMessagesChannel").listen(
    "UnreadMessagesEvent",
    (event) => {
        if (event.receiver == loginUserId) {
            // console.log('msg received');
            $('span[data-uid="' + event.sender + '"]').text(event.messageCount);
        }
    }
);

//==================================== Broadcast Channel Code =================================//

$("#broadcast").click(function () {
    $('#chatType').val('channel');
    userId = 0;
    $.ajax({
        type: "GET",
        url: "/broadcast",
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (response) {
            offset = 5;
            stopLoadMsg = true;
            $("#msger-chat").empty();
            $("#chatBox").show();
            $("#noChat").hide();
            $("#uid").val(0);
            $(".user-row").css("background-color", "white");
            $(".group-chats").css("background-color", "white");
            $("#broadcast").css("background-color", "#579ffb5c");

            if (response.chats && response.chats.length > 0) {
                response.chats.reverse();
                $("#noMsgFound").hide();

                $.each(response.chats, function (index, chat) {
                    var msgType;
                    var msg;
                    if (chat.message == null) {
                        msgType = "media";
                        msg = chat.media;
                    } else if (chat.media == null) {
                        msgType = "text";
                        msg = chat.message;
                    } else {
                        msgType = "both";
                        msg = [chat.message, chat.media];
                    }

                    if (chat.sender.id == loginUserId) {
                        appendMessage(
                            chat.sender.name,
                            PERSON_IMG,
                            "right",
                            msg,
                            chat.created_at,
                            chat.receipt,
                            "beforeend",
                            msgType
                        );
                    } else {
                        appendMessage(
                            chat.sender.name,
                            BOT_IMG,
                            "left",
                            msg,
                            chat.created_at,
                            chat.receipt,
                            "beforeend",
                            msgType
                        );
                    }
                });
                msgerChat.scrollTop = msgerChat.scrollHeight;
            } else {
                $("#noMsgFound").show();
                $("#noMsgFound").text("no previous chats found!!");
            }
        },
        error: function (err) {
            alert(err.statusText);
        },
    });
});

//----------ajax request to send message in a channel ------------------//
$("#sendBtn").on("click", function (e) {
    e.preventDefault();
    var message = $("#message").val();
    var receiver = $("#uid").val();
    var fileInput = $("#mediaInput")[0].files[0];

    if ((fileInput || message !== "") && receiver !== "" && receiver == 0) {
        $("#noMsgFound").hide();
        $("#mediaPreview").show();
        $("#meidaSelected").hide();
        $("#mediaDiv").hide();

        const formData = new FormData();
        formData.append("message", message);
        formData.append("mediaInput", fileInput);

        $.ajax({
            type: "POST",
            url: "/broadcast",
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                $("#uid").val(receiver);
                $("#mediaForm")[0].reset();
                $.each(response.chats, function (index, chat) {
                    if (chat.message == null) {
                        appendMessage(
                            chat.sender.name,
                            PERSON_IMG,
                            "right",
                            chat.media,
                            chat.created_at,
                            chat.receipt,
                            "beforeend",
                            "media"
                        );
                    } else if (chat.media == null) {
                        appendMessage(
                            chat.sender.name,
                            PERSON_IMG,
                            "right",
                            chat.message,
                            chat.created_at,
                            chat.receipt,
                            "beforeend",
                            "text"
                        );
                    } else {
                        appendMessage(
                            chat.sender.name,
                            PERSON_IMG,
                            "right",
                            [chat.message, chat.media],
                            chat.created_at,
                            chat.receipt,
                            "beforeend",
                            "both"
                        );
                    }
                    msgerChat.scrollTop = msgerChat.scrollHeight;
                });
            },
            error: function (err) {
                alert(err.statusText);
            },
        });
    }
});

//====================================== Group feature ========================================//

$('#addMembersDiv').on('click', '.addMember', function() {
    // Retrieve user information from data attributes
    var userId = $(this).data("user-id");
    var userName = $(this).data("user-name");

    // Use the retrieved information as needed
    console.log("User ID: " + userId);
    console.log("User Name: " + userName);

    // Remove the clicked row from the DOM
    $(this).closest(".row").remove();

    var groupMemberDiv =
        `<div class="row pe-2 my-3">
                        <div class="col-2"><i class="fa-solid fa-user fa-xl text-warning"></i></div>
                        <div class="col-8">` +
        userName +
        `</div>
                        <div class="col-2"><i class="fa-solid fa-square-xmark text-danger fa-xl removeMember"
                        data-user-id="`+userId+`" data-user-name = "`+userName+`"    style="cursor: pointer"></i></div>
                                <input type="hidden" name="groupMembers[]" value="` +
        userId +
        `">
                        </div>`;
    $("#groupMembersDiv").append(groupMemberDiv);
});


$('#groupMembersDiv').on('click', '.removeMember', function() {
    // Retrieve user information from the current row
    var userId = $(this).data("user-id");
    var userName = $(this).data("user-name");

    // Use the retrieved information as needed
    console.log("User ID: " + userId);
    console.log("User Name: " + userName);

    var addMemberDiv = `<div class="row pe-2 my-3">
                        <div class="col-2 "><i class="fa-solid fa-user fa-xl text-secondary"></i></div>
                        <div class="col-8">`+userName+`</div>
                        <div class="col-2"><i class="fa-solid fa-square-plus fa-xl text-success addMember"
                                data-user-id="`+userId+`" data-user-name = "`+userName+`" style="cursor: pointer"></i></div>
                    </div>`;

    // Remove the clicked row from the DOM
    $(this).closest(".row").remove();
    $("#addMembersDiv").append(addMemberDiv);
});


//----------------- open chat of a group ------------------//

$(".group-chats").click(function () {
    groupId = $(this).data("group-id");
    $('#chatType').val('group');
    userId =

    $.ajax({
        type: "POST",
        url: "/groupChats",
        data: {
            groupId: groupId,
        },
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (response) {
            offset = 5;
            stopLoadMsg = true;
            $("#msger-chat").empty();
            $("#uid").val(groupId);
            $("#chatBox").show();
            $("#noChat").hide();
            $(".user-row").css("background-color", "white");
            $("#broadcast").css("background-color", "white");
            $(".group-chats").css("background-color", "white");
            $('.group-chats[data-group-id="' + groupId + '"]').css(
                "background-color",
                "#579ffb5c"
            );

            if (response.chats && response.chats.length > 0) {
                response.chats.reverse();
                $("#noMsgFound").hide();

                $.each(response.chats, function (index, chat) {
                    var msgType;
                    var msg;
                    if (chat.message == null) {
                        msgType = "media";
                        msg = chat.media;
                    } else if (chat.media == null) {
                        msgType = "text";
                        msg = chat.message;
                    } else {
                        msgType = "both";
                        msg = [chat.message, chat.media];
                    }
                    if (chat.sender.id == loginUserId) {
                        appendMessage(
                            chat.sender.name,
                            PERSON_IMG,
                            "right",
                            msg,
                            chat.created_at,
                            chat.receipt,
                            "beforeend",
                            msgType
                        );
                    } else {
                        appendMessage(
                            chat.sender.name,
                            BOT_IMG,
                            "left",
                            msg,
                            chat.created_at,
                            chat.receipt,
                            "beforeend",
                            msgType
                        );
                    }
                });
                msgerChat.scrollTop = msgerChat.scrollHeight;
            } else {
                $("#noMsgFound").show();
                $("#noMsgFound").text("no previous chats found!!");
            }
        },
        error: function (err) {
            alert(err.statusText);
        },
    });
});

//----------ajax request to send a message in group-------//
$("#sendBtn").on("click", function (e) {
    e.preventDefault();
    var message = $("#message").val();
    var receiverGroup = $("#uid").val();
    var fileInput = $("#mediaInput")[0].files[0];
    chatType = $('#chatType').val();

    if ((fileInput || message !== "") && receiverGroup !== "" && chatType == 'group') {
        $("#noMsgFound").hide();
        $("#mediaPreview").show();
        $("#meidaSelected").hide();
        $("#mediaDiv").hide();

        const formData = new FormData();
        formData.append("groupId", receiverGroup);
        formData.append("message", message);
        formData.append("mediaInput", fileInput);

        $.ajax({
            type: "POST",
            url: "/sendChat",
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                $("#uid").val(receiverGroup);
                $("#mediaForm")[0].reset();
                $.each(response.chats, function (index, chat) {
                    if (chat.message == null) {
                        appendMessage(
                            chat.sender.name,
                            PERSON_IMG,
                            "right",
                            chat.media,
                            chat.created_at,
                            chat.receipt,
                            "beforeend",
                            "media"
                        );
                    } else if (chat.media == null) {
                        appendMessage(
                            chat.sender.name,
                            PERSON_IMG,
                            "right",
                            chat.message,
                            chat.created_at,
                            chat.receipt,
                            "beforeend",
                            "text"
                        );
                    } else {
                        appendMessage(
                            chat.sender.name,
                            PERSON_IMG,
                            "right",
                            [chat.message, chat.media],
                            chat.created_at,
                            chat.receipt,
                            "beforeend",
                            "both"
                        );
                    }
                    msgerChat.scrollTop = msgerChat.scrollHeight;
                });
            },
            error: function (err) {
                alert(err.statusText);
            },
        });
    }
});




