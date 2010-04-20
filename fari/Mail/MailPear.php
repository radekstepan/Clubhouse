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
 * Mailer using PEAR's Mail:: package.
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\Mail
 */
class Fari_MailPear extends Fari_Mail {

    /**
     * Setup host, port, login and password.
     * @param string $host
     * @param integer $port
     * @param string $username
     * @param string $password
     */
    public function __construct($host=NULL, $port=NULL, $username=NULL, $password=NULL) {
        // include PEAR Mail:: if possible...
        try {
            // get include paths
            $paths = explode(':/', get_include_path());
            foreach ($paths as $path) {
                // 'fix' directory
                if (substr($path, 0, 1) == '.') {
                    // this directory
                    $path = '';
                } else {
                    // directory from the root
                    $path = "/{$path}/";
                }

                // can we call PEAR Mail:: ?
                if (file_exists("{$path}Mail.php")) {
                    // include
                    require_once "{$path}Mail.php";
                    // switch
                    $found = TRUE;
                    // we are done here
                    break;
                }
            }
            if ($found !== TRUE) throw new Fari_Exception("PEAR Mail:: has not been found");
        } catch (Fari_Exception $exception) { $exception->fire(); }

        // setup the connection details
        $this->setConnection($host, $port, $username, $password);
    }

    /**
     * Sender.
     * @return TRUE on succesfull sending, otherwise Fari Exception is thrown
     */
    public function send() {
        $headers = array(
            'From' => $this->getHeader('From'),
            'To' => $this->getHeader('To'),
            'Subject' => $this->getHeader('Subject'),
        );
        
        // connection
        $smtp = Mail::factory(
            'smtp',
            array(
                'host' => $this->prefix.$this->host,
                'port' => $this->port,
                'auth' => true,
                'username' => $this->username,
                'password' => $this->password,
                'timeout' => 3
            )
        );
        
        try {
            // did we build the email?
            if (PEAR::isError($smtp)) {
                throw new Fari_Exception("Failed to build mail: {$smtp->getMessage()}");
            } else {
                // send
                $mail = $smtp->send($this->getHeader('To'), $headers, $this->getBody());
                // did all went fine?
                if (PEAR::isError($mail)) {
                    throw new Fari_Exception("Failed to send mail: {$mail->getMessage()}");
                }
            }
        } catch (Fari_Exception $exception) { $exception->fire(); }

        return TRUE;
    }
	
}
