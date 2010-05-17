<?php if (!defined('FARI')) die();

/**
 * Fari Framework
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link      http://radekstepan.com
 * @category  Fari Framework
 */



/**
 * Contracts through assertions.
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\Application
 */
assert_options(ASSERT_ACTIVE, 1);
assert_options(ASSERT_WARNING, 0);
assert_options(ASSERT_QUIET_EVAL, 0);
assert_options(ASSERT_BAIL, 1);
assert_options(ASSERT_CALLBACK, 'contractsCallback');

/**
* Callback function forming a Diagnostics-like output.
* @param file
* @param line
* @param message String with the assertion
*/
function contractsCallback($file, $line, $message) {
        // parse the comment in the message
        if (($position = strpos($message, '//')) !== FALSE) {
            // get the comment, trim whitespace and capitalize
            $title = ucfirst(trim(substr($message, $position + 2)));
            $message = '';
        } else {
            // don't bother and echo the assert statement
            $title = 'Contract Condition Failed';
            $message = "<br />$message";
        }

        // cleanup output
        ob_end_clean();

        ?>
            <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
            <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
            <head>
                <title>Contract Condition Failed</title>
                 <style type="text/css">
                body{background:#fff;color:#33393c;font:12px/1.5 "Trebuchet MS", "Geneva CE", lucida,sans-serif;
                margin:0;}#title{background:#222;color:#565656;border-bottom:1px solid #C52F24;margin:0 auto;
                padding:10px 30px;}#message,.error{background-color:#C52F24;color:#fff;font-weight:700;
                font-size:100%;margin:0;padding:1px 0;}#message{border-top:1px solid #980905;padding:5px 30px 10px;}
                h1{margin-bottom:0;font-weight:400;font-size:175%;}#box{background-color:#EEE;border:1px solid #ADAEAF;
                margin:10px 30px 0;padding:5px;}#file{background-color:#D5E9F6;border:1px solid #8FCDF6;
                color:#234A69;margin:10px 30px 0;padding:5px;}.code{background-color:#FFF9D8;border:1px solid #FECA51;
                margin:10px 30px;padding:5px;}i{color:#999;}.num{color:#9E9E7E;font-style:normal;font-weight:400;}
                a{color:#980905;}table{font:16px/1.5 "Trebuchet MS", "Geneva CE", lucida, sans-serif;font-size:100%;}
                td{padding-right:20px;}#title b,span.err{color:#FFF;}
                </style>
            </head>

            <body>
                <div id="title"><b><?php echo FARI; ?></b> running <b><?php echo APP_VERSION; ?></b></div>
                <div id="message"><h1><?php echo $title; ?></h1><?php echo $message; ?></div>

                <div id="file">File: <b><?php echo $file; ?></b> Line: <b><?php echo $line; ?></b></div>

                <?php Fari_ApplicationDiagnostics::showErrorSource($file, $line); ?>

            </body>
        </html>
        <?php
        
}