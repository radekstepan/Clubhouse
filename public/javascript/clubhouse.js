var newMessages;
function displayMessages(json) {
    var soundPlay = false;

    // remove any ad-hoc messages
    if ($('remove')) $('remove').remove();

    json.each(function(message) {
        // don't repeat the same name of the user
        if (message.user == lastUserName && message.type == lastMessageType) lastUserName = '';
        else lastUserName = message.user;
        lastMessageType = message.type;

        // highlight our messages
        var ourMessage = '';
        if (message.type == 'text' && message.userId == userId) ourMessage = ' our';
        
        // play audio and increase new message counter
        if (message.type == 'text' && message.userId != userId) {
            soundPlay = true; newMessages++;
        }

        // highlight
        var highlight = '';
        if (message.type == 'text') {
            highlight = '<div id="highlight_'
                + message.id + '" onclick="highlightMessage(\''
                + message.id + '\');return false;" class="highlight"><a href=""></a></div>';
        }

        $('result').innerHTML += '\
            <tr>\
                <td class="user ' + message.type + ourMessage + '">' + lastUserName + '</td>\
                <td class="body ' + message.type + ourMessage + '">'
                + html_entity_decode(message.text) + highlight + '</td>\
            </tr>';
        lastMessage = message.id;
        lastUserName = message.user;
    });

    if (soundPlay) {
        playSound();
        setTitle();
    }
}

var roomName;
function setTitle() {
    var focus = document.hasFocus();
    if (focus || parseInt(newMessages) == 0) {
        document.title = 'Clubhouse: ' + roomName;
        newMessages = 0;
    } else {
        document.title = '(' + newMessages + ') Clubhouse: ' + roomName;
    }
}

var scroll = false;
var messageTimeout;
function getMessages(url, roomId, param) {
    setTitle();

    //spinner(true);
    new Ajax.Request(url + roomId + '/' + param,
    {
        method: 'get',
        onSuccess: function(transport) {
            var result = transport.responseText.evalJSON();
            if (result == 'bye') kickUser();
            
            displayMessages(result);
            //spinner(false);

            if (scroll) scrollToBottom();

            // call again in 3 seconds
            messageTimeout = setTimeout("getMessages('" + url + "', " + roomId + ", '" + lastMessage + "')", 4000);
        }
    });
}

function displayParticipants(json) {
    $('participants').innerHTML = ''; // wipe
    json.each(function(participant) {
        $('participants').innerHTML += '\
            <li>' + participant.long + '</li>'; // uh oh, naming...
    });
}

function highlightMessage(messageId) {
    new Ajax.Request(domainAddress + 'message/highlight/' + messageId,
    {
        method: 'get',
        onSuccess: function(transport) {
            var result = transport.responseText.evalJSON();
            if (result != null) {
                if (result == 1) {
                    // turn highlight on
                    $('highlight_' + messageId).className = 'highlight on';
                } else {
                    // turn highlight off
                    $('highlight_' + messageId).className = 'highlight';
                }
            }
        }
    });
}

function kickUser() {
    window.location.reload();
}

function pollRoom(url, roomId) {
    new Ajax.Request(url + roomId,
    {
        method: 'get',
        onSuccess: function(transport) {
            var result = transport.responseText.evalJSON();
            if (result == 'bye') {
                kickUser();
            } else {
                // display participants
                displayParticipants(result.participants);
                // display room name & description
                displayRoomSettings(result.settings);
                // list files
                displayFiles(result.files);
                // call again in 5 seconds
                var t = setTimeout("pollRoom('" + url + "', '" + roomId + "')", 5000);
            }
        }
    });
}

function displayUsersChatting(json) {
    if (json < 1) $('chatting').innerHTML = '';
    else if (json < 2) $('chatting').innerHTML = '1 person currently chatting';
    else $('chatting').innerHTML = json + ' people currently chatting';
}

function getUsersChatting(url) {
    new Ajax.Request(url,
    {
        method: 'get',
        onSuccess: function(transport) {
            var result = transport.responseText.evalJSON();
            if (result == 'bye') kickUser();
            
            displayUsersChatting(result);
            // call again in 30 seconds
            var t = setTimeout("getUsersChatting('" + url + "')", 5000);
        }
    });
}

