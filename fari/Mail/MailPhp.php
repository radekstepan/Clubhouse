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
 * Mailer using sendmail.
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\Mail
 */
class Fari_MailPhp extends Fari_Mail {

    /**
     * Sender.
     */
    public function send() {
        $to = substr(end(explode(' ', $this->getHeader('To'))), 1, -1);
        $subject = $this->getHeader('Subject');
        $message = $this->body;
        // form headers
        $headers = "
            MIME-Version: 1.0" . self::EOL .
          "X-Mailer: Fari Framework" . self::EOL .
          "Content-type: text/html; charset={$this->charset}" . self::EOL .
          "From: {$this->getHeader('From')}" . self::EOL .
          "Reply-To: {$this->getHeader('From')}" . self::EOL;
        try {
            // send it
            if (mail($to, $subject, $message, $headers) != 1) {
                throw new Fari_Exception("Failed to send mail");
            }
        } catch (Fari_Exception $exception) { $exception->fire(); }

        // this gives us absolutely no guarantees...
        return TRUE;
    }
	
}
