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
 * Boolean input checks using filters.
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\Filter
 */
class Fari_Filter {
	
	/**
	 * Integer validation.
	 * @param int $input
	 * @param array $range First parameter is minimum range, second is maximum range
	 * @return boolean TRUE if number is an integer
	 */
	public static function isInt($input, array $range=NULL) {
		// have we specified limits
		if (isset($range)) {
			return filter_var($input, FILTER_VALIDATE_INT, array(
                    'options' => array(
                        'min_range'=>$range[0],
						'max_range'=>$range[1]
                        )
                    ));
		} else {
			return filter_var($input, FILTER_VALIDATE_INT);
		}
	}
	
	/**
	 * URL validation.
	 * @param string $input
	 * @return boolean TRUE if URL is valid
	 */
	public static function isURL($input) {
		return filter_var($input, FILTER_VALIDATE_URL);
	}
	
	/**
	 * Email validation (uses CodeIgniter regex).
	 * @param string $input
	 * @return boolean TRUE if email is valid
	 */
	public static function isEmail($input) {
		return (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix",
                $input)) ? FALSE : TRUE;
	}
	
	/**
	 * Will check if input date in format YYYY-MM-DD is valid.
	 * @param string $input Date string in format YYYY-MM-DD
	 * @return boolean TRUE if date is valid
	 */
	public static function isDate($input) {
		// split input date into params
		list ($year, $month, $day) = preg_split('/[-\.\/ ]/', $input);
		// check date using builtin function
		return checkdate($month, $day, $year);
	}

    /**
     * Will make a check if input string contains alphanumeric chars only.
     * @param string $input
     * @return boolean TRUE if input is alphanumeric
     */
    public static function isAlpha($input) {
        return ($input === preg_replace("/[^a-zA-Z0-9\s]/", "", $input));
    }
	
}