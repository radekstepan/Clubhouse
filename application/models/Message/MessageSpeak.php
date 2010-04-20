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

    private $time;

    private $date;

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
            // update room activity
            $this->db->update('rooms', array('timestamp' => $time), array('id' => $roomId));

            // do we have a message in the last 5 minutes?
            $this->time = $this->roundTimestamp($time);

            $result = $this->db->selectRow('rooms', 'id, locked', "id=$roomId AND activity >= {$this->time}");
            $this->lockedRoom = ($result['locked'] == 1) ? '1' : '0';

            $this->date = $this->timestampToDate($this->time);
            $niceTime = $this->timestampToTime($this->time);

            // hide this message?
            if ($hide > 0) {
                $locked = '1';
            } else {
                $locked = $this->lockedRoom;
            }

            if ($result == NULL) {
                // update last room activity, we need for correct timestamping in a locked room
                $this->db->update('rooms', array('activity' => $this->time), array('id' => $roomId));

                $this->lastId = $this->db->insert('messages', array('room' => $roomId, 'type' => 'timestamp',
                        'text' => $niceTime, 'date' => $this->date, 'user' => '', 'locked' => $locked));
            } else {
                // there might have been a message in the last 5, but in a locked room
                if ($this->lockedRoom == '0') { // ... now that it's unlocked
                    $result = $this->db->select('messages', 'id, locked',
                        "room=$roomId AND date='{$this->date}' AND text='$niceTime' AND type='timestamp' AND locked='0'");
                    // there is such a message, created in a locked room
                    if (empty($result)) {
                        // create one in the unlocked room :)
                        $this->db->insert('messages', array('room' => $roomId, 'type' => 'timestamp',
                            'text' => $niceTime, 'date' => $this->date, 'user' => '', 'locked' => $locked));
                    }
                }
            }
        }
	}

    /**
     * Generate a nice time from a timestamp, eg: '11:05 AM'
     * @param <type> $time
     * @return <type>
     */
    private function timestampToTime($time) {
        return date("g", $time) . ':' . date("i", $time) . ' ' . date("A", $time);
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
        // ... in an unlocked room
        $result = $this->db->selectRow('messages', 'user', array('room' => $roomId, 'date' => $this->date,
                'locked' => '0', 'type' => 'text'));
        if (empty($result)) {
            // new message of the day, insert transcript entry
            $this->db->insert('room_transcripts', array('room' => $roomId, 'date' => $this->date, 'deleted' => 0,
                    'niceDate' => date("l, F j", $this->time)));
        }

        // code in the message
        if (strpos($text, '{') && strpos($text, '}')) $text = "<pre><code>$text</code></pre>";

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

        $date = $this->timestampToDate($time);

        $this->db->insert('messages', array('text' => $text, 'user' => $shortName, 'room' => $roomId, 'type' => 'text',
            'userId' => $userId, 'date' => $date, 'locked' => $this->lockedRoom, 'highlight' => 0));

        // is this the first time we are saying something today?
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
                'text' => 'has entered the room', 'date' => $this->timestampToDate($time),
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
                'text' => 'has left the room', 'date' => $this->timestampToDate($time),
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
                'text' => "has ${lock}locked the room", 'date' => $this->timestampToDate(mktime()),
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
                'text' => "turned ${guest} guest access", 'date' => $this->timestampToDate($time),
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
                'text' => $text, 'date' => $this->timestampToDate(mktime()), 'locked' => $this->lockedRoom));
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



    /********************* helpers *********************/



	/**
	 * Round the time to the nearest lowest 12th of an hour
     *
     * @param integer $time UNIX timestamp
     * @return integer Rounded timestamp
	 */
    private function roundTimestamp($time) {
        $minutes = date("i", $time);
        $minutes = (substr($minutes, -1) > 4) ? substr($minutes, 0, 1) . '5' : substr($minutes, 0, 1) . '0';
        // strip the leading zero
        $minutes = ($minutes < 10) ? substr($minutes, 1) : $minutes;

        // hour minute second month day year
        return mktime(date("G", $time), $minutes, 0, date("n", $time), date("j", $time), date("Y", $time));
    }

    /**
     * Return a date formatted entry for a db from a timestamp
     *
     * @param string $time DB entry date
     */
    private function timestampToDate($time) {
        return date("Y-m-d", $time);
    }
    
}