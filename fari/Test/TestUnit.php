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
 * A simple unit testing class, similar to CodeIgniter and Symfony.
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\Test
 */
class Fari_TestUnit {

    /** @var have we failed at least one of the tests? */
    private $failed = FALSE;

    /** @var results array of all tests */
    private $results = array();

    /**
     * Compares two values and passes if they are equal (==)
     * @param mixed $test what to test for
     * @param mixed $expected what do we expect
     * @param string $testName for the test we run
     */
    public function is($test, $expected, $testName='undefined') {
        $this->saveTest($test, $expected, ($test == $expected), $testName);
    }

    /**
     * Compares two values and passes if they are not equal (!=)
     * @param mixed $test what to test for
     * @param mixed $expected what do we expect
     * @param string $testName for the test we run
     */
    public function isNot($test, $expected, $testName='undefined') {
        $this->saveTest($test, $expected, ($test != $expected), $testName);
    }

    /**
     * Compares two values and passes if they are strictly equal (===)
     * @param mixed $test what to test for
     * @param mixed $expected what do we expect
     * @param string $testName for the test we run
     */
    public function isStrictly($test, $expected, $testName='undefined') {
        $this->saveTest($test, $expected, ($test === $expected), $testName);
    }

    /**
     * Compares two values and passes if they are not strictly equal (!==)
     * @param mixed $test what to test for
     * @param mixed $expected what do we expect
     * @param string $testName for the test we run
     */
    public function isNotStrictly($test, $expected, $testName='undefined') {
        $this->saveTest($test, $expected, ($test !== $expected), $testName);
    }

    /**
     * Compares two values using a custom operator
     * @param mixed $test what to test for
     * @param string $operator
     * @param mixed $expected what do we expect
     * @param string $testName for the test we run
     */
    public function compare($test, $operator, $expected, $testName='undefined') {
        $php = sprintf("\$result = \$test $operator \$expected;");
        eval($php);
        $this->saveTest($test, $expected, $result, $testName);
    }

    /**
     * Determine type match
     * @param mixed $mixed
     * @param string $type Type to match for
     * @param string $testName for the test we run
     */
    public function isType($mixed, $type, $testName='undefined') {
        // determine the type of the mixed object
        $mixedType = is_object($mixed) ? get_class($mixed) : gettype($mixed);
        // determine type equality
        $result = ((boolean)($type == $mixedType));

        $this->saveTest($mixed, $type, $result, $testName);
    }



    /********************* test results report *********************/



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
                 <?php Fari_ApplicationDiagnostics::jsToggle();  ?>
                 <style type="text/css">
                body{background:#fff;color:#33393c;font:12px/1.5 "Trebuchet MS", "Geneva CE", lucida,sans-serif;
                margin:0;}#title{background:#222;color:#565656;border-bottom:1px solid <?php echo $color; ?>;
                margin:0 auto;padding:10px 30px;}#message{background-color:<?php echo $color; ?>;font-weight:700;
                color:#fff;font-size:100%;margin:0;padding:1px 0;}#message{border-top:1px solid <?php echo $line; ?>;
                padding:5px 30px 10px;}h1{margin-bottom:0;font-weight:400;font-size:175%;}#box{background-color:#EEE;
                border:1px solid #ADAEAF;margin:10px 30px 0;padding:5px;}#test{margin:10px 30px 0;padding:5px;background:#F5F5F5;
                color:#33393C;border:1px solid #CCCDCF;}#test .failed{color:#C52F24;}#test .passed{color:#008800;}
                i{color:#999;}.num{color:#9E9E7E;font-style:normal;font-weight:400;}a{color:#980905;}td{padding-right:20px;}
                table{font:16px/1.5 "Trebuchet MS", "Geneva CE", lucida, sans-serif;font-size:100%;}#title b,span.err{color:#FFF;}
                .code{background-color:#FFF9D8;border:1px solid #FECA51;margin:10px 30px;padding:5px;}.error{background-color:#C52F24;
                color:#fff;font-weight:700;background-color:#C52F24;font-size:100%;margin:0;padding:1px 0;}
                </style>
            </head>

            <body>
                <div id="title"><b><?php echo FARI; ?></b> running <b><?php echo APP_VERSION; ?></b></div>
                <div id="message"><h1><?php echo $title; ?></h1></div>
                <?php $i=1; foreach ($this->results as $result): ?>
                    <div id="test">
                        <div style="float:right;">
                            <?php
                            echo '<b>File:</b> ' . $result['trace'][0] . ' <b>Line:</b> ' . $result['trace'][1];
                                echo '&nbsp;&nbsp;<a href="" onclick="toggle(\'' . $i . '\');return false;" >source</a>';
                         ?>
                        </div>

                        <div class="<?php echo $result['result']; ?>">
                            <?php echo '<b>' . ucfirst($result['result']) . '</b> ' . $result['name']; ?>
                        </div>

                        <?php
                        Fari_ApplicationDiagnostics::showErrorSource(
                            $result['trace'][0], $result['trace'][1], 6, $i
                        );
                        $i++;
                     ?>
                    </div>
                <?php endforeach; ?>
            </body>
        </html>
        <?php
        die();
    }



    /********************* helpers *********************/



    private function saveTest($test, $expected, $result, $testName) {
        // backtrace and extract the tested class details
        ob_start();
        // ...because debug_backtrace() is inconsistent
        debug_print_backtrace();
        $trace = ob_get_contents();
        ob_end_clean();

        $trace = explode(':', $this->trace(explode("\n", $trace)));

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
            'trace' => $trace
        );
    }

    /**
     * Tracing function returning the position of class tested
     */
    private function trace($trace, $position=0) {
        // found ourselves :)
        if (strpos($trace[$position], 'Fari_TestUnit') !== FALSE) {
            $position++;
            return $this->trace($trace, $position);
        } else {
            // find the filename from previous function & extract them :)
            return substr(
                strrchr(
                    substr(
                        $trace[$position - 1], strpos($trace[$position - 1], "Fari_TestUnit")
                    ), '['
                ), 1, -1
            );
        }
    }

}
