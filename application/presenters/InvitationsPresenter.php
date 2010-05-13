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
 * Invite new users.
 * Access: account owner only
 *
 * @copyright Copyright (c) 2010 Radek Stepan
 * @package   Clubhouse\Presenters
 */
class InvitationsPresenter extends Fari_ApplicationPresenter {

    private $user = FALSE;
    private $accounts;

    /**
     * Applied automatically before any action is called.
     */
    public function filterStartup() {
        // is user authenticated? account owner only
        try {
            $this->user = new User('admin');

        } catch (UserNotAuthenticatedException $e) {
            $this->response->redirect('/login/');

        } catch (UserNotAuthorizedException $e) {
            $this->render('Error404/error404');

        }
        
        $this->accounts = new Accounts();
    }



    /********************* view new invitation *********************/



	/**
	 * Invitation form and processing of invited user details
	 */
    public function actionIndex($p) {
        if ($this->request->isPost()) {
            $firstName = Fari_Decode::accents($this->request->getPost('first'));
            $lastName = Fari_Decode::accents($this->request->getPost('last'));
            $email = $this->request->getPost('email');

            if (!Fari_Filter::isEmail($email) OR empty($firstName)) {
                $this->bag->message = array('status' => 'fail',
                    'message' => 'Whoops, make sure you enter a full name and proper email address.');
                $this->bag->first = $this->request->getRawPost('first');
                $this->bag->last = $this->request->getRawPost('last');
                $this->bag->email = $this->request->getRawPost('email');
            } else {
                $name = $this->accounts->newInvitation($firstName, $lastName, $email);

                // mail the instructions
                $mail = new Mailer();
                try {
                    $mail->sendInvitation();
                } catch (UserNotFoundException $e) {
                    $this->response->redirect('/error404/');
                }

                Fari_Message::success("$name is now added to your account. An email with instructions was sent to $email");

                $this->response->redirect('/users/');
            }
        }

        $this->bag->tabs = $this->user->inRooms();
        $this->render('new');
	}
    
}