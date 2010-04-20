<?php if (!defined('FARI')) die(); include('application/views/head.tpl.php'); ?>

<body>
    <div id="modal">
        <img src="<?php url('/public/images/clubhouse-small-header-logo.png'); ?>" alt="clubhouse logo"/>
        <h1>You have left the room</h1>
        <p>If you have an account you can <a href="<?php url('/login/'); ?>">sign in</a>.</p>
        <hr>
        <p>
            <a href="javascript:history.go(-1)">Go back a page</a>
        </p>
    </div>
</body>
</html>