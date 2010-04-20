<?php if (!defined('FARI')) die(); include('application/views/head.tpl.php'); ?>

<body>
    <div id="modal">
        <img src="<?php url('/public/images/clubhouse-small-header-logo.png'); ?>" alt="clubhouse logo"/>
        <h1>This room is locked</h1>
        <p>Ask the person who invited you to Clubhouse, to unlock the room.</p>
        <hr>
        <p>
            <a href="javascript:history.go(-1)">Go back a page</a>
        </p>
    </div>
</body>
</html>