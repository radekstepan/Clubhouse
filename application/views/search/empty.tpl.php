<?php if (!defined('FARI')) die(); include('application/views/head.tpl.php'); ?>

<body>
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
                
                <h2>No matches were found</h2>
            </div>
        </div>
        
        <div class="bottom">&nbsp;</div>
    </div>
</body>
</html>