function showMessage() {
    // user name
    var name;
    if (shortName == lastUserName && 'text' == lastMessageType) name = ''
    else name = shortName;

    // the message
    $('result').innerHTML += '\
    <tr id="remove">\
        <td class="user text our">' + name + '</td>\
        <td class="body text our">' + $('text').value + '</td>\
    </tr>';

    scroll = true;
    scrollToBottom();
}

function sendMessage() {
    showMessage();
    $('sendMessageForm').request(
    {
        method: 'post',
        onSuccess: function(transport) {
            $('text').value = '';
            $('text').focus();
            scroll = true;
        },
        onFailure: function() { alert('Something went wrong...'); }
    });
}

function timeDifference(past) {
    // this better be positive... ;)
    var diff = Math.floor(((new Date().getTime() / 1000) - past) / 60);
    if (diff < 1) diff = '<span class="green">less than a minute ago</span>';
    else if (diff < 2) diff = '<span class="green">Active 1 minute ago</span>';
    else if (diff < 60) diff = '<span class="green">Active ' + diff + ' minutes ago</span>';
    else diff = '<span>Inactive</span>';
    return diff;
}

var lobbyRooms = new Array();
function displayRooms(json, highlight) {
    // clear first
    $('lobby').innerHTML = '<tr><td></td><td></td><td></td></tr>';
    var roomCount = 0; var rowNumber = 0; var newRooms = new Array();
    json.each(function(room) {
        newRooms[room.id] = true;
        // create a new room for a triplet of rooms
        if (roomCount % 3 == 0) {
            rowNumber++;
            var row = document.createElement('tr');
            row.setAttribute('id', 'row_' + rowNumber);
            $('lobby').appendChild(row);
        }

        // create a column with our room
        var element = document.createElement('td')

        // locked room?
        if (room.locked == 0) {
            element.innerHTML = '<div id="room_' + room.id + '" class="room"><h2>\
                <a class="blue" href="room/' + room.id + '/">' + room.name + '</a></h2>'
            + timeDifference(room.timestamp) + '<p>' + room.description + '</p></div>';
        } else {
            element.innerHTML = '<div id="room_' + room.id + '" class="room locked"><h2 class="locked">'
                + room.name + '</a></h2><span>Locked</span><p>' + room.description + '</p>\
                <p class="tiny">You can <a class="blue" href="room/' + room.id + '/">enter the locked room</a> because\
                you\'re an admin.</p></div>';
        }

        // do we have users in the room?
        if (room.users && room.locked == 0) {
            var roomUsers = '';
            room.users.each(function(name) {
                roomUsers += '<li>' + name + '</li>';
            });
            element.innerHTML += '<ul class="participants">' + roomUsers + '</ul>';
        }

        $('row_' + rowNumber).appendChild(element);

        if (highlight && (lobbyRooms[room.id] == undefined)) new Effect.Highlight('room_' + room.id, {duration:3});
        roomCount++;
    });
    lobbyRooms = newRooms;
}

function createRoom() {
    $('dots').toggle();
    $('createRoomForm').request(
    {
        method: 'post',
        onSuccess: function(transport) {
            $('dots').toggle();
            $('createRoomForm-name').value = '';
            $('createRoomForm-description').value = '';
            slideInOut('createRoom');
            displayRooms(transport.responseText.evalJSON(), true);
        },
        onFailure: function() { alert('Something went wrong...'); }
    });
}

function getRooms(url, highlight) {
    new Ajax.Request(url,
    {
        method: 'get',
        onSuccess: function(transport) {
            var result = transport.responseText.evalJSON();
            if (result == 'bye') kickUser();
            displayRooms(result, highlight);
            spinner(false);
            var t = setTimeout("getRooms('" + url + "', 'true')", 5000);
        }
    });
}

function alternateTableColor() {
    var i = 0;
    $$('.alternate tr').each(function(element) {
        if (i % 2 == 0) {
            element.className = "even";
        } else {
            element.className = "odd";
        }
        i++;
    });
}

function spinner(show) {
    if (!show) {
        if ($('spinner')) $('spinner').remove();
    } else {
        var element = document.createElement('div')
        element.setAttribute('id', 'spinner');
        element.innerHTML = '&nbsp;';
        $('ajax').appendChild(element);
    }
}

function slideInOut(id) {
    if ($(id).style.display != 'none') {
        Effect.BlindUp(id, { duration:0.2 });
    } else {
        Effect.BlindDown(id, { duration:0.3 });
    }
}

