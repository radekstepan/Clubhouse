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
            <div id="transcripts">
                <h1>Transcripts &amp; Search</h1>

                <form id="searchForm" class="form" method="POST" action="<?php url('/search/'); ?>">
                    <input type="text" id="q" name="q" />
                    <input type="submit" value="Search" />
                </form>

                <?php $pagin = '
                    <p class="paginator">
                        Page: ';
                        foreach ($paginator as $page) {
                            if ($page['class'] == 'current')
                                $links[] = $page['number'];
                            else
                                $links[] = '<a class="blue" href="' .
                                        url('/transcripts/page/' . $page['number'], FALSE) .
                                    '">' . $page['number'] . '</a>';
                        }
                    $pagin .= implode(' | ', $links) . '</p>';
                  
                  echo $pagin;
              ?>

                <?php
                if (!empty($transcripts)):
                    foreach ($transcripts as $t):
                        ?>
                            <div class="transcript">
                                <h2>
                                    <strong><?php echo $t['niceDate']; ?></strong>
                                    in <?php echo $t['name']; ?>
                                    &ndash;
                                    <a class="blue" target="_blank" href="<?php
                                    url('/transcripts/read/' . $t['id'] . '/' . str_replace('-', '/', $t['date']) . '/');
                                ?>">Read the transcript</a>
                                    <a class="gray" href="<?php
                                    url('/transcripts/delete/' . $t['id'] . '/' . str_replace('-', '/', $t['date']) . '/');
                                ?>">Delete transcript</a>
                                </h2>
                                <p><span><?php echo $t['users']; ?></span></p>

                                <!-- files -->
                                <?php if (!empty($t['files'])): ?>
                                    <div class="files">
                                        <h3>Files</h3>
                                        <?php foreach ($t['files'] as $f): ?>
                                            <img src="<?php url('public/images/files/icon_' . $f['type'] . '.gif'); ?>" alt ="filetype"/>
                                            <a class="blue" href="<?php url('file/get/' . $f['code'] . '/'); ?>">
                                                <?php echo $f['filename']; ?>
                                            </a><br />
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <!-- highlights -->
                                <?php if (!empty($t['starred'])): ?>
                                    <div class="starred">
                                        <h3>Highlights</h3>
                                        <table>
                                            <?php foreach ($t['starred'] as $m): ?>
                                                <tr class="<?php if (!isset($first)) echo 'first'; $first = FALSE; ?>">
                                                    <td class="star"><span>star</span></td>
                                                    <td class="person"><strong><?php echo $m['user']; ?></strong></td>
                                                    <td class="body"><?php echo $m['text']; ?></td>
                                                </tr>
                                            <?php endforeach; unset($first); ?>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                <?php endforeach; endif; echo $pagin; ?>
            </div>
        </div>
        
        <div class="bottom">&nbsp;</div>
    </div>
</body>
</html>