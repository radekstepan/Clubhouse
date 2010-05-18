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
 * Manages messages to be flashed to the user. Uses session to preserve content across redirects.
 *
 * @deprecated
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\Message
 */
class Fari_Message {
	
    /**#@+ session storage namespace */
	const SESSION_STORAGE = 'Fari\Message\\';
    /**#@-*/
	
	/**
	 * Set a message under notify status.
	 * @param string $message Message to save
	 */
	public static function notify($message) {
		self::setMessage(array('status' => 'notify', 'message' => $message));
	}

	/**
	 * Set a message under fail status.
	 * @param string $message Message to save
	 * @return void
	 */	
	public static function fail($message) {
		self::setMessage(array('status' => 'fail', 'message' => $message));
	}
	
	/**
	 * Set a message under success status.
	 * @param string $message Message to save
	 * @return void
	 */
	public static function success($message) {
		self::setMessage(array('status' => 'success', 'message' => $message));
	}
	
	/**
	 * Function overloading to 'dynamically' set message status and text.
	 * @uses PHP 5.3.0
	 *
	 * @param string $method Method called and not implemented
	 * @param array $arguments Arguments passed
	 */
	public static function __callStatic($method, array $arguments) {
		self::setMessage(array('status' => $method, 'message' => $arguments[0]));
	}
	
	/**
	 * Will set a message in the session not overwriting current contents.
	 * @param array $message Array message with status/text to save
	 * @param int $messagesPointer Used when saving more messages, leave off
	 * @return void
	 */
	private static function setMessage(array $message, $messagesPointer=0) {
		// if a message is already set at this pointer...
		if (isset($_SESSION[self::SESSION_STORAGE . APP_SALT . '\\' . $messagesPointer])) {
			// set in the next available slot
			$messagesPointer++;
			self::setMessage($message, $messagesPointer);
		} else {
			// save message
			$_SESSION[self::SESSION_STORAGE . APP_SALT . '\\' . $messagesPointer] = $message;
			return;
		}
	}
	
	/**
	 * Will return an array of messages to be flashed to the user in View.
	 * @return array Messages with status/message fields if we have some
	 */
	public static function get() {
		$messages = array();
		
		// traverse the whole session looking for messages
		foreach ($_SESSION as $key => $value) {
			// our messages
			if (strstr($key, self::SESSION_STORAGE . APP_SALT)) {
				// 'save' message to the array
				array_push($messages, $value);
				// 'delete' the message
				unset($_SESSION[$key]);
			}
		}
		
		// return
		if (!empty($messages)) return $messages;
	}
	
}