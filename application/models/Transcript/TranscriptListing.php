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
 * Transcript listing.
 *
 * @copyright Copyright (c) 2010 Radek Stepan
 * @package   Clubhouse\Models
 */
class TranscriptListing extends Fari_Bag {

    function __construct($roomPermissionsString) {
        // setup db connection
        $this->db = Fari_Db::getConnection();

        // fetch the count of all the transcripts, calculate in PHP
        $this->all = $this->db->select(
            'room_transcripts'.
            ' JOIN rooms'.
            ' ON room_transcripts.room=rooms.id',
            'room_transcripts.key, rooms.id, rooms.name, room_transcripts.niceDate, room_transcripts.date',
            "room_transcripts.deleted=0 AND rooms.id IN ($roomPermissionsString)",
            'room_transcripts.key DESC'
        );

        // count of all items
        if (($this->count = count($this->all)) == 0) {
            throw new EmptyException();
        }
    }

    function buildPage($page, $result) {
        // build KEYs and save them
        $keys = array();
        foreach ($result as $transcript) {
            $keys[] = $transcript['key'];
        }
        $this->keys = implode(',', $keys);

        // fetch users
        $this->users = $this->getPageUsers();
        // fetch diles
        $this->files = $this->getPageFiles();
        // fetch starred messages
        $this->starred = $this->getPageStarred();
    }

    private function getPageUsers() {
        $result = $this->db->select('transcript_users JOIN users ON transcript_users.user=users.id',
            'transcript, long', "transcript IN ({$this->keys})", 'transcript DESC, long ASC');
        $list = array();
        foreach ($result as $row) {
            $list[$row['transcript']][] = $row['long'];
        }
        return $list;
    }

    private function getPageStarred() {
        $result = $this->db->select('messages', 'transcript, text, user',
            "transcript IN ({$this->keys}) AND highlight=1", 'transcript DESC, id DESC');
        $list = array();
        foreach ($result as $row) {
            $list[$row['transcript']][] = array('user' => $row['user'], 'text' => $row['text']);
        }
        return $list;
    }

    private function getPageFiles() {
        $result = $this->db->select('files', 'transcript, filename, type, code',
            "transcript IN ({$this->keys})", 'transcript DESC, id DESC');
        $list = array();
        foreach ($result as $row) {
            $list[$row['transcript']][] = $row;
        }
        return $list;
    }

}