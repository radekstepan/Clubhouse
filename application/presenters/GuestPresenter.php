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
 * Guest room.
 * Access: public if an invitation code matches a room
 *
 * @copyright Copyright (c) 2010 Radek Stepan
 * @package   Clubhouse\Presenters
 */
class GuestPresenter extends Fari_ApplicationPresenter {
    
    private $guestUser;
    private $room;
	
	public function startup() {
        $this->room = new Room();
    }



    /********************* view guest room *********************/



    /**
	 * Display the room from a guest's perspective
	 */
	public function actionIndex($guestCode) {
        try {
            // get the room
            $this->room->getGuestRoom($guestCode = Fari_Escape::text($guestCode));

            // is user authenticated?
            $this->guestUser = new User();
            // is user authorized?
            $this->guestUser->canEnter($this->room['id']);

        // the room does not exist
        } catch (RoomNotFoundException $e) {
            $this->render('room/invalid');

        // we haven't signed in
        } catch (UserNotAuthenticatedException $e) {
            $this->bag->code = $guestCode;
            // show a form to enter a name for the new guest
            $this->render('account/guest');

        // we cannot enter this room
        } catch (UserNotAuthorizedException $e) {
            $this->bag->code = $guestCode;
            // show a form to enter a name for the new guest
            $this->render('account/guest');
            
        }
                    
        // we are already in
        $time = mktime();

        // is the user already in the room?
        if (!$this->guestUser->inRoom($this->room['id'])) {
            // not in the room... is it locked?
            if ($this->room['locked']) {
                $system = new System();
                $this->render('room/locked');
            } else {
                // enter them into the room
                $guestUser->enterRoom($this->room['id'], $time);

                // say that the user has entered
                $message = new MessageSpeak();
                $message->enter($this->room['id'], $time, $this->guestUser->getShortName());
            }
        }

        // all other fails captured...
        // show a 'guest' view
        $this->render('room/guest');
    }

    public function renderGuest() {
        $messages = new Message();
        $this->bag->messages = $messages->get($this->room['id']);

        $this->bag->room = $this->room->getDescription($this->room['id']);

        $this->bag->userId = $this->guestUser->getId();
        $this->bag->shortName = $this->guestUser->getShortName();
    }



    /********************* action create account *********************/



    /**
     * Get code and name from the form and create a new user for us (generate username)
     */
    public function actionCreate() {
        $name = Fari_Decode::accents($this->request->getPost('name'));
        $code = $this->request->getPost('code');
        if (!empty($name)) {

            $name = explode(' ', $name);
            // do we have a 'long' name?
            if (count($name) > 1) {
                $short = $name[0] . ' ' . substr(end($name), 0, 1) . '.';
                $long = implode(' ', $name);
                $surname = end($name);
                $name = $name[0];
            } else {
                $short = $long = $name = $name[0];
                $surname = '';
            }

            // generate a username
            $username = Fari_Escape::slug($long) . Fari_Tools::randomCode(10);

            $db = Fari_Db::getConnection();

            // insert the user in a guest role
            $userId = $db->insert('users', array('short' => $short, 'long' => $long, 'name' => $name,
                    'surname' => $surname, 'role' => 'guest', 'username' => $username));

            // log them in automatically
            Fari_AuthenticatorSimple::forceAuthenticate($username);

            // give them permissions to enter this room
            $room = $db->selectRow('rooms', 'id', array('guest' => $code));
            if (!empty($room)) $db->insert('user_permissions', array('room' => $room['id'], 'user' => $userId));
        }
        // redirect to the room, if we've ailed will be asked for guest's name again
        $this->response->redirect('/g/' . $code);
    }

}