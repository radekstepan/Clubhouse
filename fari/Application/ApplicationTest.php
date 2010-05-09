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

    /** @var have we failed at least one of the tests? */
    private $failed = FALSE;

    /** @var results array of all tests */
    private $results = array();

    /** @var PHP functions we can check for */
    private $functions = array('is_string', 'is_bool', 'is_true', 'is_false', 'is_int', 'is_numeric', 'is_float',
        'is_double', 'is_array', 'is_null');

    /**
     * Run a single unit test.
     * @param mixed what to test for
     * @param mixed what do we expect OR is_* PHP function
     * @param string name for the test we run
     */
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
            'file' => $trace[0]['file'],
            'line' => $trace[0]['line'],
            'result' => $result,
            'class' => $trace[1]['class'],
            'function' => $trace[1]['function'],
            'expected' => (substr($expected, 0, 3) == 'is_') ?
                substr($expected, 3) : Fari_ApplicationDiagnostics::formatVars($expected),
            'test' => Fari_ApplicationDiagnostics::formatVars($test)
        );
    }


    /**
     * Show a resulting test report.
     */
    public function report() {
        // header formatting
        if ($this->failed === TRUE) {
            $title = 'Test(s) failed';
            $color = '#C52F24';
            $line = '#980905';
        } else {
            $title = 'All tests passed';
            $color = '#26BF26';
            $line = '#059824';
        }

        // HTML output
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
                            <?php echo $result['name'] . ' <b>' . $result['result'] . '</b>'; ?>
                        </div>

                        File: <b><?php echo $result['file']; ?></b>
                        Line: <b><?php echo $result['line']; ?></b>
                        Class: <b><?php echo $result['class']; ?></b>
                        Function: <b><?php echo $result['function']; ?>()</b>

                        <?php if ($result['result'] == 'failed'): ?>
                            <br /><div class="failed" style="text-align:right;">
                                <b>Expected:</b> <?php echo $result['expected']; ?>
                                <b>Got:</b> <?php echo $result['test']; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </body>
        </html>
        <?php
        die();
    }

}