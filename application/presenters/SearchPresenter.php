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
 * Search through messages.
 * Access: restricted to signed-in users
 *
 * @copyright Copyright (c) 2010 Radek Stepan
 * @package   Clubhouse\Presenters
 */
final class SearchPresenter extends Fari_ApplicationPresenter {

    private $user = FALSE;

    /**
     * Applied automatically before any action is called.
     */
	public function filterStartup() {
        // is user authenticated?
        try {
            $this->user = new User();
        } catch (UserNotAuthenticatedException $e) {
            $this->redirectTo('/login/');
        }
	}



    /********************* view results *********************/



    /**
	 * Search results
	 */
	public function actionIndex($p) {
        if ($this->request->getPost('q')) {
            $this->bag->tabs = $this->user->inRooms();

            $this->bag->q = $q = $this->request->getPost('q');

            $messages = new Message();
            $result = $messages->search($q);

            // render empty results
            if (empty($result)) $this->renderAction('empty');
            else $this->bag->results = $result;
            
            $this->renderAction('results');
        } else {
            $this->redirectTo('/transcripts/');
        }
	}

}