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
 * Fetch or search messages.
 *
 * @copyright Copyright (c) 2010 Radek Stepan
 * @package   Clubhouse\Models
 */
class Messages extends Fari_ApplicationModel {

    function search($query) {
        return $this->db->select('messages JOIN rooms ON rooms.id=messages.room JOIN users ON users.id=messages.userId',
            'rooms.name, rooms.id, users.long, date, text',
            "messages.type='text' AND messages.text LIKE '%$query%' AND messages.locked=0");
    }



    /********************* getters *********************/



    function getLatest($lastMessage, $roomId) {
        return $this->db->select('messages', '*', "id > $lastMessage AND room=$roomId");
    }

    function get($roomId) {
        return array_reverse($this->db->select('messages', '*', array('room' => $roomId), 'id DESC', 100));
    }

    function haveMore($roomId) {
        return ($this->db->count('messages', array('room' => $roomId)) > 100) ? true : false;
    }



    /********************* highlighting *********************/



    function switchHighlight($messageId) {
        $result = $this->db->selectRow('messages', 'highlight, room, date', array('id' => $messageId, 'type' => 'text'));
        if (!empty($result)) {
            if ($result['highlight'] == 1) {
                $highlight = 0;
                $this->db->update('messages', array('highlight' => 0, 'transcript' => ''), array('id' => $messageId));
            } else {
                // save the key of the transcript so we can retrieve faster on transcripts listing
                $highlight = 1;
                $transcript = $this->db->selectRow('room_transcripts', 'key',
                    "room='{$result['room']}' AND date='{$result['date']}'");
                $this->db->update('messages', array('highlight' => 1,
                        'transcript' => $transcript['key']), array('id' => $messageId));
            }
            return $highlight;
        }
        
        throw new NotFoundException();
    }

}