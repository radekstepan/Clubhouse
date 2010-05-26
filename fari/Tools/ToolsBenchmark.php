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
 * Benchmark execution time.
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\Benchmark
 */
class Fari_ToolsBenchmark {

    /** @var array of running benchmarks or results */
    private $data;

    /**
     * Start benchmark.
     * @param string $activity to save under
     */
    public function start($activity) {
        $this->data[$activity] = microtime();
    }

    /**
     * Stop benchmark and save time into activity.
     * @param string $activity
     * @return FALSE if activity not found
     */
    public function stop($activity) {
        if (isset($this->data[$activity])) {
            $stop = $this->fixMicrotime(microtime());
            $start = $this->fixMicrotime($this->data[$activity]);
            $this->data[$activity] = round($stop - $start, 4) . ' s';
        } else {
            return FALSE;
        }
    }

    /**
     * Return an array of benchmark results.
     * @param boolean $nice set to TRUE if you want to echo the results into a table
     * @return array
     */
    public function report($nice=FALSE) {
        if ($nice) {
            echo '<table class="benchmark">';
            foreach ($this->data as $activity => $time) {
                // print only results
                if (strpos($time, 's') !== FALSE) {
                    echo "<tr><td>{$activity}</td><td>{$time}</td></tr>";
                }
            }
            echo '</table>';
        } else {
            return $this->data;
        }
    }

    /**
     * Fix microtime.
     * @param string $time
     * @return string
     */
    private function fixMicrotime($time) {
        $time = explode(' ', $time);
        return doubleval($time[0]) + $time[1];
    }

}