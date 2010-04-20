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
 * User login and signoff.
 * Access: public
 *
 * @copyright Copyright (c) 2010 Radek Stepan
 * @package   Clubhouse\Presenters
 */
class AuthPresenter extends Fari_ApplicationPresenter {

    private $user = FALSE;
	
	public function actionIndex($p) {
        $this->render('Error404/error404');
    }



    /********************* view login *********************/



	/**
	 * User sign-in/login
	 */
	public function actionLogin() {
        // authenticate user if form data POSTed
        if ($this->request->getPost('username')) {
            $username = Fari_Decode::accents($this->request->getPost('username'));
            $password = Fari_Decode::accents($this->request->getPost('password'));

            try {
                $this->user = new UserLogin($username, $password, $this->request->getPost('token'));
            } catch (UserNotAuthenticatedException $e) {
                Fari_Message::fail('Sorry, your username or password wasn\'t recognized');

            }

            $this->response->redirect('/');
        }

        $this->bag->messages = Fari_Message::get();

		// create token & display login form
		$this->bag->token = Fari_FormToken::create();
		$this->render();
	}



    /********************* action logout *********************/



	/**
	 * Destroy user session
	 */
    public function actionLogout() {
        try {
            // we might not be signed in actually
            $this->user = new User();
        } catch (UserNotAuthenticatedException $e) {
            Fari_Message::success('You are already logged out');
        }

        // as we are logging out, leave us from all rooms
        if ($this->user != NULL) {
            $inRooms = $this->user->inRooms();
            if (!empty($inRooms)) {
                
                $time = mktime();
                foreach ($inRooms as $room) {
                    // message about it
                    $message = new MessageSpeak($room, $time);
                    $message->leave($room, $time, $this->user->getShortName());
                }
                // remove us from participants
                $room = new Room();
                $room->removeParticipant($this->user->getId());
            }

            Fari_Message::success('You have been logged out');

            $this->user->signOut();
        }

        $this->bag->messages = Fari_Message::get();

        $this->bag->token = Fari_FormToken::create();
		$this->render('login');
	}

}