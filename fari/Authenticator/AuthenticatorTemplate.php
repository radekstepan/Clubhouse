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
 * Template for user authentication.
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\Authenticator
 */
abstract class Fari_AuthenticatorTemplate {

   	/**#@+ cache files directory & extension */
	const SESSION_CREDENTIALS_STORAGE = 'Fari\User\Credentials\\';
    /**#@-*/

    /** @var string username to work with */
    private $username;

    /** @var string password */
    private $password;



    /********************* input escaping *********************/



    /**
     * Escape username.
     * @param string
     */
    abstract function prepareUsername($username);

    /**
     * Escape password.
     * @param string
     */
    abstract function preparePassword($password);

    /**
     * Escape SESSION data.
     * @param string
     */
    abstract function prepareSession($sessionString);



    /********************* matching *********************/


    /**
     * Match credentials with a resource of users.
     * @return boolean TRUE if we have a match
     */
    abstract function matchUser();

    /**
     * Match SESSION with a resource of users.
     * @param string
     */
    abstract function matchSessionCredentials($credentialsString);

    /**
     * Validate token data
     * @param mixed $token
     * @return boolean TRUE on success
     */
    abstract function validateToken($token);

    /**
     * Build a credentials string.
     * @return string
     */
    abstract function credentialsString();



    /********************* authentication results *********************/



    /**
     * Succesfull authentication.
     */
    abstract function authenticateSuccess();

    /**
     * Failed authentication.
     */
    abstract function authenticateFail();



    /********************* authentication *********************/



	/**
	 * Authenticate user.
	 * @param string $username
	 * @param string $password
	 * @param string $token optional form token
	 * @return authenticationFail() or authenticationSuccess()
	 */
	public function authenticate($username, $password, $token=NULL) {
        // escape input
        $this->prepareUsername($username);
        $this->preparePassword($password);

        // if credentials provided and token is valid
        if (isset($username, $password) && ($this->validateToken($token))) {
			// select a matching row from a resource
			if ($this->matchUser()) {
                // save user into a session
                $_SESSION[self::SESSION_CREDENTIALS_STORAGE . APP_SALT] = $this->credentialsString();
                // success
                return $this->authenticateSuccess();
            }
		}
        // fail
        return $this->authenticateFail();
	}

    /**
     * Will 'force' authenticate a user by saving their credentials in the session.
     * @param string $creedentialsString String to save into credentials session
     */
    public function forceAuthenticate($creedentialsString) {
        $_SESSION[self::SESSION_CREDENTIALS_STORAGE . APP_SALT] = $creedentialsString;
    }
	
	/**
	 * Check if user is authenticated (calls getCredentials()).
	 * @return authenticationFail() or authenticationSuccess()
	 */
	public function isAuthenticated() {
		@$unsafe = $this->getCredentials();
		// are credentials set in a session?
		if (isset($unsafe)) {
            // match credentials with a resource
            if ($this->matchSessionCredentials($this->prepareSession($unsafe))) {
                // success
                return $this->authenticateSuccess();
            }
		}
		// no credentials in the session, fail
		return $this->authenticateFail();
	}
	
	/**
	 * Get credentials saved in a session.
	 * @return string Credentials stored during authentication
	 */
	public function getCredentials() {
		return $_SESSION[self::SESSION_CREDENTIALS_STORAGE . APP_SALT];
	}



    /********************* sign out *********************/



	/**
	 * Sign out user from the system.
	 */
	public function signOut() {
        // sign out by destroying the session credentials
		unset($_SESSION[self::SESSION_CREDENTIALS_STORAGE . APP_SALT]);
	}
	
}