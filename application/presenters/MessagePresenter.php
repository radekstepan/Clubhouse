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
 * Sending and getting messages.
 * Access: restricted to signed-in users through AJAX requests
 *
 * @copyright Copyright (c) 2010 Radek Stepan
 * @package   Clubhouse\Presenters
 */
class MessagePresenter extends Fari_ApplicationPresenter {

    private $user = FALSE;
    private $room;
	
	public function startup() {
        // is this Ajax?
        if ($this->request->isAjax()) {
            // is user authenticated and allowed to speak?
            $this->user = new User();
            if (!$this->user->isAuthenticated() OR !$this->user->canEnter($roomId)) {
                // user is fetching new messages... not for long
                $this->response('bye', 'json');
            }

            $this->room = new Room();
        } else {
            $this->render('error404/javascript');
        }
	}
	
	public function actionIndex($p) { $this->response->redirect('/error404/'); }



    /********************* action send a message *********************/



    /**
	 * Send a message from a room
     *
     * @uses Ajax
	 */
    public function actionSpeak($roomId) {
        $text = Fari_Escape::text(Fari_Decode::javascript($this->request->getRawPost('text')));
        if (!empty($text)) {
            $time = mktime();

            // a text message
            $message = new MessageSpeak($roomId, $time);
            $message->text($roomId, $time, $this->user->getShortName(), $this->user->getId(), $text);

            // the message might be saved under wrong room id, but activity updater will kick us...
            try {
                $this->room->updateUserActivity($roomId, $time, $this->user->getId());
            } catch (NotFoundException $e) {
                $this->response('bye', 'json');
            }
        }
    }



    /********************* action retrieve messages *********************/



    /**
	 * Retrieve last messages from a room
     *
     * @uses Ajax
	 */
    public function actionGet($roomId, $lastMessage) {
        if (Fari_Filter::isInt($roomId) && Fari_Filter::isInt($lastMessage)) {
            $time = mktime();

            $messages = new Message();
            $messages = $messages->getLatest($lastMessage, $roomId);

            $system = new System();

            try {
                $this->room->updateUserActivity($roomId, $time, $this->user->getId());
            } catch (NotFoundException $e) {
                $this->response('bye', 'json');
            }

            $this->response($messages, 'json');
        } else $this->response('bye', 'json');
    }



    /********************* action set highlight status *********************/



    /**
	 * Message highlighting
     *
     * @uses Ajax
	 */
    public function actionHighlight($messageId) {
        if (Fari_Filter::isInt($messageId)) {
            $time = mktime();

            $messages = new Message();

            try {
                $result = $messages->switchHighlight($messageId);
            } catch (NotFoundException $e) {
                // you mess with us... we mess with you
                $this->response('bye', 'json');
            }

            $this->response($result, 'json');
        } else $this->response('bye', 'json');
    }

}



class NotFoundException extends Exception {}