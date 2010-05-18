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
 * New invited user account setup.
 * Access: public to users with invitation code
 *
 * @copyright Copyright (c) 2010 Radek Stepan
 * @package   Clubhouse\Presenters
 */
final class AccountPresenter extends Fari_ApplicationPresenter {

    private $accounts;

    /**
     * Applied automatically before any action is called.
     */
    public function filterStartup() {
        $this->accounts = new Accounts();
    }

	public function actionIndex($p) {
        $this->render('Error404/error404');
    }



    /********************* view create *********************/



	/**
	 * Account setup form upon receiving an invitation.
	 */
	public function actionCreate($invitationCode) {
        $userAccount = $this->accounts->getInvitedUser($this->bag->code = $code = Fari_Escape::text($invitationCode));
        
        if (empty($code) OR empty($userAccount)) {
            $this->render('expired');
        } else {
            $this->bag->account = $userAccount;
            $this->render();
        }
	}

    public function renderCreate() {
        // get the account owner's full name
        $accounts = new Accounts();
        $this->bag->admin = $accounts->getAdmin();
    }



    /********************* action check account *********************/



    /**
     * Check for uniqueness of the username
     *
     * @param string $username URL encoded username
     */
    public function actionCheckUsername($username) {
        // is this Ajax?
        if ($this->request->isAjax()) {
            // URL decode & filter out username
            $username = Fari_Escape::text(Fari_Decode::url($username));

            if (empty($username)) {
                $this->response("The username can't be empty.", 'json');
            } else {
                // alphanumeric only?
                if (!Fari_Filter::isAlpha($username)) {
                    $this->response("Only alphanumeric characters are allowed.", 'json');
                } else {
                    // do we have a match?
                    if (!$this->accounts->isUsernameUnique($username)) {
                        $this->response("The username \"$username\" is unavailable, sorry.", 'json');
                    } else {
                        $this->response('', 'json');
                    }
                }
            }
        } else {
            $this->render('error404/javascript');
        }
    }



    /********************* view complete account *********************/



	/**
	 * A 'Welcome' screen for a new user
	 */
    public function actionComplete() {
        $result = $this->accounts->getInvitedUser($invitationCode = $this->request->getPost('code'));
        
        if (empty($invitationCode) OR empty($result)) {
            $this->render('error');
        } else {
            $username = $this->request->getPost('username');
            $password1 = $this->request->getPost('password1');
            $password2 = $this->request->getPost('password2');

            // some fail conditions
            if (!$this->accounts->isUsernameUnique($username) OR $password1 !== $password2) {
                $this->render('error');
            }

            // set the new credentials
            try {
                $this->accounts->setInvitedUserCredentials($username, $password, $invitationCode);
            } catch (UserNotFoundException $e) {
                $this->render('error');
            }

            // force authenticate the user
            $user = new User();
            $user->forceAuthenticate($username);

            $this->bag->account = $result;
            $this->render();
        }
	}
    
}