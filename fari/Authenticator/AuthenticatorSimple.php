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
 * A simple implementation of AuthenticatorTemplate.
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\Authenticator
 */
class Fari_AuthenticatorSimple extends Fari_AuthenticatorTemplate {

    /** @var string column in a database with credentials */
    private $credentialsColumn = 'username';

    /** @var holds credentials string */
    private $credentialsString;

    /** @var Fari_Db */
    private $db;

    /**
     * Setup database connection.
     * @param optional db connection, otherwise defaults to Fari_Db
     */
   	public function __construct($db=NULL) {
        $this->db = (!isset($db)) ? Fari_Db::getConnection() : $db;
    }



    /********************* input escaping *********************/



    /**
     * Escape username.
     * @param string
     */
    public function prepareUsername($username) {
        $this->username = Fari_Escape::text($username);
    }

    /**
     * Escape password.
     * @param string
     */
    public function preparePassword($password) {
        // SHA-1 encrypt
        $this->password = sha1(Fari_Escape::text($password));
    }

    /**
     * Escape SESSION data.
     * @param string
     */
    public function prepareSession($sessionString) {
        return Fari_Escape::text($sessionString);
    }



    /********************* matching *********************/



    /**
     * Match credentials with a db table 'users'.
     * @return boolean TRUE if we have a match
     */
    public function matchUser() {
		// db row select on 'users' table
        $result = $this->db->selectRow(
            'users',
            $this->credentialsColumn,
            array('username' => $this->username, 'password' => $this->password)
        );

        // save credentials string
        $this->credentialsString = $result[$this->credentialsColumn];

        return isset($result[$this->credentialsColumn]);
    }

    /**
     * Match SESSION with a resource of users.
     * @param string
     */
    public function matchSessionCredentials($credentialsString) {
        $result = $this->db->selectRow('users', '*', array($this->credentialsColumn => $credentialsString));

		return (isset($result[$this->credentialsColumn]));
    }

    /**
     * Validate token data through Fari_FormToken.
     * @param mixed $token
     * @return boolean TRUE on success
     */
    public function validateToken($token) {
        return Fari_FormToken::isValid($token);
    }

    /**
     * Build a credentials string.
     * @return string
     */
    public function credentialsString() {
        return $this->credentialsString;
    }



    /********************* authentication results *********************/



    /**
     * Succesfull authentication.
     * @return boolean TRUE
     */
    public function authenticateSuccess() {
        return TRUE;
    }

    /**
     * Failed authentication.
     * @return boolean TRUE
     */
    public function authenticateFail() {
        return FALSE;
    }
	
}
