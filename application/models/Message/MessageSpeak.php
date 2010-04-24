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
 * Main message sending/creating class.
 *
 * @copyright Copyright (c) 2010 Radek Stepan
 * @package   Clubhouse\Models\Message
 */
class MessageSpeak extends Fari_ApplicationModel {

    /** @var if the room is locked, don't log the messages in a transcript */
    private $lockedRoom;
    private $locked;
    private $time;
    private $niceTime;
    private $date;
    private $roomId;

	/**
	 * Constructor, creating a timestamp type message (whenever any action happens)
     *
     * Make sure that activity column has a number in it!
     *
     * @param integer $roomId Id of the room (optional)
     * @param integer $time UNIX timestamp (optional)
     * @param integer $hide Set to one if you don't want a message to appear in transcript but room is not locked yet
     * @return void
	 */
	function __construct($roomId=null, $time=null, $hide=0) {
        // call parent constructor to set db connection
        $this->db = Fari_Db::getConnection();
        parent::__construct($this->db);

        // we don't want to timestamp a room as active if a user is leaving for example...
        if (isset($roomId)) {
            $this->roomId = $roomId;

            $this->timestampRoom($time, $hide);
        }
	}

    private function timestampRoom($time, $hide) {
        // update room activity
        $this->updateRoomTimestamp($this->roomId, $time);

        // do we have a message in the last 5 minutes?
        $this->time = SystemTime::roundTimestamp($time);

        $result = $this->db->selectRow('rooms', 'id, locked', "id={$this->roomId} AND activity >= {$this->time}");
        $this->lockedRoom = ($result['locked'] == 1) ? '1' : '0';

        $this->date = SystemTime::timestampToDate($this->time);
        $this->niceTime = SystemTime::timestampToTime($this->time);

        // hide this message?
        if ($hide > 0) {
            $this->locked = '1';
        } else {
            $this->locked = $this->lockedRoom;
        }

        if ($result == NULL) {
            // update last room activity, we need for correct timestamping in a locked room
            $this->updateRoomActivity();
            $this->messageTimestamp();
        } else {
            // there might have been a message in the last 5, but in a locked room
            if ($this->lockedRoom == '0') { // ... now that it's unlocked
                $this->messageUnlockedTimestamp();
            }
        }
    }

    private function updateRoomTimestamp() {
        $this->db->update('rooms', array('timestamp' => $this->time), array('id' => $this->roomId));
    }

    private function updateRoomActivity() {
        // update last room activity, we need for correct timestamping in a locked room
        $this->db->update('rooms', array('activity' => $this->time), array('id' => $this->roomId));
    }

    private function messageTimestamp() {
        $this->lastId = $this->db->insert('messages', array('room' => $this->roomId, 'type' => 'timestamp',
            'text' => $this->niceTime, 'date' => $this->date, 'user' => '', 'locked' => $this->locked));
    }

