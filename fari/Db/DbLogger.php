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
 * An observer subject used when logging queries.
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\Db
 */
class Fari_DbLogger extends Fari_ApplicationLoggerSubject {

    /**#@+ type of the subject */
    const SUBJECT_TYPE = "db";
    /**#@-*/

    /** @var array of observers to notify of changes */
    private $observers = array();

    /** @var value observers get from us */
    public $value;

    /**
     * Attach observer object.
     * @param Fari_ApplicationLogger $observer
     */
    function attach(Fari_ApplicationLogger $observer) {
        array_push($this->observers, $observer);
    }

    /**
     * Detach observer object.
     * @param Fari_ApplicationLogger $observer
     */
    function detach(Fari_ApplicationLogger $observer) {
        foreach($this->observers as $key => $value) {
            if ($value == $observer) {
                unset($this->observers[$key]);
            }
        }
    }

    /**
     * Notify observers with some values.
     * @param string $value
     */
    function notify($value) {
        // only notify in development environment...
        if (Fari_ApplicationEnvironment::isDevelopment()) {
            // save the info
            $this->value = $value;

            // notify each of our observers
            foreach($this->observers as $observer) {
                $observer->update($this);
            }
        }
    }

    /**
     * Return type of this observer subject.
     * @return string
     */
    public function type() {
        return self::SUBJECT_TYPE;
    }
    
}