<?php if (!defined('FARI')) die(); include('application/views/prototype.tpl.php'); ?>

<body onload="alternateTableColor();">
    <!-- header -->
    <div id="products"><a class="active" href="#">Clubhouse</a></div>
    
    <!-- menu -->
    <div id="menu">
        <ul>
            <li><a href="<?php url('/'); ?>">Lobby</a></li>
            <?php foreach($tabs as $roomName => $roomId): ?>
                <li><a href="<?php url('/room/' . $roomId . '/'); ?>"><?php echo $roomName; ?></a></li>
            <?php endforeach; ?>
            <li class="indent"><a href="<?php url('/transcripts/'); ?>">Files, Transcripts &amp; Search</a></li>
            
            <li class="right nobg"><a href="<?php url('/logout/'); ?>">Sign out</a></li>
            <li class="right"><a href="<?php url('/settings/'); ?>">Settings</a></li>
            <li class="active right"><a href="<?php url('/users/'); ?>">Users</a></li>
        </ul>
    </div>

    <!-- left column -->
    <div id="left">
        <!-- users listing -->
        <div id="main">
            <h1><span class="right">
                    <a href="<?php url('/invitations/new/'); ?>" class="orange">Invite a new user</a>
                    | <a href="#" class="orange">
                Edit your personal info</a></span> Users</h1>

            <?php if (isset($messages)) foreach($messages as $message): ?>
                <div id="flash" class="<?php echo $message['status']; ?>"><?php echo $message['message']; ?></div>
            <?php endforeach; ?>

            <table class="alternate" id="users">
                <tr><th>Name</th><th>Permissions</th></tr>
                <?php foreach ($users as $user): ?>
                <tr id="user_<?php echo $user['id'] ?>">
                    <td class="name"><?php echo $user['long']; ?></td>
                    <td class="permissions">

                        <!-- delete all but the owner -->
                        <?php if ($user['perm'] != 'Owner'): ?>
                            <div id="trash_<?php echo $user['id'] ?>" class="right">
                                <a href="<?php url('/javascript/'); ?>" title="Delete this member"
                                   onclick="deleteUser('<?php url('/users/delete/'); ?>',
                                       '<?php echo $user['id']; ?>');return false;">
                                    <img src="<?php url('public/images/trash.gif'); ?>" alt="trash" />
                                </a>
                            </div>
                        <?php endif; ?>

                        <!-- access -->
                        <?php
                        switch($user['perm']) {
                            case 'Owner': echo 'Owner'; break;
                            case 'Guest': echo 'Guest'; break;
                            case '': echo 'Cannot access any rooms
                                            (<a href="permissions/'.$user['id'].'" class="blue">Change</a>)';
                                        break;
                            default: echo $user['perm'] . ' (<a href="permissions/'.$user['id'].'" class="blue">Change</a>)';
                        }
                     ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        
        <div class="bottom">&nbsp;</div>
    </div>

    <!-- right column -->
    <div id="right">
        <!-- help -->
        <h3>What can an admin do?</h3>
        <p>Admins can see the Users and Settings tabs. They can also delete transcripts and enter locked rooms.</p>
        <h4>On the Users tab they can…</h4>
        <ul>
            <li>Invite new users</li>
            <li>Delete users</li>
            <li>Restrict users to certain rooms</li>
        </ul>

        <h4>On the Settings tab they can…</h4>
        <ul>
            <li>Rename and delete rooms</li>
            <li>Change the time zone</li>
            <li>Change the lobby header</li>
            <li>Change the logo</li>
        </ul>
    </div>

</body>
</html>