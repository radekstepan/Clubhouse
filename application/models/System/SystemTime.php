<?php if (!defined('FARI')) die();

/**
 * Clubhouse, a 37Signals' Campfire port
 *
 * @copyright Copyright (c) 2010 Radek Stepan
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link      http://radekstepan.com
 * @category  Clubhouse
 */



/**
 * System time functions.
 *
 * @copyright Copyright (c) 2010 Radek Stepan
 * @package   Clubhouse\Models\System
 */
class SystemTime {

    /**
     * Return a date formatted entry for a db from a timestamp.
     * @param string $time DB entry date
     */
    public static function timestampToDate($time=NULL) {
        return (isset($time)) ? date("Y-m-d", $time) : date("Y-m-d", mktime());
    }

    /**
     * Return a date formatted entry for a transcript timestamp, e.g.: 'Tuesday, January 21'
     * @param string $time DB entry date
     */
    public static function timestampToNiceDate($time=NULL) {
        return (isset($time)) ? date("l, F j", $time) : date("l, F j", mktime());
    }

    /**
     * Generate a nice time from a timestamp, e.g.: '11:05 AM'
     * @return string
     */
    public static function timestampToTime($time=NULL) {
        if (!isset($time)) $time = mktime();
        return date("g", $time) . ':' . date("i", $time) . ' ' . date("A", $time);
    }

    /**
	 * Round the time to the nearest lowest 12th of an hour
     * @param integer $time UNIX timestamp
     * @return integer Rounded timestamp
	 */
    public static function roundTimestamp($time) {
        $minutes = date("i", $time);
        $minutes = (substr($minutes, -1) > 4) ? substr($minutes, 0, 1) . '5' : substr($minutes, 0, 1) . '0';
        // strip the leading zero
        $minutes = ($minutes < 10) ? substr($minutes, 1) : $minutes;

        // hour minute second month day year
        return mktime(date("G", $time), $minutes, 0, date("n", $time), date("j", $time), date("Y", $time));
    }

}