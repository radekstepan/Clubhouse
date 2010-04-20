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
 * File upload.
 *
 * @copyright Copyright (c) 2010 Radek Stepan
 * @package   Clubhouse\Models
 */
class Upload extends Fari_Bag {

    private $fileTypes = array('doc', 'gif', 'htm', 'html', 'jpeg', 'jpg', 'pdf', 'png', 'psd', 'txt', 'xls', 'zip');

    public function __construct($file, $roomId) {
        // get file
        $this->name = Fari_Escape::file($file['name'], TRUE);
        $this->mime = $file['type'];
        
        // db instance
        $db = Fari_Db::getConnection();

        $type = explode('/', $this->mime);
        $type = (count($type) > 1) ? $type[1] : $type[0];
        // set generic filetype for files we don't have icons for :)
        if (!in_array($type, $this->fileTypes)) $type = 'generic';

        $stream = fopen($file['tmp_name'], 'rb');

        $code = $this->randomCode($db);
        $date = date("Y-m-d", mktime());

        // let's associate the file with a transcript (there better be a transcript...)
        $transcript = $db->selectRow('room_transcripts', 'key', array('date' => $date, 'room' => $roomId));

        // insert the file
        $db->query("INSERT INTO files (mime, data, code, room, filename, type, date, transcript)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            array($this->mime, $stream, $this->code = $code, $roomId, $this->name,
                $this->type = $type, $date, $transcript['key'])
        );

        fclose($stream);
        
        // create a thumbnail if required
        $thumbnail = new Thumbnail($file);
        if ($thumbnail->isCreated()) {
            // yes we do have one
            $this->thumbnail = TRUE;

            $thumb = fopen($thumbnail->getPath(), 'rb');

            // insert the thumbnail
            $db->query("INSERT INTO thumbs (data, code) VALUES (?, ?)", array($thumb, $this->code));

            fclose($thumb);
            //$thumbnail->destroy();
        }
    }

    private function randomCode($db) {
        $code = Fari_Tools::randomCode(6);
        $result = $db->selectRow('files', 'id', array('code' => $code));
        return (!empty($result)) ? $this->_randomCode() : $code;
    }
        
}