function deleteUser(url, param) {
    $('trash_' + param).innerHTML = "<div class='dots'>&nbsp;</div>";
    new Ajax.Request(url + param,
    {
        method: 'get',
        onSuccess: function(transport) {
            $('user_' + param).remove();
            alternateTableColor();
        }
    });
}

function deleteRoom(url, param) {
    $('room_' + param).innerHTML = "<div class='dots'>&nbsp;</div>";
    new Ajax.Request(url + param,
    {
        method: 'get',
        onSuccess: function(transport) {
            $('room_' + param).remove();
            alternateTableColor();
        }
    });
}

var lockRoomText = 'Are you sure you want to lock this room?';
function lockRoom(url) {
    // are you sure?
    if (confirm(lockRoomText)) {
        // spinner on, link off
        $('lockSpinner').toggle(); $('lockLink').toggle();

        new Ajax.Request(url,
        {
            method: 'get',
            onSuccess: function(transport) {
                // link text & spinner
                if (!lockedRoom) {
                    $('lockLink').innerHTML = "Unlock room";
                    lockedRoom = true;
                    lockRoomText = 'Are you sure you want to unlock this room?';
                } else {
                    $('lockLink').innerHTML = "Lock room";
                    lockedRoom = false;
                    lockRoomText = 'Are you sure you want to lock this room?';
                }

                // locked room message toggle
                $('lockMessage').toggle();

                // spinner off, link on
                $('lockSpinner').toggle(); $('lockLink').toggle();
            }
        });
    }
}

function displayRoomSettings(json) {
    $('roomName').innerHTML = json.name;
    $('roomDescription').innerHTML = json.description;
    // eh... a toggle of sorts
    if (json.description) {
        $('topicEdit').innerHTML = 'Edit';
        $('addTopic').innerHTML = '';
    } else {
        $('addTopic').innerHTML = 'Add a topic';
        $('topicEdit').innerHTML = '';
    }
    
    // room lock
    if ($('lockLink')) { // guest room...
        if (json.locked > 0) {
            if (lockedRoom == false) $('lockMessage').toggle();
            lockedRoom = true;
            $('lockLink').innerHTML = "Unlock room";
            lockedRoom = true;
            lockRoomText = 'Are you sure you want to unlock this room?';
        } else {
            if (lockedRoom == true) $('lockMessage').toggle();
            lockedRoom = false;
            $('lockLink').innerHTML = "Lock room";
            lockedRoom = false;
            lockRoomText = 'Are you sure you want to lock this room?';
        }
    }

    // guest status
    if ($('guest')) {
        if (json.guest != '0') {
            //if (guestStatus == 'off') $('guestLink').toggle();
            // we have enabled guest access
            $('guestLink').innerHTML = "Turn it off"; // link message
            $('guest').className = 'guest'; // css
            guestStatus = 'on'; // status
            $('guestStatus').innerHTML = 'on'; // text of the status :)
            $('guestNote').style.display = 'inline'; // guest mode note
            $('guestAddress').innerHTML = domainAddress + 'g/' + json.guest; // guest address
        } else {
            //if (guestStatus == 'on') $('guestLink').toggle();
            // we have disabled guest access
            $('guestLink').innerHTML = "Turn it on"; // link message
            $('guest').className = ''; // css
            guestStatus = 'off'; // status
            $('guestStatus').innerHTML = 'off'; // text of the status :)
            $('guestNote').style.display = 'none'; // guest mode note
            $('guestAddress').innerHTML = ''; // guest address
        }
    }
}

function editTopicToggle() {
    $('editTopicForm').toggle();
    $('description').toggle();
    $('topicEntry').focus();
}

function editTopic() {
    $('dots').toggle();
    $('editTopicForm').request(
    {
        method: 'post',
        onSuccess: function(transport) {
            var newTopic = transport.responseText.evalJSON()
            $('roomDescription').innerHTML = newTopic;
            // clear input form
            $("topicEntry").value = '';
            // switch off ajax spinner
            $('dots').toggle();

            editTopicToggle();
            // are we showing 'Edit' or 'Add'?
            if (newTopic.length > 0) {
                $('topicEdit').style.display = 'inline'; $('topicEdit').innerHTML = 'Edit';
                $('addTopic').style.display = 'none';
            } else {
                $('topicEdit').style.display = 'none';
                $('addTopic').style.display = 'inline'; $('addTopic').innerHTML = 'Add a topic';
            }
        },
        onFailure: function() { alert('Something went wrong...'); }
    });
}

