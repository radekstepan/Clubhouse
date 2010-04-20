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
 * Various text and number formatting functions.
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\Format
 */
class Fari_Format {

	/**#@+ default currency format reflecting the situation in financial markets... */
	const CURRENCY = 'GBP';
    /**#@-*/



    /********************* string *********************/



    /**
     * Converts 'egg_and_ham' into 'Egg And Ham'.
     * @param string $string Input string in underscore format
     * @return string String in title format
     */
    public static function titleize($string) {
        // explode by underscore
        $array = explode('_', $string);
        $result = '';

        // add space and uppercase first letter
        foreach ($array as $word) {
            $result .= ucwords($word) . ' ';
        }

        // remove trailing space and return
        return substr($result, 0, -1);
    }

	/**
	 * Highlight word(s) in text(s) (e.g.: search results).
	 *
	 * @param mixed $string Array/string to apply highlighting to
     * @param mixed $highlight Array/string that we want to highlight
     * @param array $whitelist Array of keys we want to highlight in $string array (optional)
	 * @return mixed Text with <span class="highlight"> applied
	 */
    public static function highlight($string, $highlight, array $whitelist=NULL) {
        // multiple words to highlight
        if (is_array($highlight)) {
            // sort by length
            $lengths = array(); $match = '';
            foreach ($highlight as $word) $lengths []= strlen($word);
            arsort(&$lengths);
            // form string to match sorted by word length
            foreach ($lengths as $key => $word) $match .= $highlight[$key] . '|'; $highlight = substr($match, 0, -1);
        }

        // the input text is an array...
        if (is_array($string)) {
            // highlight words in the array
            foreach ($string as $key => &$value) {
                // if we have a whitelist... use it
                if (!isset($whitelist) OR in_array($key, $whitelist))
                    $value = preg_replace("/($highlight)/i", '<span class="highlight">\1</span>', $value);
            } // ... or is a string
        } else $string = preg_replace("/($highlight)/i", '<span class="highlight">\1</span>', $string);

        return $string;
    }
    


    /********************* currency *********************/



    /**
     * Format input number based on currency settings to be used in HTML context.
     * @param int $number Number we want to format
     * @param string $currencyCode Currency code e.g., EUR
     * @return string Formatted number
     */
    public static function currency($number, $currencyCode) {
        // if the currency doesn't have a function defined for itself, give us a default
        $function = (!is_callable('self::to' . $currencyCode)) ? 'to' . self::CURRENCY : 'to' . $currencyCode;

        return self::$function($number);
	}

    /**
     * Format as GBP.
     * @param int $number
     * @return string Nicely formatted
     */
    private static function toGBP($number) {
        setlocale(LC_MONETARY, 'en_GB');
        $value = self::formatCurrency($number);
        return '&pound;' . $value;
    }

    /**
     * Format as CZK.
     * @param int $number
     * @return string Nicely formatted
     */
    private static function toCZK($number) {
        setlocale(LC_MONETARY, 'cs_CZ.UTF-8');
        $value = self::formatCurrency($number);
        return $value . '&nbsp;Kč';
    }
    
    /**
     * Format as EURO.
     * @param int $number
     * @return string Nicely formatted
     */
    private static function toEUR($number) {
        setlocale(LC_MONETARY, 'de_DE@euro');
        $value = self::formatCurrency($number);
        return $value . '&nbsp;&euro;';
    }
    
    /**
     * Format as USD.
     * @param int $number
     * @return string Nicely formatted
     */
    private static function toUSD($number) {
        setlocale(LC_MONETARY, 'en_US');
        $value = self::formatCurrency($number);
        return '&#36;' . $value;
    }

	/**
	 * Format our number after we've changed the locale.
	 * @param int $number
	 * @return string number in currency format
	 */
	private static function formatCurrency($number) {
        return @number_format($number, 2, ',', ' ');
	}



    /********************* time & date *********************/
    


    /**
     * Will format the date in tables as per our wishes, leavein date unchanged if $dateFormat not recognized
     * @param string $date Date in 'standard' format YYYY-MM-DD
     * @param string $dateFormat Target formatting to use (YYYY-MM-DD, DD-MM-YYYY, D MONTH YYYY, RSS, timestamp)
     * @return string Formatted date
     */
    public static function date($date, $dateFormat) {
        // check if input date is valid
        if (Fari_Filter::isDate($date)) {
            // split into params
            list ($year, $month, $day) = preg_split('/[-\.\/ ]/', $date);
        // else return input
        } else return $date;

        switch ($dateFormat) {
            case 'DD-MM-YYYY':
                return $day . '-' . $month . '-' . $year;
                break;

            case 'D MONTH YYYY':
                // get month's name
                $month = date('F', mktime(0, 0, 0, $month, 1));
                // make a nice day formatting, 9th, 10th etc.
                if ($day < 10) $day = substr($day, 1, 1);
                return $day . ' ' . $month . ' ' . $year;
                break;

            // in RSS feed context
            case 'RSS':
                return date(DATE_RSS, mktime(0, 0, 0, $month, $day, $year));
                break;

            case 'timestamp':
                return mktime(0, 0, 0, $month, $day, $year);
                break;

            // for unknown formats or default, just return
            default:
                return $date;
        }
    }

