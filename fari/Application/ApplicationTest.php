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
 * A simple unit testing class, similar to CodeIgniter.
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\Application
 */
class Fari_ApplicationTest {

    private $failed = FALSE;
    private $results = array();
    private $functions = array('is_string', 'is_bool', 'is_true', 'is_false', 'is_int', 'is_numeric', 'is_float',
        'is_double', 'is_array', 'is_null');

    public function run($test, $expected=TRUE, $testName='undefined') {
        if (in_array($expected, $this->functions, TRUE)) {
            // test the equality with a function
            $result = $expected($test) ? TRUE : FALSE;
        } else {
            // non-strict equality
            $result = ($test == $expected) ? TRUE : FALSE;
        }

        // backtrace and get the tested class
        $trace = debug_backtrace();
        extract($trace[1]);

        
        // switch failed status?
        if ($result === TRUE) {
            $result = 'passed';
        } else {
            $result = 'failed';
            $this->failed = TRUE;
        }

        // form report
        $this->results[] = array (
            'name' => $testName,
            'result' => $result,
            'class' => $class,
            'function' => $function,
            'expected' => substr($expected, 3),
            'test' => gettype($test),
        );
    }

    public function report() {
        if ($this->failed === TRUE) {
            $title = 'Test(s) failed';
            $color = '#C52F24';
            $line = '#980905';
        } else {
            $title = 'All tests passed';
            $color = '#26BF26';
            $line = '#059824';
        }
        ?>
            <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
            <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
            <head>
                <title>Unit Testing</title>
                 <style type="text/css">
                body{background:#fff;color:#33393c;font:12px/1.5 "Trebuchet MS", "Geneva CE", lucida,sans-serif;
                margin:0;}#title{background:#222;color:#565656;border-bottom:1px solid <?php echo $color; ?>;
                margin:0 auto;padding:10px 30px;}#message{background-color:<?php echo $color; ?>;font-weight:700;
                color:#fff;font-size:100%;margin:0;padding:1px 0;}#message{border-top:1px solid <?php echo $line; ?>;
                padding:5px 30px 10px;}h1{margin-bottom:0;font-weight:400;font-size:175%;}#box{background-color:#EEE;
                border:1px solid #ADAEAF;margin:10px 30px 0;padding:5px;}#test{margin:10px 30px 0;padding:5px;background:#F5F5F5;
                color:#33393C;border:1px solid #CCCDCF;}#test .failed{color:#C52F24;}#test .passed{color:#26BF26;}
                i{color:#999;}.num{color:#9E9E7E;font-style:normal;font-weight:400;}a{color:#980905;}td{padding-right:20px;}
                table{font:16px/1.5 "Trebuchet MS", "Geneva CE", lucida, sans-serif;font-size:100%;}#title b,span.err{color:#FFF;}
                </style>
            </head>

            <body>
                <div id="title"><b><?php echo FARI; ?></b> running <b><?php echo APP_VERSION; ?></b></div>
                <div id="message"><h1><?php echo $title; ?></h1></div>
                <?php foreach ($this->results as $result): ?>
                    <div id="test">
                        <div class="<?php echo $result['result']; ?>" style="float:right;">
                            <?php
                            echo $result['name'];
                            if ($result['result'] == 'failed')
                                echo ' (expected <em>' . $result['expected'] . '</em> got <em>' .
                                    $result['test'] . '</em>) ';
                            echo ' <b>' . $result['result'] . '</b>';
                         ?>
                        </div>
                        <?php echo $result['class']; ?> / <?php echo $result['function']; ?>()
                    </div>
                <?php endforeach; ?>
            </body>
        </html>
        <?php
    }

}