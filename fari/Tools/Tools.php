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
 * A toolbag of useful functions.
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\Tools
 */
class Fari_Tools {
		
	/**
     * Generate a (fairly) random code of optional length.
     * @param integer max length of the code we want to generate (optional)
     */
    public function randomCode($length=20) {
        return substr(md5(uniqid(rand(), TRUE)), 0, $length);
    }
	
}