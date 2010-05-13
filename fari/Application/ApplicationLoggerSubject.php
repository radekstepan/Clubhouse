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
 * An abstract template of observer subject.
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\Application
 */
abstract class Fari_ApplicationLoggerSubject {

    /**
     * Attach observer object.
     * @param Fari_ApplicationLogger $observer
     */
    abstract function attach(Fari_ApplicationLogger $observer);

    /**
     * Detach observer object.
     * @param Fari_ApplicationLogger $observer
     */
    abstract function detach(Fari_ApplicationLogger $observer);

    /**
     * Notify observers with some value.
     * @param string $value
     */
    abstract function notify($value);

    /**
     * Return type of this observer subject.
     * @return string
     */
    abstract function type();
}