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
final class LobbyPresenter extends Fari_ApplicationPresenter {

    private $user = FALSE;
	
    /**
     * Applied automatically before any action is called.
     */
	public function filterStartup() {
        // is user authenticated? guests not allowed
        try {
            $this->user = new User(array('admin', 'registered'));
        } catch (UserNotAuthenticatedException $e) {
            if ($this->request->isAjax()) {
                $this->renderJson('bye');
            } else {
                $this->redirectTo('/login/');
            }
        } catch (UserNotAuthorizedException $e) {
            if ($this->request->isAjax()) {
                $this->renderJson('bye');
            } else {
                $this->redirectTo('/login/');
            }
        }
	}

    public function filterAjax() {
        // is this Ajax?
        if (!$this->request->isAjax()) {
            $this->renderTemplate('error404/javascript');
        }
    }



    /********************* view lobby *********************/



	/**
	 * List rooms in the app
	 */
	public function actionIndex($p) {
        $this->bag->isAdmin = $this->user->isAdmin();
        $this->bag->tabs = $this->user->inRooms();

        $this->renderAction('lobby');
	}



    /********************* action get rooms *********************/



	/**
	 * Rooms in the lobby
     *
     * @uses Ajax
	 */
    public function actionRooms() {
        $this->filterAjax();

        // clear out old users
        $system = new System();
        $this->renderJson($system->lobbyRooms($this->user->getId(), $this->user->isAdmin()));
    }



    /********************* action get number of users chatting *********************/



	/**
	 * Get the number of users chatting
     *
     * @uses Ajax
	 */
    public function actionUsers() {
        $this->filterAjax();

        $system = new System();
        $this->renderJson($system->userCount());
    }

}
