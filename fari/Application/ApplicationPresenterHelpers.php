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
 * Helpers to use withing presenter context.
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\Application
 */
final class Fari_ApplicationPresenterHelpers {

    /** @var Fari_Bag of values */
    private $bag;

    /**
     * Constructor gets access to a bag of values so we can save values to it.
     * @param Fari_Bag $bag
     */
    public function __construct(Fari_Bag &$bag) {
        $this->bag = $bag;
    }

    public function __set($method, $params) {
        try {
            // match the request
            if (!preg_match('/^(flash)(\w+)$/', $method, $matches)) {
                throw new Fari_Exception("Call to undefined helper {$method}");
            } else {
                // determine what to do
                switch ($matches[1]) {
                    case 'flash':
                        $this->flash(strtolower($matches[2]), $params);
                        break;
                }
            }
        } catch (Fari_Exception $exception) { $exception->fire(); }
 	}



    /********************* message flashing *********************/



    /**
     * Our 'accessor' when saving a message from a presenter, keys are not overwritten.
     * @param string $key to save under, comes from __set()
     * @param string $text to save
     */
	private function flash($key, $text) {
        assert('is_string($text); // you can only flash strings and thongs');
        $this->saveFlashMessageInSession(array('key' => $key, 'text' => $text));
    }

    /**
     * Save the message into session
     * @param array $message under key => value
     * @param integer $pointer
     * @return void
     */
    private function saveFlashMessageInSession(array $message, $pointer=0) {
		assert('is_int($pointer); // pointer needs to be an integer');
        
        // if a message is already set at this pointer...
		if (isset($_SESSION['Fari\Flash\\' . APP_SALT . '\\' . $pointer])) {
			// set in the next available slot
			$pointer++;
			$this->saveFlashMessageInSession($message, $pointer);
		} else {
			// save message
			$_SESSION['Fari\Flash\\' . APP_SALT . '\\' . $pointer] = $message;
			return;
		}
    }

}