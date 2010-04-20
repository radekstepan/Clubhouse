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
 * Mailer template, compose and send messages.
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\Mail
 */
abstract class Fari_Mail {

    /**#@+ end of line character, linefeed */
    const EOL = "\r\n";
    /**#@-*/

    /** @var prefix, host and port number of the server */
    public $prefix;
    public $host;
    public $port;

    /** @var credentials */
    public $login;
    public $pass;

    /** @var array used to build headers */
    private $headers = array();

    /** @var body of the message */
    private $body;

    /** @var email character set */
    public $charset = 'UTF-8';

    /**
     * Set from field.
     * @param string $email Email address
     * @param string $name Optional name
     * @return Fari_Mail subclass
     */
    public function addFrom($email, $name=NULL) {
        $this->setHeader('From', $this->formatEmail($email, $name));
        return $this;
    }

    /**
     * Add to field.
     * @param string $email Email address
     * @param string $name Optional name
     * @return Fari_Mail subclass
     */
    public function addTo($email, $name=NULL) {
        $this->setHeader('To', $this->formatEmail($email, $name));
        return $this;
    }

    /**
     * Set email subject.
     * @param string $subject Text
     * @return Fari_Mail subclass
     */
    public function setSubject($subject) {
		$this->setHeader('Subject', $subject);
		return $this;
    }

    /**
     * Set body of the messsage
     * @param string $subject Text
     * @param boolean $text escape text if set to true
     * @return Fari_Mail subclass
     */
    public function setBody($body, $text=FALSE) {
        $this->body = ($text) ? Fari_Escape::text($body) : $body;
        return $this;
    }

    /**
     * Message body accessor
     * @return string
     */
    public function getBody() {
        return $this->body;
    }

    /**
     * Set email header in values array.
     * @param string $type
     * @param string $value
     */
    private function setHeader($type, $value) {
        $this->headers[$type] = trim($value);
    }

    /**
     * Retrieve message header.
     * @param string $type
     * @return string
     */
    public function getHeader($type) {
        return $this->headers[$type];
    }
    
    /**
     * Setup host, port, login and password.
     * @param string $host
     * @param integer $port
     * @param string $username
     * @param string $password
     */
    public function setConnection($host=NULL, $port=NULL, $username=NULL, $password=NULL) {
        // we are not passing the server connection details
        if (!isset($host) OR !isset($port)) {
            try {
                if (!defined('SMTP_HOST') OR !defined('SMTP_PORT')) {
                    throw new Fari_Exception("Email connection details not provided");
                }
                // set constants
                $this->host = SMTP_HOST;
                $this->port = SMTP_PORT;
            } catch (Fari_Exception $exception) { $exception->fire(); }
        } else {
            // set the passed host & port number, we will fail on connect if they are incorrect
            // do we have SSL or TLS prefix?
            if (($prefix = strpos($host, '://')) !== FALSE) {
                $this->prefix = substr($host, 0, $prefix + 3);
                $this->host = substr($host, $prefix + 3);
            } else {
                $this->host = $host;
            }
            $this->port = $port;
        }

        // do we have credentials defined?
        if (defined('SMTP_USERNAME') && defined('SMTP_PASSWORD')) {
            $this->username = trim(SMTP_USERNAME);
            $this->password = trim(SMTP_PASSWORD);
        }
        // are we passing credentials?
        if (isset($username) && isset($password)) {
            $this->username = trim($username);
            $this->password = trim($password);
        }
    }

    /**
     * Format email and name string to build an email ready header.
     * @param string $email Email address
     * @param string $name Optional name
     * @return string
     */
    private function formatEmail($email, $name) {
        try {
            // check email validity
            if (empty($email) OR !Fari_Filter::isEmail($email)) {
                throw new Fari_Exception("\"$email\" is not a valid email address.");
            } else {
                $email = "<$email>";
                // have we provided the name?
                if (!empty($name)) {
                    // only alphanumeric characters allowed
                    if (!Fari_Filter::isAlpha($name)) {
                        throw new Fari_Exception("\"$name\" needs to contain only alphanumeric characters.");
                    } else {
                        // prepend name before the email
                        return '"' . $name . '" ' . $email;
                    }
                } else {
                    // add brackets around the email
                    return $email;
                }
            }
        } catch (Fari_Exception $exception) { $exception->fire(); }
    }

    /**
     * Sender.
     */
    abstract function send();
	
}
