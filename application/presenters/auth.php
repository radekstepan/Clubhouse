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
class Auth_Presenter extends Fari_ApplicationPresenter {

    private $user = FALSE;
	
	public function startup() { 
        $this->bag->messages = Fari_Message::get();
    }
	
	public function actionIndex($p) {
        $this->render('error404/error404');
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
            
            $this->user = new User();
		    if ($this->user->authenticate($username, $password, $this->request->getPost('token'))) {
                $this->response->redirect('/');
            } else {
                Fari_Message::fail('Sorry, your username or password wasn\'t recognized');
                $this->bag->messages = Fari_Message::get();
            }
        }
        
		// create token & display login form
		$this->bag->token = Fari_FormToken::create();
		$this->render();
	}



    /********************* action logout *********************/



	/**
	 * Destroy user session
	 */
    public function actionLogout() {
        $this->user = new User();

        // as we are logging out, leave us from all rooms
        if ($this->user->isAuthenticated()) {
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
        }

        Fari_Message::success('You have been logged out');
        Fari_Message::get();

		$this->user->signOut();
        
		$this->render('login');
	}

}
