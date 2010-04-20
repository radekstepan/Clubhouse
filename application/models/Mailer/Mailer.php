<?php if (!defined('FARI')) die();

/**
 * Clubhouse, a 37Signals' Campfire port
 *
 * @copyright Copyright (c) 2010 Radek Stepan
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link      http://radekstepan.com
 * @category  Clubhouse
 */



/**
 * Invitations mailer.
 *
 * @copyright Copyright (c) 2010 Radek Stepan
 * @package   Clubhouse\Models\Mailer
 */
class Mailer {

    private $mailer;

    public function __construct() {
        $this->mailer = new Fari_MailPear();
    }

    public function sendInvitation() {
        // fetch the newly invited user
        $users = new Table('users');
        $user = $users->findFirst('id DESC')->where(array('role' => 'invited'));
        
        // have we actually retrieved the user?
        if (!Fari_Filter::isInt($user->id)) throw new UserNotFoundException();

        // form the email
        $this->mailer->addTo($user->email)->addFrom('radek.stepan@gmail.com', 'Clubhouse');
        $this->mailer->setSubject('You\'re invited to join Clubhouse');

        $this->mailer->setBody(
            "Hi {$user->first},\nYou're invited to join Clubhouse, our group chat system.\n\n".
            "Click this link to get started:\n" .
            url('account/invitation/' . $user->invitation . '/', FALSE, TRUE) .
            "\n\nThanks"
        );

        //$this->mailer->send();
    }
        
}
