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
final class SettingsPresenter extends Fari_ApplicationPresenter {

    private $user = FALSE;
    private $settings;

    /**
     * Applied automatically before any action is called.
     */
	public function filterStartup() {
        // is user authenticated? account owner only
        try {
            $this->user = new User('admin');

        } catch (UserNotAuthenticatedException $e) {
            $this->redirectTo('/login/');

        } catch (UserNotAuthorizedException $e) {
            $this->renderTemplate('Error404/error404');

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

        $this->renderAction('settings');
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
                } catch (RoomNotFoundException $e) {
                    //
                }
                
            }
        } else $this->renderTemplate('error404/javascript');
	}

    public function actionReset() {
        $system = new System();
        $system->reset();
        $this->redirectTo('/');
    }

    public function actionBackup() {
        $backup = new Fari_BackupTable();
        $this->renderXml($backup->dbToXML('users'));
    }

}