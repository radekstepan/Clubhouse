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
 * Gets system stats, performs cleanups.
 *
 * @copyright Copyright (c) 2010 Radek Stepan
 * @package   Clubhouse\Models\System
 */
class System extends Fari_ApplicationModel {

	function __construct($time=NULL) {
        if (!isset($time)) $time = mktime();

        $this->db = Fari_Db::getConnection();
        parent::__construct($this->db);

        $cutoff = $time - (60*10);

        // check if user has a timestamp older than 5 minutes in a room
        $leave = $this->db->select('room_users JOIN users ON room_users.user=users.id', 'user, room, short', "timestamp < $cutoff");
        if (!empty($leave)) {
            $message = new MessageSpeak();
            foreach ($leave as $user) {
                // leaving message
                $message->leave($user['room'], $time, $user['short']);
            }
            // clear them out from the room
            $this->db->delete('room_users', "timestamp < $cutoff");
        }
    }

    function lobbyRooms($userId, $isAdmin) {
        // display locked rooms only to the admin
        $locked = ($isAdmin) ? '0, 1' : '0';

        $rooms = $this->db->select('rooms JOIN user_permissions ON rooms.id=user_permissions.room',
                'id, name, description, user, timestamp, locked', "user=$userId AND locked IN($locked)", 'id ASC');

        $activity = $this->db->select('room_users JOIN users ON room_users.user=users.id', 'room, long', null,
            'room ASC, long ASC');
        
        $roomUsers = array();
        foreach ($activity as $value) {
            $roomUsers[$value['room']][] = $value['long'];
        }

        foreach ($rooms as &$room) {
            $room['users'] = $roomUsers[$room['id']];
        }
        return $rooms;
    }

    function isRoom($roomId) {
        $result = $this->db->selectRow('rooms', 'id', array('id' => $roomId, 'deleted' => 0));
        if (empty($result)) {
            throw new RoomNotFoundExcception();
        } else {
            return $result;
        }
    }

    function userCount() {
        $result = $this->db->select('room_users', 'user', null, 'user ASC');

        $users = 0; $lastUserId = 0;
        foreach ($result as $user) {
            if ($user['user'] != $lastUserId) {
                $users++; $lastUserId = $user['user'];
            }
        }

        return $users;
    }

    function getFile($fileCode) {
        return $this->db->selectRow('files', 'data, filename', array('code' => $fileCode));
    }

    function getThumbnail($fileCode) {
        return $this->db->selectRow('thumbs', 'data', array('code' => $fileCode));
    }

    /**
	 * Will return a formatted array for the Users listing
     *
     * @return array Name and Room names permissions list
	 */
    function userPermissions() {
        // do not show inactive zombie users
        $users = $this->db->select('users', 'id, long, role', "role NOT IN ('inactive')", 'name ASC, surname ASC');
        $permissions = $this->db->select('user_permissions JOIN rooms ON user_permissions.room=rooms.id', 'room, user, rooms.name');
        foreach ($users as &$user) {
            switch($user['role']) {
                case 'admin':
                    $user['perm'] = 'Owner';
                    break;
                case 'guest':
                    $user['perm'] = 'Guest';
                    break;
                default:
                    foreach ($permissions as $room) {
                        if ($room['user'] == $user['id']) {
                            $user['perm'] .= '<strong>' . $room['name'] . '</strong>, ';
                        }
                    }
                    $user['perm'] = (empty($user['perm'])) ? '' : substr($user['perm'], 0, -2);

            }
        }

        return $users;
    }

    function reset() {
        $this->db->delete('files');
        $this->db->delete('thumbs');
        $this->db->delete('messages');
        $this->db->delete('room_transcripts');
        $this->db->delete('room_users');
        $this->db->delete('rooms');
        $this->db->delete('transcript_users');
        $this->db->delete('user_permissions');
        $this->db->delete('users', "role NOT IN('admin')");
    }

}