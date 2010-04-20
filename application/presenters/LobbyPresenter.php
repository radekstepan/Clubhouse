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
 * Main lobby listing rooms.
 * Access: restricted to signed-in users, not guests!
 *
 * @copyright Copyright (c) 2010 Radek Stepan
 * @package   Clubhouse\Presenters
 */
class LobbyPresenter extends Fari_ApplicationPresenter {

    private $user = FALSE;
	
	public function startup() {
        // is user authenticated? guests not allowed
        try {
            $this->user = new User(array('admin', 'registered'));
        } catch (UserNotAuthenticatedException $e) {
            if ($this->request->isAjax()) {
                $this->response('bye', 'json');
            } else {
                $this->response->redirect('/login/');
            }
        } catch (UserNotAuthorizedException $e) {
            if ($this->request->isAjax()) {
                $this->response('bye', 'json');
            } else {
                $this->response->redirect('/login/');
            }
        }
	}



    /********************* view lobby *********************/



	/**
	 * List rooms in the app
	 */
	public function actionIndex($p) {
        $this->bag->isAdmin = $this->user->isAdmin();
        $this->bag->tabs = $this->user->inRooms();
        
        $this->render('lobby');
	}



    /********************* action get rooms *********************/



	/**
	 * Rooms in the lobby
     *
     * @uses Ajax
	 */
    public function actionRooms() {
        // is this Ajax?
        if ($this->request->isAjax()) {
            // clear out old users
            $system = new System();
            $this->response($system->lobbyRooms($this->user->getId(), $this->user->isAdmin()), 'json');
        } else {
            $this->render('error404/javascript');
        }
    }



    /********************* action get number of users chatting *********************/



	/**
	 * Get the number of users chatting
     *
     * @uses Ajax
	 */
    public function actionUsers() {
        // is this Ajax?
        if ($this->request->isAjax()) {
            $system = new System();
            
            $this->response($system->userCount(), 'json');
        } else {
            $this->render('error404/javascript');
        }
    }

}
