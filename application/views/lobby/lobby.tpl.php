<?php if (!defined('FARI')) die(); include('application/views/scriptaculous.tpl.php'); ?>

<body onload="
    spinner(true);
    getRooms('<?php url('/lobby/rooms/'); ?>');
    getUsersChatting('<?php url('/lobby/users/'); ?>');
    $('createRoomForm-description').observe('keypress', function(event){ if (event.keyCode == Event.KEY_RETURN) createRoom();});
">
    <!-- header -->
    <div id="products"><a class="active" href="#">Clubhouse</a></div>
    
    <!-- menu -->
    <div id="menu">
        <ul>
            <li class="active"><a href="<?php url('/'); ?>">Lobby</a></li>
            <?php foreach($tabs as $roomName => $roomId): ?>
                <li><a href="<?php url('/room/' . $roomId . '/'); ?>"><?php echo $roomName; ?></a></li>
            <?php endforeach; ?>
            <li class="indent"><a href="<?php url('/transcripts/'); ?>">Files, Transcripts &amp; Search</a></li>
            
            <li class="right nobg"><a href="<?php url('/logout/'); ?>">Sign out</a></li>
            <?php if ($isAdmin): ?>
                <li class="right"><a href="<?php url('/settings/'); ?>">Settings</a></li>
                <li class="right"><a href="<?php url('/users/'); ?>">Users</a></li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- left column -->
    <div id="left" class="full">
        <div id="main">
            <h1><span class="right" style="margin-top:14px;">
                    <a href="<?php url('/javascript/'); ?>" onclick="slideInOut('createRoom');return false;" class="orange">
                        <strong>Create a new room</strong></a>
                </span> Lobby</h1>
            <p id="chatting" class="small"></p>
            <div id="ajax"></div>

            <!-- create a room -->
            <div id="createRoom" style="display:none;">
                <div class="left">
                    <h2>Create a new room</h2>
                    <p>All chats take place in rooms. You can create as many rooms as you'd like.</p>
                    <p>Consider making rooms for client projects, general discussions, specific meetings/events, and more.</p>
                    <div id="dots" class='dots' style="display:none;">&nbsp;</div>
                </div>
                <form id="createRoomForm" class="form" method="POST" action="<?php url('/room/create/'); ?>">
                    <div class="field">
                        <label><strong>Name the room</strong></label>
                        <input id="createRoomForm-name" type="text" name="name" />
                    </div>
                    <div class="field">
                        <label>Optional: Give the room a topic or description</label>
                        <textarea id="createRoomForm-description" name="description"></textarea>
                    </div>
                    <input type="submit" onclick="createRoom();return false;"
                           value="Create the room" class="button" />
                    <span>or</span> <a onclick="slideInOut('createRoom');return false;"
                                       href="<?php url('/javascript/'); ?>" class="cancel">Cancel</a>
                </form>
                <div style="clear: both;"></div>
            </div>

            <!-- rooms listing -->
            <table id="lobby"><tr><td></td><td></td><td></td></tr></table>
            <div style="clear:both; padding-top:45px;"></div>
        </div>
        
        <div class="bottom">&nbsp;</div>
    </div>

</body>
</html>