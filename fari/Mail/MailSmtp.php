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
 * Mailer using SMTP (untested).
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\Mail
 */
class Fari_MailSmtp extends Fari_Mail {

    /** @var SMTP stream */
    public $stream;

    /**
     * Setup host, port, login and password.
     * @param string $host
     * @param integer $port
     * @param string $username
     * @param string $password
     */
    public function __construct($host=NULL, $port=NULL, $username=NULL, $password=NULL) {
        $this->setConnection($host, $port, $username, $password);
    }

    /**
     * Sender.
     * @return TRUE on succesfull sending, otherwise Fari Exception is thrown
     */
    public function send() {
        // setup connection with the server
        $this->connectServer();
        $this->fputs('HELO  ' . $this->host, '250');
        $this->authenticateServer();

        // from and to fields
        $this->fputs('MAIL FROM:' . end(explode(' ', $this->getHeader('From'))), '250');
        $this->fputs('RCPT TO:' . end(explode(' ', $this->getHeader('To'))), '250');
        // data part with headers and body of the message
        $this->fputs('DATA', '354');
        $this->fputs(
            "MIME-Version: 1.0" . self::EOL .
            "X-Mailer: Fari Framework" . self::EOL .
            "Content-type: text/html; charset={$this->charset}" . self::EOL .
            "From: {$this->getHeader('From')}" . self::EOL .
            "To: {$this->getHeader('To')}" . self::EOL .
            "Date: " . date(DATE_RFC2822) . self::EOL .
            "Subject: {$this->getHeader('Subject')}" . self::EOL . self::EOL .
            $this->getBody() . self::EOL . '.'
            , '250');
        // goodbye
        $this->fputs('QUIT', '221');

        return TRUE;
    }

    /**
     * Helpful message sender to the server, will check result too.
     * @param string $string To send to the server
     * @param integer $okResponse Response we expect from the server
     */
    private function fputs($string, $okResponse) {
        fputs($this->stream, $string . self::EOL);
		$this->checkServerResponse(&$this->stream, $okResponse);
    }

    /**
     * Setup server connection.
     */
    private function connectServer() {
        try {
            // create stream
            if (!$this->stream = fsockopen($this->prefix . $this->host, $this->port, $errno, $errstr, 3)) {
                throw new Fari_Exception("Could not connect to SMTP: $errno $errstr");
            }
            // check result
            $this->checkServerResponse(&$this->stream, '220');
        } catch (Fari_Exception $exception) { $exception->fire(); }
    }

    /**
     * An optional server authentication.
     */
    private function authenticateServer() {
        // are we authenticating?
        if (!empty($this->username)) {
            // request authentication
            $this->fputs("AUTH LOGIN" . self::EOL, 334);
            // send login
            $this->fputs(base64_encode($this->username) . self::EOL, 334);
            // send password
            $this->fputs(base64_encode($this->password) . self::EOL, 235);
        }
    }

    /**
     * Check downstream result from the server
     * @param <type> $stream Stream with the server
     * @param integer $okResponse Integer response we expect, otherwise fail...
     */
    private function checkServerResponse($stream, $okResponse) {
		try {
            if (substr($response = fgets($stream, 256), 0, 3) != $okResponse) {
                throw new Fari_Exception("SMTP stream has failed: $response");
            }
        } catch (Fari_Exception $exception) { $exception->fire(); }
    }
	
}