    private function messageUnlockedTimestamp() {
        $result = $this->db->select('messages', 'id, locked',
            "room={$this->roomId} AND date='{$this->date}' AND text='{$this->niceTime}'
                 AND type='timestamp' AND locked='0'");
        // there is such a message, created in a locked room
        if (empty($result)) {
            // create one in the unlocked room :)
            $this->db->insert('messages', array('room' => $this->roomId, 'type' => 'timestamp',
                'text' => $this->niceTime, 'date' => $this->date, 'user' => '', 'locked' => $this->locked));
        }
    }

	/**
	 * Plain old boring text message
     *
     * @param integer $roomId Id of the room
     * @param integer $time UNIX timestamp
     * @param string $shortName Our short name
     * @param integer $userId Our user id
     * @param string $text Text of the message we are sending
     * @return void
	 */
    public function text($roomId, $time, $shortName, $userId, $text) {
        // determine if this is the first message of the day...
        $this->newTranscript($roomId);

        // code in the message
        $text = $this->textCode($text);

        $text = $this->textLinks($text);

        $date = SystemTime::timestampToDate($time);

        $this->db->insert('messages', array('text' => $text, 'user' => $shortName, 'room' => $roomId, 'type' => 'text',
            'userId' => $userId, 'date' => $date, 'locked' => $this->lockedRoom, 'highlight' => 0));

        // is this the first time we are saying something today?
        $this->newTranscriptUser($roomId, $userId, $date);
    }

    private function newTranscript($roomId) {
        // ... in an unlocked room
        $result = $this->db->selectRow('messages', 'user', array('room' => $roomId, 'date' => $this->date,
                'locked' => '0', 'type' => 'text'));
        if (empty($result)) {
            // new message of the day, insert transcript entry
            $this->db->insert('room_transcripts', array('room' => $roomId, 'date' => $this->date,
                    'deleted' => 0, 'niceDate' => SystemTime::timestampToNiceDate($this->time)));
        }
    }

    private function newTranscriptUser($roomId, $userId, $date) {
        $sql = array('room' => $roomId, 'user' => $userId, 'date' => $date);
        $result = $this->db->selectRow('transcript_users', 'user', $sql);
        if (empty($result)) {
            $transcript = $this->db->selectRow('room_transcripts', 'key', array('date' => $date, 'room' => $roomId));
            if (!empty($transcript)) { // ... in case the transcript is deleted or something
                $sql['transcript'] = $transcript['key'];
                $this->db->insert('transcript_users', $sql);
            }
        }
    }

    private function textCode($text) {
        return (strpos($text, '{') && strpos($text, '}')) ? "<pre><code>$text</code></pre>" : $text;
    }

    private function textLinks($text) {
        // URL highlight
        $urls = explode(' ', $text); $containsLink = FALSE;
        foreach ($urls as &$link) {
            if (Fari_Filter::isURL($link)) {
                $containsLink = TRUE;

                // do we have a YouTube video?
                // source: http://www.youtube.com/watch?v=nBBMnY7mANg&feature=popular
                // target: <img src="http://img.youtube.com/vi/nBBMnY7mANg/0.jpg" alt="0">
                if (stripos(strtolower($link), 'youtube') !== FALSE) {
                    $url = parse_url($link);
                    parse_str($url[query], $query);
                    // replace link with an image 'boosted' link :)
                    $link = '<a class="youtube" target="_blank" href="' . $link .
                            '"><img src="http://img.youtube.com/vi/' . $query['v'] . '/0.jpg" alt="YouTube"></a>';
                } else {
                    // plain old link
                    $link = '<a class="blue" href="' . $link . '">' . $link . '</a>';
                }

                // convert so we can insert into DB
                $link = Fari_Escape::html($link);
            }
        }
        if ($containsLink) $text = implode(' ', $urls);

        return $text;
    }

	/**
	 * Say that a user has entered the room
     *
     * @param integer $roomId Id of the room
     * @param integer $time UNIX timestamp
     * @param string $shortName Short version of the user's name
     * @return void
	 */
    public function enter($roomId, $time, $shortName) {
        $this->db->insert('messages', array('room' => $roomId, 'user' => $shortName, 'type' => 'room',
                'text' => 'has entered the room', 'date' => SystemTime::timestampToDate($time),
                'locked' => $this->lockedRoom));
    }

    /**
	 * Say that a user has left the room
     *
     * @param integer $roomId Id of the room
     * @param integer $time UNIX timestamp
     * @param string $shortName Short version of the user's name
     * @return void
	 */
    public function leave($roomId, $time, $shortName) {
        $this->db->insert('messages', array('room' => $roomId, 'user' => $shortName, 'type' => 'room',
                'text' => 'has left the room', 'date' => SystemTime::timestampToDate($time),
                'locked' => $this->lockedRoom));
    }

    /**
	 * Say that a room has been un-/locked
     *
     * @param integer $roomId Id of the room
     * @param string $shortName Short version of the user's name
     * @param integer $lock 1 if a room is now locked
     * @return void
	 */
    public function lock($roomId, $shortName, $lock) {
        $lock = ($lock > 0) ? '' : 'un';
        $this->db->insert('messages', array('room' => $roomId, 'user' => $shortName, 'type' => 'lock',
                'text' => "has ${lock}locked the room", 'date' => SystemTime::timestampToDate(),
                'locked' => $this->lockedRoom));
    }

    /**
	 * Say that a room has been guest un-/statused :)
     *
     * @param integer $roomId Id of the room
     * @param string $shortName Short version of the user's name
     * @param integer $guest 1 if a room is guests are allowed to enter
     * @param integer $time UNIX timestamp
     * @return void
	 */
    public function guest($roomId, $shortName, $guest, $time) {
        $guest = ($guest != '0') ? 'on' : 'off';
        $this->db->insert('messages', array('room' => $roomId, 'user' => $shortName, 'type' => 'guest',
                'text' => "turned ${guest} guest access", 'date' => SystemTime::timestampToDate($time),
                'locked' => $this->lockedRoom));
    }

    /**
	 * Say that a topic has been set/cleared
     *
     * @param integer $roomId Id of the room
     * @param string $shortName Short version of the user's name
     * @param string $topic Topic that has been set
     * @return void
	 */
    public function topic($roomId, $shortName, $topic) {
        $text = (empty($topic)) ? 'cleared the topic ' : "changed the room&#39;s topic to <em>$topic</em>";
        $this->db->insert('messages', array('room' => $roomId, 'user' => $shortName, 'type' => 'topic',
                'text' => $text, 'date' => SystemTime::timestampToDate(), 'locked' => $this->lockedRoom));
    }

    /**
	 * Say that a transcript has been cleared, if the transcript is from today
     *
     * @param integer $roomId Id of the room
     * @param string $shortName Short version of the user's name
     * @param string $date Date to save under
     * @return void
	 */
    public function transcript($roomId, $shortName, $date) {
        $this->db->insert('messages', array('room' => $roomId, 'user' => $shortName, 'type' => 'transcript',
                'text' => 'cleared the transcript', 'date' => $date, 'locked' => $this->lockedRoom));
    }
    
}