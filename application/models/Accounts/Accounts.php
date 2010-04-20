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
 * Invitations, permissions deleting users.
 *
 * @copyright Copyright (c) 2010 Radek Stepan
 * @package   Clubhouse\Models
 */
class Accounts extends Fari_ApplicationModel {

    function getInvitedUser($invitationCode) {
        return $this->db->selectRow('users', 'name, invitation, email', array('invitation' => $invitationCode));
    }
    
    function getAdmin() {
        return $this->db->selectRow('users', 'id, long', array('role' => 'admin'));
    }

    function getUser($userId) {
        return $this->db->selectRow('users', 'id, long', "id=$userId AND role='invited'");
    }



    /********************* permissions *********************/



    function getUserPermissions($userId) {
        return $this->db->select('user_permissions', 'user, room', array('user' => $userId), 'room ASC');
    }

    function deleteUserPermissions($userId) {
        $this->db->delete('user_permissions', array('user' => $userId));
    }

    function insertUserRoomPermissions($userId, $roomId) {
        $this->db->insert('user_permissions', array('user' => $userId, 'room' => $roomId));
    }



    /********************* invitations *********************/



    function newInvitation($firstName, $lastName, $email=NULL) {
        $shortName = $longName = $firstName; // default
        if (!empty($lastName)) {
            $last = explode(' ', $lastName);
            $shortName = $firstName . ' ' . substr(end($last), 0, 1) . '.'; // short
            $longName = $firstName . ' ' . implode(' ', $last); // long
        }

        $this->db->insert('users', array(
                        'email' => $email,
                        'name' => $firstName,
                        'surname' => $lastName,
                        'short' => $shortName,
                        'long' => $longName,
                        'invitation' => Fari_Tools::randomCode(6),
                        'role' => 'invited'
                    ));
        if (!empty($lastName)) $firstName .= ' ' . $lastName;
        return $firstName;
    }

    function setInvitedUserCredentials($username, $password, $invitationCode) {
        $result = $this->db->update('users', array('invitation' => '', 'username' => $username,
                'password' => sha1($password)), array('invitation' => $invitationCode));
        if ($result != 1) throw new NotFoundException();
    }
    
    
    
    /********************* misc *********************/



    function deleteUser($userId) {
        // inactivate them
        $result = $this->db->update('users', array('role' => 'inactive'), array('id' => $userId));
        if ($result != 1) throw new NotFoundException();

        // disallow them
        $this->db->delete('user_permissions', array('user' => $userId));

        // leave them, nicely.. with a message :)
        $leaveThis = $this->db->selectRow('room_users JOIN users on room_users.user = users.id', 'room, short',
            array('user' => $userId));
        if (!empty($leaveThis)) {
            $time = mktime();

            $message = new MessageSpeak($leaveThis['room'], $time);
            $message->leave($leaveThis['room'], $time, $leaveThis['short']);
            // delete in one big swoop
            $this->db->delete('room_users', array('user' => $userId));
        }
    }

    function isUsernameUnique($username) {
        $result = $this->db->selectRow('users', 'id', array('username' => $username));
        return (empty($result)) ? TRUE : FALSE;
    }


}