var guestRoomText = 'Are you sure you want to turn guest access ';
function guestRoom(url) {
    // this is the new status we are trying to achieve
    if (guestStatus == 'on') {
        guestStatus = 'off';
    } else {
        guestStatus = 'on';
    }

    // are you sure?
    if (confirm(guestRoomText + guestStatus + '?')) {
        // spinner on, link off
        $('guestSpinner').toggle(); $('guestLink').toggle();

        new Ajax.Request(url,
        {
            method: 'get',
            onSuccess: function(transport) {
                var response = transport.responseText.evalJSON();
                // link text & spinner
                if (response != '0') {
                    // we have enabled guest access
                    $('guestLink').innerHTML = "Turn it off"; // link message
                    $('guest').className = 'guest'; // css
                    guestStatus = 'on'; // status
                    $('guestStatus').innerHTML = 'on'; // text of the status :)
                    $('guestNote').style.display = 'inline'; // guest mode note
                    $('guestAddress').innerHTML = domainAddress + 'g/' + response; // guest address
                } else {
                    // we have disabled guest access
                    $('guestLink').innerHTML = "Turn it on"; // link message
                    $('guest').className = ''; // css
                    guestStatus = 'off'; // status
                    $('guestStatus').innerHTML = 'off'; // text of the status :)
                    $('guestNote').style.display = 'none'; // guest mode note
                    $('guestAddress').innerHTML = ''; // guest address
                }
                
                // spinner off, link on
                $('guestSpinner').toggle(); $('guestLink').toggle();
            }
        });
    }
}

function scrollToBottom() {
    scrollTo(0,999999);
    scroll = false;
}

var fileUploadStatus;
function fileUpload() {
    // hide form
    $('fileUpload').toggle();
    // show spinner and file name
    var f = $('upload').value.toString().substring($('upload').value.toString().lastIndexOf('/'));
    $('fileUploadText').innerHTML = 'Uploading <strong>' + f + '</strong>…';
    $('fileUploadSpinner').toggle();
    // initial status
    fileUploadStatus = $('target').innerHTML;
    // launch poller
    fileUploadPoll();
}

// file upload poller
function fileUploadPoll() {
    // check file upload success
    if ($('target').contentWindow.document.body.innerHTML != fileUploadStatus) {
        // stop spinner
        $('fileUploadSpinner').toggle();
        // clear form
        $('upload').value = '';
        // clear iframe
        $('target').contentWindow.document.body.innerHTML = '';
    }
    else var t = setTimeout("fileUploadPoll()", 1000);
}

function displayFiles(result) {
    $('fileListing').innerHTML = '';
    result.each(function(file) {
        $('fileListing').innerHTML += '<li class="' + file.type + '">\
            <a class="blue" href="' + domainAddress + 'file/get/' +  file.code + '/">' + file.filename + '</a></li>';
    });
}

var sound = true;
function soundSwitch(url) {
    if (sound) {
        sound = false;
        $('speaker').src = url + 'sound-off.gif';
    } else {
        sound = true;
        $('speaker').src = url + 'sound-on.gif';
    }
}

function playSound() {
    if (sound) Sound.play($('incomingSound').value);
}

function checkUsername(url) {
    new Ajax.Request(url + encodeURI($('usernameField').value),
    {
        method: 'get',
        onSuccess: function(transport) {
            var result = transport.responseText.evalJSON();
            if (result.empty()) {
                $('username').className = 'field green';
                $('button').enable();
                $('usernameMsg').innerHTML = 'This is what you\'ll use to sign in.';
            } else {
                $('username').className = 'field red';
                $('button').disable();
                $('usernameMsg').innerHTML = result;
            }
        }
    });
}

function checkPasswords() {
    if ($('passwordField2').value != '') {
        if ($('passwordField1').value === $('passwordField2').value) {
            $('password1').className = 'field green';
            $('password2').className = 'field green';
            $('button').enable();
            $('passwordMsg').innerHTML = '';
        } else {
            $('password1').className = 'field red';
            $('password2').className = 'field red';
            $('button').disable();
            $('passwordMsg').innerHTML = 'These passwords don\'t match.';
        }
    }
}

// Bram.us
function html_entity_decode(string) {
    var ta = document.createElement("textarea");
    ta.innerHTML = string.replace(/</g,"&lt;").replace(/>/g,"&gt;");
    toReturn = ta.value;
    ta = null;
    return toReturn;
}