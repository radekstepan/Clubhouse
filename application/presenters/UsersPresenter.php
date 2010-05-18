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
 * List, delete users and edit permissions
 * Access: account owner only
 *
 * @copyright Copyright (c) 2010 Radek Stepan
 * @package   Clubhouse\Presenters
 */
final class UsersPresenter extends Fari_ApplicationPresenter {

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
            $this->redirectTo('/login/');

        } catch (UserNotAuthorizedException $e) {
            $this->renderTemplate('Error404/error404');

        }

        $this->accounts = new Accounts();
	}



    /********************* view list users *********************/



	/**
	 * List users and their permissions
	 */
	public function actionIndex($p) {
        $this->bag->tabs = $this->user->inRooms();

        $system = new System();

        $this->bag->users = $system->userPermissions();
        $this->renderAction('listing');
	}



    /********************* view permissions edit *********************/



    /**
     * Edit access permissions to rooms for a user
     *
     * @uses no checking! We only allow admin here so no checking on sanity of the form posted!
     */
    public function actionPermissions($userId) {
        if (Fari_Filter::isInt($userId)) {
            // fetch the user we are editing
            $result = $this->accounts->getUser($userId);
            if (empty($result)) {
                $this->renderAction('error');
            } else {
                $this->bag->user = $result;
            }

            // fetch the rooms
            $settings = new Settings();
            $this->bag->rooms = $settings->getRooms();

            // fetch the permissions
            $permissions = $this->accounts->getUserPermissions($userId);

            // 'beautify' into an array
            $result = array(); foreach ($permissions as $perm) array_push($result, $perm['room']);
            $this->bag->permissions = $result;

            // we are posting new permissions
            if ($this->request->isPost()) {
                // to make it easier on us wipe their permissions for a second (will that break/kick a user?)
                $this->accounts->deleteUserPermissions($userId);

                // and now just save those that are 'on'
                foreach ($this->request->getPost() as $roomId => $permission) {
                    if ($permission == 'on') $this->accounts->insertUserRoomPermissions($userId, $roomId);
                }

                // back to the users listing
                $this->redirectTo('/users/');
            } else {
                // display the form
                $this->renderAction();
            }
        }
    }



    /********************* action delete user *********************/



    /**
	 * Delete a user other than the owner
     *
     * @uses Ajax
	 */
    public function actionDelete($userId) {
        // is this Ajax?
        if ($this->request->isAjax()) {
            $adminUser = $this->user->getAdmin();

            if (Fari_Filter::isInt($userId) && $userId !=  $adminUser['id']) {
                try {
                    $this->accounts->deleteUser($userId);
                } catch (UserNotFoundException $e) {
                    //
                }
            }
        } else {
            $this->renderTemplate('error404/javascript');
        }
    }

}