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
 * An observer saving information to log files.
 * @example $ tail -f log/db.log
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\Application
 */
class Fari_ApplicationLogger {

    /**#@+ directory where to save logs */
    const LOG_DIRECTORY = '/log';
    /**#@-*/

    /** @var array of counters for each log file type */
    private $counters = array();

    /**
     * Constructs the logger checking if the logs directory is writable.
     */
    public function __construct() {
		try {
            // can we write into the dir?
			if (!is_writable(BASEPATH . self::LOG_DIRECTORY)) {
				throw new Fari_Exception('Logs directory ' . self::LOG_DIRECTORY . ' is not writable.');
			}
		} catch (Fari_Exception $exception) { $exception->fire(); }
    }

    /**
     * Update this observer with subject values.
     * @param Fari_ApplicationLoggerSubject $subject
     */
    public function update(Fari_ApplicationLoggerSubject $subject) {
        // determine if type is set
        $type = $subject->type();

        try { if (empty($type)) {
            throw new Fari_Exception('You need to specify observer subject type.'); }
        } catch (Fari_Exception $exception) { $exception->fire(); }

        // update the counters kept for each type of log so we can switch between colors etc.
        $this->updateCounters($type);

        // write the information into a log file
        $this->writeLog($type, $subject->value);
    }

    /**
     * Type of the log file we are saving to
     * @param string $type
     */
    private function updateCounters($type) {
        assert('!empty($type) && is_string($type); // log file type better be set!');
        
        if (array_key_exists($type, $this->counters)) {
            // increase count
            $this->counters[$type]++;
        } else {
            // create & start off count
            $this->counters[$type] = 0;
        }
    }

    /**
 	 * Write content to a log file.
 	 * @param string $type
 	 * @param string $content
 	 */
 	private function writeLog($type, $content) {
		try {
            // is content a string?
			if (!is_string($content)) {
				throw new Fari_Exception('Content needs to be in string format!');
            }
		} catch (Fari_Exception $exception) { $exception->fire(); }

        // determine which color to use
        assert('array_key_exists($type, $this->counters); // log file type is not set yet!');
        $color = ($this->counters[$type] % 2 == 0) ? 'green' : 'blue';

        // open file for writing
		$logFile = fopen(BASEPATH . self::LOG_DIRECTORY . '/' . "{$type}.log", 'a');
        // append to file...
		fwrite($logFile, $this->formatTime($color) . "\n  {$this->rLogHighlight($content)}\n\n");
		// close file
		fclose($logFile);
	}

    /**
     * Will format & highlight timestamp
     * @param string $color to apply
     * @return string
     */
    private function formatTime($color) {
        $time = explode(' ', date(DATE_RSS, mktime()));
        $time[4] = $this->rLogHighlight($time[4], $color);
        return implode(' ', $time);
    }

    /**
     * RLog highlighting function
     * @param string $string to highlight
     * @param string $color to apply
     * @param boolean $underline apply?
     */
    private function rLogHighlight($string, $color='black', $underline=FALSE) {
        // apply underline?
        $underline = ($underline) ? '4' : '1';
        
        // color switcher
        switch ($color) {
            case "magenta":
                return "[{$underline};36;1m{$string}[0m";
            case "violet":
                return "[{$underline};35;1m{$string}[0m";
            case "blue":
                return "[{$underline};34;1m{$string}[0m";
            case "yellow":
                return "[{$underline};33;1m{$string}[0m";
            case "green":
                return "[{$underline};32;1m{$string}[0m";
            case "red":
                return "[{$underline};31;1m{$string}[0m";
            case "gray":
                return "[{$underline};30;1m{$string}[0m";
            case "black":
            default:
                return "[{$underline};29;1m{$string}[0m";
        }
    }
	
}