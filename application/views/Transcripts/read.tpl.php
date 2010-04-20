<?php if (!defined('FARI')) die(); include('application/views/head.tpl.php'); ?>
<body>
    <!-- left column -->
    <div class="up" id="left">
        <!-- messages -->
        <div id="main">
            <h1><?php echo $transcript['niceDate']; ?></h1>

            <?php
            if (!empty($previousTranscript) OR !empty($nextTranscript)) {
                $adjacent = '<p id="adjacentTranscripts">';

                if (!empty($previousTranscript)) {
                    $adjacent .= '&larr; <a class="blue" href="' . url('/transcripts/read/' .
                        $transcript['room'] . '/' . str_replace('-', '/', $previousTranscript['date']) . '/', FALSE) . '">'
                        . $previousTranscript['niceDate'] . '</a>';
                }

                if (!empty($nextTranscript)) {
                    $adjacent .= '<a class="blue" href="' . url('/transcripts/read/' .
                        $transcript['room'] . '/' . str_replace('-', '/', $nextTranscript['date']) . '/', FALSE) . '">'
                        . $nextTranscript['niceDate'] . '</a> &rarr;';
                }
                $adjacent .=  '</p>';
                echo $adjacent;
            }
            ?>

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
                        <td class="body <?php echo $message['type'] . $ourMessage; ?>">
                            <?php echo html_entity_decode($message['text']);
                            if ($message['type'] == 'text' && $message['highlight'] == 1): ?>
                                <div class="star"><a></a></div>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php
                $lastMessage = $message['id'];
                $lastUserName = $message['user'];

                endforeach;
              ?>
            </table>

            <?php if (!empty($previousTranscript) OR !empty($nextTranscript)) echo $adjacent; ?>
        </div>
        
        <div class="bottom">&nbsp;</div>
    </div>

    <div id="right" style="top:14px;">
        <h1><?php echo $transcript['name']; ?></h1>
        <h2>People in this transcript</h2>
        <ul id="participants">
            <?php foreach ($users as $user): ?>
                <li><?php echo $user; ?></li>
            <?php endforeach; ?>
        </ul>

        <?php if (!empty($files)): ?>
            <h2>Files</h2>
            <ul id="fileListing">
                <?php foreach ($files as $file): ?>
                    <li class="<?php echo $file['type']; ?>">
                        <a class="blue" href="<?php url('file/get/' .  $file['code'] . '/'); ?>">
                            <?php echo $file['filename']; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

</body>
</html>
