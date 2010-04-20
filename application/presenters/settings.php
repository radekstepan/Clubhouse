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
 * Edit settings.
 * Access: account owner only
 *
 * @copyright Copyright (c) 2010 Radek Stepan
 * @package   Clubhouse\Presenters
 */
class SettingsPresenter extends Fari_ApplicationPresenter {

    private $user = FALSE;
    private $settings;
	
	public function startup() {
        // is user authenticated? guests not allowed
        $this->user = new User();
        if (!$this->user->isAuthenticated() OR !$this->user->isAdmin()) {
            $this->response->redirect('/login/');
        }

        $this->settings = new Settings();
	}



    /********************* view settings *********************/



    /**
	 * Display the settings screen
	 */
	public function actionIndex($p) {
        $this->bag->tabs = $this->user->inRooms();
        $this->bag->rooms = $this->settings->getRooms();

        $this->render('settings');
	}



    /********************* action delete room *********************/



    /**
	 * Delete the room
	 */
	public function actionDelete($roomId) {
        if ($this->request->isAjax()) {
            if (Fari_Filter::isInt($roomId)) {

                try {
                    $this->settings->deleteRoom($roomId);
                } catch (NotFoundException $e) {
                    //
                }
                
            }
        } else $this->render('error404/javascript');
	}

    public function actionReset() {
        $system = new System();
        $system->reset();
        $this->response->redirect('/');
    }

    public function actionBackup() {
        $backup = new Fari_BackupTable();
        $this->response($backup->dbToXML('users'), 'xml');
    }

}



class NotFoundException extends Exception {}