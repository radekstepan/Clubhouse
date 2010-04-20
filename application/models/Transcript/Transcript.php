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
 * Transcript reading.
 *
 * @copyright Copyright (c) 2010 Radek Stepan
 * @package   Clubhouse\Models\Transcript
 */
class Transcript extends Fari_Bag {

    function __construct($date, $roomId) {
        // setup db connection
        $this->db = Fari_Db::getConnection();

        $this->details = $this->db->selectRow('room_transcripts JOIN rooms ON rooms.id=room_transcripts.room',
            'room_transcripts.key, niceDate, name, room, date',
            array('date' => $date, 'room_transcripts.deleted' => 0, 'room' => $roomId));

        // nothing found, throw an exception
        if (!is_array($this->details)) throw new TranscriptNotFoundException();
        
        // get users
        $this->users = $this->getUsers($date, $roomId);

        // get messages
        $this->messages = $this->getMessages($date, $roomId);

        // get files
        $this->files = $this->getFiles($date, $roomId);

        // next transcript
        $this->next = $this->next($roomId);

        // previous transcript
        $this->previous = $this->previous($roomId);
    }



    /********************* delete it *********************/



    /**
     * Delete the transcript.
     */
    public function delete($date, $shortName) {
        // transcript users are not needed anymore
        $this->db->delete('transcript_users', array('transcript' => $this->details['key']));

        // files
        $files = $this->db->selectRow('files',
            'group_concat(code) as codes',
            array('transcript' => $this->details['key']));

        $this->db->delete('files', array('transcript' => $this->details['key']));
        // thumbnails
        $this->db->delete('thumbs', "code IN ('{$files['codes']}')");

        // finaly ourselves
        $this->db->delete('room_transcripts', array('key' => $this->details['key']));

        // reset the last activity for the room so a new transcript can be created
        $this->db->update('rooms', array('activity' => 0), array('id' => $this->details['room']));

        // delete all messages for the day
        $this->db->delete('messages', array('room' => $this->details['room'], 'date' => $this->details['date']));

        // has this happened today?
        if ($date == date("Y-m-d", mktime())) {
            $message = new MessageSpeak($this->details['room'], mktime());
            $message->transcript($this->details['room'], $shortName, $date);
        }
    }



    /********************* get transcript specs *********************/



    private function getUsers($date, $roomId) {
        $result = $this->db->select('transcript_users JOIN users ON users.id=transcript_users.user',
            'long', array('date' => $date, 'room' => $roomId), 'long ASC');
        
        $names = array();
        foreach ($result as &$name) {
            $names[] = $name['long'];
        }
        return $names;
    }

    private function getMessages($date, $roomId) {
        return array_reverse($this->db->select(
                'messages',
                '*',
                "room='$roomId' AND date='$date' AND locked='0' AND type NOT IN('lock')",
                'id DESC'
            ));
    }

    private function getFiles($date, $roomId) {
        return $this->db->select('files',
            'filename, code, type', array('date' => $date, 'room' => $roomId), 'filename ASC');
    }



    /********************* previous and next transcripts *********************/



    function previous($roomId) {
        return $this->db->selectRow('room_transcripts', 'date, niceDate',
            "room='$roomId' AND key<'{$this->details['key']}' AND deleted='0'", 'key DESC');
    }

    function next($roomId) {
        return $this->db->selectRow('room_transcripts', 'date, niceDate',
            "room='$roomId' AND key>'{$this->details['key']}' AND deleted='0'", 'key ASC');
    }

}