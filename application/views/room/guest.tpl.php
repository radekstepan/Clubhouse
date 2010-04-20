<?php if (!defined('FARI')) die(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="Content-Language" content="en" />
    <meta name="description" content="Clubhouse" />
    <title>Clubhouse: <?php echo $room['name']; ?></title>

    <link rel="stylesheet" type="text/css" media="screen" href="<?php url('/public/css/reset.css'); ?>" />
    <link rel="stylesheet" type="text/css" media="screen" href="<?php url('/public/css/codebase.css'); ?>" />
    
    <link rel="shortcut icon" type="image/x-icon" href="<?php url('/public/favicon.ico'); ?>" />

    <script src="<?php url('/public/javascript/prototype.js'); ?>" language="javascript" type="text/javascript"></script>
    <script src="<?php url('/public/javascript/scriptaculous/scriptaculous.js'); ?>" language="javascript" type="text/javascript"></script>
    <script src="<?php url('/public/javascript/clubhouse.js'); ?>" language="javascript" type="text/javascript"></script>
</head>

<body onload="
    $('text').observe('keypress', function(event){ if (event.keyCode == Event.KEY_RETURN) sendMessage();});
    $('topicEntry').observe('keypress', function(event){ if (event.keyCode == Event.KEY_RETURN) editTopic();});

    $('text').focus();
    scroll = true;
    roomName = '<?php echo $room['name']; ?>';
    domainAddress = '<?php url('', TRUE, TRUE); ?>';
    getMessages('<?php url('/message/get/'); ?>', '<?php echo $room['id']; ?>', lastMessage);
    pollRoom('<?php url('/room/poll/'); ?>', '<?php echo $room['id']; ?>');
">    
    <!-- menu -->
    <div id="menu" class="guest"></div>

    <!-- left column -->
    <div id="left" class="guest">
        <!-- messages -->
        <div id="main">
            <div id="ajax"></div>
            <p class="guest">Welcome to the chat. You can see who's currently in the room by looking to the right. Say
                "Hello" to everyone by just typing in the box below and hitting return.</p>
            <table id="result">
                <?php
                $lastUserName = '';
                $lastMessageType = '';

                foreach ($messages as $message):
                    if ($message['user'] == $lastUserName && $message['type'] == $lastMessageType) $lastUserName = '';
                    else $lastUserName = $message['user'];
                    $lastMessageType = $message['type'];

                    $ourMessage = '';
                    if ($message['type'] == 'text' && $message['userId'] == $userId) $ourMessage = ' our';
              ?>
                    <tr>
                        <td class="user <?php echo $message['type'] . $ourMessage; ?>"><?php echo $lastUserName; ?></td>
                        <td class="body <?php echo $message['type'] . $ourMessage; ?>"><?php echo html_entity_decode($message['text']); ?></td>
                    </tr>
                <?php
                $lastMessage = $message['id'];
                $lastUserName = $message['user'];

                endforeach;
              ?>
            </table>
            <script type="text/javascript">
                var userId = <?php echo $userId; ?>;
                var lastUserName = '<?php echo $lastUserName; ?>';
                var shortName = '<?php echo $shortName; ?>';
                var lastMessage = <?php echo $lastMessage; ?>;
                var lastMessageType = '<?php echo $lastMessageType; ?>';
            </script>
        </div>
        
        <div class="bottom">&nbsp;</div>
    </div>

    <!-- right column -->
    <div id="right" class="guest">
        <!-- sound -->
        <div id="sound">
            <a href="<?php url('/javascript/'); ?>" onclick="soundSwitch('<?php url('public/images/') ?>');return false;">
                <input id="incomingSound" type="hidden" value="<?php url('public/audio/incoming.mp3'); ?>" />
                <img id="speaker" src="<?php url('public/images/sound-on.gif') ?>" alt="sound" />
            </a>
        </div>

        <!-- room, topic -->
        <h1 id="roomName"><?php echo $room['name']; ?></h1>

        <div id="description">
            <a id="addTopic" class="orange" onclick="editTopicToggle();return false;"
               href="<?php url('/javascript/'); ?>"></a>
            <span id="roomDescription" class="sub"><?php echo $room['description']; ?></span>
            <a id="topicEdit" class="gray" onclick="editTopicToggle();return false;" href="<?php url('/javascript/'); ?>"></a>
        </div>
        <form id="editTopicForm" style="display:none;" method="POST" action="<?php url('/room/topic/' . $room['id']); ?>">
            <textarea id="topicEntry" name="topic"></textarea>
            <input onclick="editTopic();return false;" type="submit" value="Save" />
            <span>or</span>
            <a class="orange" onclick="editTopicToggle();return false;" href="<?php url('/javascript/'); ?>">Cancel</a>
            <div id="dots" class='dots' style="display:none;">&nbsp;</div>
        </form>
        
        <!-- room lock message -->
        <p id="lockMessage" style="display:none;">
            <strong>This room is locked & off the record.</strong> No one else can enter this room. Any conversations
            or files will not be logged to the transcript.
        </p>

        <!-- participants -->
        <h2>Who's here? <a class="blue" href="<?php url('/room/leave/' . $room['id'] . '/'); ?>">Leave</a></h2>
        <ul id="participants"><li></li></ul>

        <!-- file upload -->
        <h2>Files
            <a class="blue" onclick="slideInOut('fileUpload');return false;" href="<?php url('/javascript/'); ?>
               "style="float:right;" href="">Upload a file</a></h2>
        <form id="fileUpload" action="<?php url('/file/upload/'); ?>" style="display:none;"
              enctype="multipart/form-data" method="POST" target="target">
            <p>Choose a file less than 10MB in size.</p>
            <input type="file" name="upload" id="upload" /><br />
            <input type="hidden" name="roomId" value="<?php echo $room['id']; ?>" />
            <input type="submit" value="Upload" onclick="fileUpload();" /> <span>or</span>
            <a onclick="slideInOut('fileUpload');return false;" href="<?php url('/javascript/'); ?>" class="orange">Cancel</a>
            <iframe id="target" name="target" src="#"></iframe>
        </form>
        <div id="fileUploadSpinner" style="display:none;">
            <img src="<?php url('/public/images/progress_bar.gif'); ?>" />
            <p id="fileUploadText"></p>
        </div>
        <ul id="fileListing"></ul>
    </div>

    <!-- footer -->
    <div id="footer">
        <form id="sendMessageForm" method="POST" action="<?php url('/message/speak/' . $room['id']); ?>">
            <textarea id="text" name="text"></textarea>
            <input onclick="sendMessage();return false;" type="submit" value="Send message" />
        </form>
    </div>

</body>
</html>