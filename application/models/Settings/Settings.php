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
 * List and delete rooms.
 *
 * @copyright Copyright (c) 2010 Radek Stepan
 * @package   Clubhouse\Models
 */
class Settings extends Fari_ApplicationModel {

    function getRooms() {
        return $this->db->select('rooms', 'id, name, description', 'deleted NOT IN(1)', 'id ASC');
    }

    function deleteRoom($roomId) {
        // throw everyone out (no need for messages as we are deleting the room)
        $this->db->delete('room_users', array('room' => $roomId));

        // remove all permissions
        $this->db->delete('user_permissions', array('room' => $roomId));

        // set room as inactive, locked and barring guest access
        $result = $this->db->update('rooms',
            array('deleted' => 1, 'guest' => 0, 'locked' => 1),
            array('id' => $roomId)
        );
        if ($result != 1) throw new NotFoundException();

        // inactivate transcript entries... the related messages are still accessible
        $this->db->update('room_transcripts', array('deleted' => 1), array('room' => $roomId));
    }

}