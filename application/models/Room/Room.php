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
 * Create, poll and edit rooms.
 *
 * @copyright Copyright (c) 2010 Radek Stepan
 * @package   Clubhouse\Models\Room
 */
class Room extends Fari_ApplicationModel {

    function getDescription($roomId) {
        return $this->db->selectRow('rooms', 'id, name, description, guest, locked', array('id' => $roomId));
    }

    function poll($roomId) {
        // get participants
        $participants = $this->db->select('room_users JOIN users ON room_users.user=users.id', 'long',
            array('room' => $roomId), 'long ASC');

        // room description & settings
        $settings = $this->getDescription($roomId);

        // room files
        $files = $this->db->select('files', 'code, filename, type', array('room' => $roomId), 'id DESC', 5);

        // return the whole shebang
        return array('participants' => $participants, 'settings' => $settings, 'files' => $files);
    }

    function getGuestRoom($guestCode) {
        $result = $this->db->selectRow('rooms', 'id, name, description, locked', array('guest' => $guestCode));
        if (empty($result)) {
            throw new RoomNotFoundException();
        } else {
            return $result;
        }
    }

    function removeParticipant($userId, $roomId=NULL) {
        if (!isset($roomId)) {
            $this->db->delete('room_users', array('user' => $userId));
        }
    }

	/**
	 * Update activity for the given user
     *
     * @param integer $roomId Room
     * @param integer $time Timestamp
     * @param integer $userId User
	 */
    function updateUserActivity($roomId, $time, $userId) {
        // update the activity for the user
        $id = $this->db->update('room_users', array('timestamp' => $time), array('room' => $roomId, 'user' => $userId));
        if ($id != 1) throw new UserNotFoundException();
    }



    /********************* new room *********************/



    function create($name, $description='') {
        $roomId = $this->db->insert('rooms', array('name' => $name, 'description' => $description, 'activity' => 0,
                'locked' => 0, 'guest' => 0, 'deleted' => 0));

        // admin always has access
        $accounts = new Accounts();
        $admin = $accounts->getAdmin();
        $this->db->insert('user_permissions', array('room' => $roomId, 'user' => $admin['id']));
    }

    /**
     * Will return a new name for a room in a form "Room n"
     * 
     * @return string Room name
     */
    function newName() {
        // find all "Room"s
        $rooms = $this->db->select('rooms', 'name', "name LIKE '%Room %' AND deleted IN (0)", 'name ASC');
        // return early
        if (empty($rooms)) return 'Room 1';

        // only leave numbers
        foreach ($rooms as &$room) {
            preg_match('{(\d+)}', $room['name'], $match);
            $room['name'] = $match[1];
        }
        // custom sorting function...
        usort($rooms, create_function('$a,$b', "return strnatcmp(\$a['name'], \$b['name']);"));
        $last = end($rooms);

        // increase the value of highest element
        return 'Room ' . ((int)$last['name'] + 1);
    }



    /********************* lock, change topic and set guest access *********************/



    function lock($roomId) {
        // get the locked status for the room
        $status = $this->db->selectRow('rooms', 'locked', array('id' => $roomId));

        $status = ($status['locked'] > 0) ? 0 : 1;

        // update with new status
        $this->db->update('rooms', array('locked' => $status), array('id' => $roomId));

        return $status;
    }

    function topic($roomId, $topic) {
        $this->db->update('rooms', array('description' => $topic), array('id' => $roomId));
    }

    function guest($roomId, $userName) {
        $result = $this->db->selectRow('rooms', 'guest', array('id' => $roomId));

        $time = mktime();
        $message = new MessageSpeak($roomId, $time);

        if ($result['guest'] != '0') {
            $this->kickGuests($roomId, $time);
            $status = '0';
        } else {
            $status = substr(md5(uniqid(rand(), TRUE)), 0, 6);
        }

        // update the guests status
        $this->db->update('rooms', array('guest' => $status), array('id' => $roomId));

        // our guest on message
        $message->guest($roomId, $userName, $status, $time);

        return $status;
    }

    function kickGuests($roomId, $time) {
        $leaving = $this->db->select('user_permissions JOIN users ON user_permissions.user = users.id',
                            'id, short', array('role' => 'guest', 'room' => $roomId));

        if (!empty($leaving)) {
            $message = new MessageSpeak();

            foreach ($leaving as $kick) {
                // you should be leaving...
                $this->db->delete('user_permissions', array('user' => $kick['id'], 'room' => $roomId));

                // you are leaving...
                $message->leave($roomId, $time, $kick['short']);

                // you are actually leaving...
                $this->db->delete('room_users', array('user' => $kick['id'], 'room' => $roomId));
            }
        }        
    }

}