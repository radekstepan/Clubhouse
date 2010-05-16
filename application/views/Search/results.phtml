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
            <li class="active indent"><a href="<?php url('/transcripts/'); ?>">Files, Transcripts &amp; Search</a></li>
            
            <li class="right nobg"><a href="<?php url('/logout/'); ?>">Sign out</a></li>
            <li class="right"><a href="<?php url('/settings/'); ?>">Settings</a></li>
            <li class="right"><a href="<?php url('/users/'); ?>">Users</a></li>
        </ul>
    </div>

    <!-- left column -->
    <div class="full" id="left">
        <div id="main">
            <div id="search">
                <h1>Search results
                    <span>(</span><a class="blue" href="<?php url('/transcripts/'); ?>"
                                     >Return to transcript browser</a><span>)</span>
                </h1>

                <form id="searchForm" class="form" method="POST" action="<?php url('/search/'); ?>">
                    <input type="text" id="q" name="q" value="<?php echo $q; ?>" />
                    <input type="submit" value="Search" />
                </form>
                <table class="alternate" id="searchResults">
                    <tr><th>Room</th><th>Person</th><th>Result</th><th>Day</th><th class="blank"></th></tr>
                <?php foreach ($results as $result): ?>
                    <tr>
                        <td><?php echo $result['name']; ?></td>
                        <td class="right"><?php echo $result['long']; ?></td>
                        <td class="wrap"><?php echo $result['text']; ?></td>
                        <td><?php echo date("l, M j", Fari_Format::date($result['date'], 'timestamp')); ?></td>
                        <td><a target="_new" class="blue" href="
                            <?php url('/transcripts/read/' . $result['id'] .
                                date("/Y/m/d/", Fari_Format::date($result['date'], 'timestamp'))); ?>">Open</a></td>
                    </tr>
                <?php endforeach; ?>
                </table>
            </div>
        </div>
        
        <div class="bottom">&nbsp;</div>
    </div>
</body>
</html>