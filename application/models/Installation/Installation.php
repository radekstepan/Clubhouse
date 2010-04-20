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
 * Install the SQLite database.
 *
 * @copyright Copyright (c) 2010 Radek Stepan
 * @package   Clubhouse\Models
 */
class Installation extends Fari_DbSqLite {

    function __construct() {
        // create SQLite database
        parent::__construct();

        // files
        $this->createTable('files', array(
                'id' => 'INTEGER PRIMARY KEY',
                'mime' => 'TEXT',
                'data' => 'DATA',
                'code' => 'TEXT',
                'date' => 'TEXT',
                'room' => 'NUMERIC',
                'transcript' => 'NUMERIC',
                'filename' => 'TEXT',
                'type' => 'TEXT'
            ));

        // image thumbnails
        $this->createTable('thumbs', array(
                'data' => 'DATA',
                'code' => 'TEXT'
            ));

        // messages
        $this->createTable('messages', array(
                'id' => 'INTEGER PRIMARY KEY',
                'date' => 'TEXT',
                'room' => 'NUMERIC',
                'userId' => 'NUMERIC',
                'text' => 'TEXT',
                'type' => 'TEXT',
                'user' => 'NUMERIC',
                'transcript' => 'NUMERIC',
                'highlight' => 'NUMERIC',
                'locked' => 'NUMERIC'
            ));

        // room transcripts
        $this->createTable('room_transcripts', array(
                'key' => 'INTEGER PRIMARY KEY',
                'deleted' => 'NUMERIC',
                'niceDate' => 'TEXT',
                'date' => 'TEXT',
                'room' => 'NUMERIC'
            ));

        // room users
        $this->createTable('room_users', array(
                'timestamp' => 'NUMERIC',
                'room' => 'NUMERIC',
                'user' => 'NUMERIC'
            ));

        // rooms
        $this->createTable('rooms', array(
                'id' => 'INTEGER PRIMARY KEY',
                'deleted' => 'TEXT',
                'activity' => 'NUMERIC',
                'timestamp' => 'NUMERIC',
                'description' => 'TEXT',
                'guest' => 'TEXT',
                'locked' => 'TEXT',
                'name' => 'TEXT'
            ));

        // transcript users
        $this->createTable('transcript_users', array(
                'date' => 'TEXT',
                'room' => 'NUMERIC',
                'user' => 'NUMERIC'
            ));

        // user permissions
        $this->createTable('user_permissions', array(
                'room' => 'NUMERIC',
                'user' => 'NUMERIC'
            ));

        // users
        $this->createTable('users', array(
                'id' => 'INTEGER PRIMARY KEY',
                'role' => 'TEXT',
                'long' => 'TEXT',
                'short' => 'TEXT',
                'email' => 'TEXT',
                'invitation' => 'TEXT',
                'name' => 'TEXT',
                'password' => 'TEXT',
                'surname' => 'TEXT',
                'username' => 'TEXT'
            ));

        $db = Fari_Db::getConnection();
        $db->insert('users', array(
                'role' => 'admin',
                'name' => 'Radek',
                'surname' => 'Stepan',
                'long' => 'Radek Stepan',
                'short' => 'Radek S.',
                'password' => 'd033e22ae348aeb5660fc2140aec35850c4da997',
                'username' => 'admin'));
    }

}