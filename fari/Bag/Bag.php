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
 * A values bag using magic __set() and __get().
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\Bag
 */
class Fari_Bag {

    /** @var array of elements, public for easy access */
    public $values = array();

    /**
     * Magic setter.
     * @param mixed $key to save value under
     * @param mixed $value to save
     */
    public function __set($key, $value) {
		$this->values[$key] = $value;
 	}

    /**
     * Magic getter.
     * @param mixed $key value is saved under
     * @return mixed saved value
     */
    public function __get($key) {
		return $this->values[$key];
 	}

    /**
     * Save a whole array of items.
     * @param array of key:value
     */
    public function set(array $array) {
        foreach ($array as $key => $value) $this->values[$key] = $value;
    }

}