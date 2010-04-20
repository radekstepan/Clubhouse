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
 * Decoding of input to output.
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\Decode
 */
class Fari_Decode {

	/**
	 * Use in HTML context.
	 * @param string $input
	 * @return string
	 */
	public static function html($input) {
		return html_entity_decode($input, ENV_QUOTES, 'UTF-8');
	}

    /**
	 * Use on form data submitted via JavaScript (weierophinney.net).
	 * @param array/string $input
	 * @return array/String Decoded accents etc.
	 */
    public static function javascript($input) {
        // process an array
        if (is_array($input)) {
            foreach ($input as $key => $val) {
                $input[$key] = javascript($val);
            }
        // process a separate element
        } else {
            // convert urlencoded hex values to decimal and then to a character
            $input = preg_replace('/%([0-9a-f]{2})/ie', 'chr(hexdec($1))', (string)$input);
        }
        return $input;
    }

	/**
	 * Use in URL context.
	 * @param string $input
	 * @return string
	 */
	public static function url($input) {
		return urldecode($input);
	}

	/**
	 * Strip slashes from input array or a string.
	 * @param string/array
	 * @return string/array
	 */
	public static function slashes($input) {
		// strip slashes in an array
		if (is_array($input)) {
			// array walk...
			foreach ($input as $key => $value) {
				// ... and strip slashes
				$stripped_array[$key] = stripslashes($value);
			}
			return $stripped_array;
		// strip slashes in a string
		} else return stripslashes($input);
	}

	/**
	 * Decode diacritics after being encoded (in JavaScript context for ex.).
	 * @param string $input
	 * @return string
	 */
	public static function diacritics($input) {
		return str_replace(array('%25u011B', '%25u0161', '%25u010D', '%25u0159', '%25u010F', '%25u0165',
					 '%25u0148', '%E1', '%E9', '%ED', '%F3', '%FA', '%25u016F', '%25u017E', '%FD',
					 '%25u011A', '%25u0158', '%25u0160', '%25u010C', '%25u010E', '%25u0164',
					 '%25u0147', '%25u017D'
					 ),
				   array('ě', 'š', 'č', 'ř', 'ď', 'ť', 'ň', 'á', 'é', 'í', 'ó', 'ú', 'ů', 'ž', 'ý',
					 'Ě', 'Ř', 'Š', 'Č', 'Ď', 'Ť', 'Ň', 'Ž', 'Á', 'É', 'Í', 'Ý', 'Ó', 'Ú'
					 ),
				   $input);
	}

	/**
	 * Decode characters like ., (, ), [, ], @, ? etc. into HTML entities (in JavaScript context for ex.).
	 * @param string $input
	 * @return string
	 */
	public static function chars($input) {
		return str_replace(array('%20', '%21', '%22', '%23', '%24', '%25',
					 '%26', '%27', '%28', '%29', '%2A', '%2B',
					 '%2C', '%2D', '%2E', '%2F', '%3A', '%3B',
					 '%3C', '%3D', '%3E', '%3F', '%40', '%5F',
					 '%5C', '%5B', '%5D', '%0A',
					 ),
				   array('&#32;', '&#33;', '&#34;', '&#35;', '&#36;', '&#37;',
					 '&#38;', '&#39;', '&#40;', '&#41;', '&#42;', '&#43;',
					 '&#44;', '&#45;', '&#46;', '&#47;', '&#58;', '&#59;',
					 '&#60;', '&#61;', '&#62;', '&#63;', '&#64;', '&#95;',
					 '&#92;', '&#91;', '&#93;', '<br />',
					 ),
				   $input);
	}

	/**
	 * Decodes accents from UTF-8 into ANSI (PHPro.org).
	 * @param string $input
	 * @return string
	 */
	public static function accents($input) {
		$accented = array(
				  'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ă', 'Ą',
				  'Ç', 'Ć', 'Č', 'Œ',
				  'Ď', 'Đ',
				  'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ă', 'ą',
				  'ç', 'ć', 'č', 'œ',
				  'ď', 'đ',
				  'È', 'É', 'Ê', 'Ë', 'Ę', 'Ě',
				  'Ğ',
				  'Ì', 'Í', 'Î', 'Ï', 'İ',
				  'Ĺ', 'Ľ', 'Ł',
				  'è', 'é', 'ê', 'ë', 'ę', 'ě',
				  'ğ',
				  'ì', 'í', 'î', 'ï', 'ı',
				  'ĺ', 'ľ', 'ł',
				  'Ñ', 'Ń', 'Ň',
				  'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ő',
				  'Ŕ', 'Ř',
				  'Ś', 'Ş', 'Š',
				  'ñ', 'ń', 'ň',
				  'ò', 'ó', 'ô', 'ö', 'ø', 'ő',
				  'ŕ', 'ř',
				  'ś', 'ş', 'š',
				  'Ţ', 'Ť',
				  'Ù', 'Ú', 'Û', 'Ų', 'Ü', 'Ů', 'Ű',
				  'Ý', 'ß',
				  'Ź', 'Ż', 'Ž',
				  'ţ', 'ť',
				  'ù', 'ú', 'û', 'ų', 'ü', 'ů', 'ű',
				  'ý', 'ÿ',
				  'ź', 'ż', 'ž',
				  'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р',
				  'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'р',
				  'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я',
				  'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я'
				  );
		$replace = array(
				 'A', 'A', 'A', 'A', 'A', 'A', 'AE', 'A', 'A',
				 'C', 'C', 'C', 'CE',
				 'D', 'D',
				 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'a', 'a',
				 'c', 'c', 'c', 'ce',
				 'd', 'd',
				 'E', 'E', 'E', 'E', 'E', 'E',
				 'G',
				 'I', 'I', 'I', 'I', 'I',
				 'L', 'L', 'L',
				 'e', 'e', 'e', 'e', 'e', 'e',
				 'g',
				 'i', 'i', 'i', 'i', 'i',
				 'l', 'l', 'l',
				 'N', 'N', 'N',
				 'O', 'O', 'O', 'O', 'O', 'O', 'O',
				 'R', 'R',
				 'S', 'S', 'S',
				 'n', 'n', 'n',
				 'o', 'o', 'o', 'o', 'o', 'o',
				 'r', 'r',
				 's', 's', 's',
				 'T', 'T',
				 'U', 'U', 'U', 'U', 'U', 'U', 'U',
				 'Y', 'Y',
				 'Z', 'Z', 'Z',
				 't', 't',
				 'u', 'u', 'u', 'u', 'u', 'u', 'u',
				 'y', 'y',
				 'z', 'z', 'z',
				 'A', 'B', 'B', 'r', 'A', 'E', 'E', 'X', '3', 'N', 'N', 'K', 'N', 'M', 'H', 'O', 'N', 'P',
				 'a', 'b', 'b', 'r', 'a', 'e', 'e', 'x', '3', 'n', 'n', 'k', 'n', 'm', 'h', 'o', 'p',
				 'C', 'T', 'Y', 'O', 'X', 'U', 'u', 'W', 'W', 'b', 'b', 'b', 'E', 'O', 'R',
				 'c', 't', 'y', 'o', 'x', 'u', 'u', 'w', 'w', 'b', 'b', 'b', 'e', 'o', 'r'
				 );
		return str_replace($accented, $replace, $input);
	}

}