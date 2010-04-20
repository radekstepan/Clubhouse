<?php if (!defined('FARI')) die(); include('application/views/head.tpl.php'); ?>

<body>
    <!-- main center block -->
    <div id="center" class="small">
        <div id="wrapper">
            <h1>You're all set, <?php echo $account['name']; ?>!</h1>
            <div id="main">
                <p>We just sent you an email to <?php echo $account['email']; ?> with your sign-in information so you'll
                    always have it handy.</p>
                <div class="button">
                    <a href="<?php url('/'); ?>"><span>Continue to your Clubhouse account &rarr;</span></a>
                </div>
            </div>
        </div>
        <div class="bottom">&nbsp;</div>
    </div>
</body>
</html>