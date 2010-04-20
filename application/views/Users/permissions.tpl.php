<?php if (!defined('FARI')) die(); include('application/views/head.tpl.php'); ?>

<body>
    <!-- main center block -->
    <div class="tiny" id="center">
        <div id="wrapper">
            <h1>Restrict <?php echo $user['long']; ?>.</h1>
            <div id="main">
                <p>This person will only be able to enter and view transcripts for the rooms you choose here.</p>
                <form class="permission" method="POST" action="<?php url('/users/permissions/' . $user['id']); ?>">
                    <?php foreach ($rooms as $room): ?>
                        <div>
                            <input <?php if (in_array($room['id'], $permissions)) echo ' checked'; ?>
                                type="checkbox" name="<?php echo $room['id'] ?>" />
                            <label><?php echo $room['name'] ?></label>
                        </div>
                    <?php endforeach; ?>

                    <input type="hidden" name="tadaa" />
                    <input type="submit" class="button" value="Save changes" />
                    or <a class="orange" href="<?php url('/users/'); ?>">Cancel</a>
                </form>
            </div>
        </div>
        <div class="bottom">&nbsp;</div>
    </div>
</body>
</html>