	/**
	 * Convert a time to distance from now.
	 *
	 * @param string $time A timestamp of a date (or convert into one from YYYY-MM-DD)
	 * @return string A formatted string of a date from now, e.g.: '3 days ago'
	 */
    public static function age($time) {
        // convert YYYY-MM-DD into a timestamp
        if (Fari_Filter::isDate($time)) {
            list ($year, $month, $day) = preg_split('/[-\.\/ ]/', $time);
            $time = mktime('1', '1', '1', $month, $day, $year);
        }

        // time now
        $now = time();
        // the difference
        $difference = $now - $time;
        // in the past?
        $ago = ($difference > 0) ? 1 : 0;
        // absolute value
        $difference = abs($difference);

        // switch case textual difference
        switch ($difference) {
            case ($difference < 60): $result = 'less than a minute'; break;
            case ($difference < 60 * 2): $result = '2 minutes'; break;
            case ($difference < 60 * 3): $result = '3 minutes'; break;
            case ($difference < 60 * 4): $result = '4 minutes'; break;
            case ($difference < 60 * 5): $result = '5 minutes'; break;
            case ($difference < 60 * 10): $result = '10 minutes'; break;
            case ($difference < 60 * 15): $result = '15 minutes'; break;
            case ($difference < 60 * 20): $result = '20 minutes'; break;
            case ($difference < 60 * 25): $result = '25 minutes'; break;
            case ($difference < 60 * 30): $result = 'half an hour'; break;
            case ($difference < 60 * 40): $result = '40 minutes'; break;
            case ($difference < 60 * 50): $result = '50 minutes'; break;
            case ($difference < 60 * 60): $result = 'an hour'; break;
            case ($difference < 60 * 60 * 24): $result = 'a day'; break;
            case ($difference < 60 * 60 * 24 * 7): $result = 'a week'; break;
            case ($difference < 60 * 60 * 24 * 7 * 2): $result = 'two weeks'; break;
            case ($difference < 60 * 60 * 24 * 7 * 3): $result = 'three weeks'; break;
            case ($difference < 60 * 60 * 24 * 30): $result = 'a month'; break;
            case ($difference < 60 * 60 * 24 * 60): $result = 'two months'; break;
            case ($difference < 60 * 60 * 24 * 90): $result = 'three months'; break;
            case ($difference < 60 * 60 * 24 * 120): $result = 'four months'; break;
            case ($difference < 60 * 60 * 24 * 182): $result = 'half a year'; break;
            case ($difference < 60 * 60 * 24 * 365): $result = 'a year'; break;
            case ($difference < 60 * 60 * 24 * 365 * 2): $result = 'two years'; break;
            case ($difference < 60 * 60 * 24 * 365 * 3): $result = 'three years'; break;
            case ($difference < 60 * 60 * 24 * 365 * 4): $result = 'four years'; break;
            case ($difference < 60 * 60 * 24 * 365 * 5): $result = 'five years'; break;
            case ($difference < 60 * 60 * 24 * 365 * 6): $result = 'six years'; break;
            case ($difference < 60 * 60 * 24 * 365 * 7): $result = 'seven years'; break;
            case ($difference < 60 * 60 * 24 * 365 * 10): $result = 'a decade'; break;
            case ($difference < 60 * 60 * 24 * 365 * 20): $result = 'two decades'; break;
            case ($difference < 60 * 60 * 24 * 365 * 30): $result = 'three decades'; break;
            case ($difference < 60 * 60 * 24 * 365 * 40): $result = 'four decades'; break;
            case ($difference < 60 * 60 * 24 * 365 * 50): $result = 'half a century'; break;
            case ($difference < 60 * 60 * 24 * 365 * 100): $result = 'a century'; break;
            default: $result = 'more than a century'; break;
        }

        return ($ago) ? $result . ' ago' : 'in ' . $result;
    }



    /********************* size *********************/



	/**
	 * Convert bytes to human readable format (based on CodeIgniter).
	 * @param int $bytes Value in bytes
	 * @return string nicely formatted into b, kB, MB etc.
	 */
	public static function bytes($bytes) {
		// terabytes
        if ($bytes >= 1000000000000) {
			$bytes = round($bytes / 1099511627776, 1);
			$unit = ('TB');
        // gigabytes
        } elseif ($bytes >= 1000000000) {
			$bytes = round($bytes / 1073741824, 1);
			$unit = ('GB');
		// megabytes
        } elseif ($bytes >= 1000000) {
			$bytes = round($bytes / 1048576, 1);
			$unit = ('MB');
		// kilobytes
        } elseif ($bytes >= 1000) {
			$bytes = round($bytes / 1024, 1);
			$unit = ('kb');
		// bytes
        } else {
			return number_format($bytes) . ' b';
		}
        
		return number_format($bytes, 1) . ' ' . $unit;
	}

}