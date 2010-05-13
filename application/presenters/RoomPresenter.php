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
 * Room activities.
 * Access: restricted to signed-in users
 *
 * @copyright Copyright (c) 2010 Radek Stepan
 * @package   Clubhouse\Presenters
 */
class RoomPresenter extends Fari_ApplicationPresenter {

    private $user = FALSE;



    /********************* filters *********************/



    /**
     * Applied automatically before any action is called.
     */
	public function filterStartup() {
        // is user authenticated?
        try {
            $this->user = new User();
        } catch (UserNotAuthenticatedException $e) {
            $this->response->redirect('/login/');
        }
        
        // we will use our ID in the view to match our messages
        $this->bag->userId = $this->user->getId();
        $this->bag->shortName = $this->user->getShortName();
	}

    /**
     * Check for AJAX request and roomId is an integer.
     * @param integer $roomId
     */
    public function filterRoomId($roomId) {
        // is this Ajax?
        if ($this->request->isAjax()) {
            if (!Fari_Filter::isInt($roomId)) {
                $this->response('bye', 'json');
            }
        } else {
            $this->render('error404/javascript');
        }
    }



    /********************* view display room *********************/



    /**
	 * Display the room
	 */
	public function actionIndex($roomId) {
        if (Fari_Filter::isInt($roomId)) {

            $system = new System();

            try {
                // is this even a real room?
                $system->isRoom($roomId);

                // are we allowed to enter?
                $this->user->canEnter($roomId);

                $this->render('room', $roomId);

            } catch (RoomNotFoundException $e) {
                $this->render('invalid');

            } catch (UserNotAuthorizedException $e) {

                $this->render('permissions');
            }
            
        } else {
            $this->render('invalid');
        }
	}

    public function renderRoom($roomId) {
        $time = mktime();

        // is the user already in the room?
        if (!$this->user->inRoom($roomId)) {
            // enter them into the room
            $this->user->enterRoom($roomId, $time);

            // say that the user has entered
            $message = new MessageSpeak($roomId, $time);
            $message->enter($roomId, $time, $this->user->getShortName());
        }

        $messages = new Message();
        $this->bag->messages = $messages->get($roomId);
        // do we have some messages in the history?
        $this->bag->history = $messages->haveMore($roomId);

        $this->bag->earlier = true;
        
        $this->bag->tabs = $this->user->inRooms();
        $this->bag->isAdmin = $this->user->isAdmin();

        $this->bag->host = $this->host;

        $room = new Room();
        $this->bag->room = $room->getDescription($roomId);
    }



    /********************* view leave room *********************/



    /**
	 * Leave the room
	 */
	public function actionLeave($roomId) {
        if (Fari_Filter::isInt($roomId)) {
            // are we actually in the room?
            if ($this->user->inRoom($roomId)) {
                // remove us from participants
                $this->user->leaveRoom($roomId);

                // message about it
                $time = mktime();
                $message = new MessageSpeak($roomId, $time);
                $message->leave($roomId, $time, $this->user->getShortName());

                // the user might be a guest in which case show her a slightly different exit message
                if ($this->user->isGuest()) {
                    $this->render('bye');
                }
            }
        }
        // redir either way
        $this->response->redirect('/');
	}



    /********************* action create a room *********************/



    /**
	 * Create a new room
     *
     * @uses Ajax
	 */
	public function actionCreate() {
        // is this Ajax?
        if ($this->request->isAjax()) {
            $name =  $this->request->getPost('name');
            $desc =  $this->request->getPost('description');

            $room = new Room();
            // generate a room name for us
            if (empty($name)) $name = $room->newName();

            // save room
            $room->create($name, $desc);

            // 'refresh' rooms listing much like in a lobby
            $system = new System();
            $this->response($system->lobbyRooms($this->user->getId(), $this->user->isAdmin()), 'json');
        } else {
            $this->render('error404/javascript');
        }
	}



    /********************* action lock room *********************/



    /**
	 * Lock the room, no new users will be allowed to enter
     *
     * @uses Ajax
	 */
	public function actionLock($roomId) {
        $this->filterRoomId($roomId);

        try {
            // are we allowed to enter?
            $this->user->canEnter($roomId);

            $room = new Room();
            $status = $room->lock($roomId);

            // message about it withour showing the timestamp in transcript
            $time = mktime();
            $message = new MessageSpeak($roomId, $time, '1');
            $message->lock($roomId, $this->user->getShortName(), $status);

        } catch (UserNotAuthorizedException $e) {
            $this->response('bye', 'json');
        }
	}



    /********************* action poll room *********************/



    /**
	 * Get name, topic, participants, locked and guest statuses and files for the current room
     *
     * @uses Ajax
	 */
    public function actionPoll($roomId) {
        $this->filterRoomId($roomId);

        try {
            // are we allowed to enter?
            $this->user->canEnter($roomId);

            $room = new Room();
            $this->response($room->poll($roomId), 'json');

        } catch (UserNotAuthorizedException $e) {
            $this->response('bye', 'json');
        }
    }



    /********************* action set new topic *********************/



    /**
	 * Set a new topic or clear it
     *
     * @uses Ajax
	 */
	public function actionTopic($roomId) {
        $this->filterRoomId($roomId);

        try {
            // are we allowed to enter?
            $this->user->canEnter($roomId);

            $topic = $this->request->getPost('topic');

            $room = new Room();
            $room->topic($roomId, $topic = $this->request->getPost('topic'));

            // message about it
            $time = mktime();
            $message = new MessageSpeak($roomId, $time);
            $message->topic($roomId, $this->user->getShortName(), $topic);

            $this->response($topic, 'json');

        } catch (UserNotAuthorizedException $e) {
            $this->response('bye', 'json');
        }
	}



    /********************* action set guest status *********************/



    /**
	 * Provide guest access to the room
     *
     * @uses Ajax
	 */
	public function actionGuest($roomId) {
        $this->filterRoomId($roomId);

        try {
            // are we allowed to enter?
            $this->user->canEnter($roomId);

            $room = new Room();
            $this->response($room->guest($roomId, $this->user->getShortName()), 'json');

        } catch (UserNotAuthorizedException $e) {
            $this->response('bye', 'json');
        }
	}

}