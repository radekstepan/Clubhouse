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
 * Create image thumbnail.
 *
 * @copyright Copyright (c) 2010 Radek Stepan
 * @package   Clubhouse\Models
 */
class Thumbnail extends Fari_Bag {

    /**#@+ maximum width of the thumbnail */
	const THUMB_WIDTH = 300;
    /**#@-*/

    public function __construct($file) {
        // set the file
        $this->set($file);

        // a new filepath with coded filename for a slightly better security
        $this->path = BASEPATH . '/tmp/' . Fari_Tools::randomCode() . '.jpg';

        // 1. determine if image
        // 2. upload
        // 3. create the image from path
        switch ($this->type) {
            case 'image/jpeg':
            case 'image/jpg':
                $this->upload();
                if ($this->needsResize()) {
                    $image = imagecreatefromjpeg($this->path);
                }
                break;
            case 'image/png':
                $this->upload();
                if ($this->needsResize()) {
                    $image = imagecreatefrompng($this->path);
                }
                break;
            case 'image/gif':
                $this->upload();
                if ($this->needsResize()) {
                    $image = imagecreatefromgif($this->path);
                }
                break;
        }

        // do we have an image?
        if (isset($image)) {
            // set the new height
            $height = self::THUMB_WIDTH / ($this->width / $this->height);

            // create a new JPEG thumbnail
            $thumb = imagecreatetruecolor(self::THUMB_WIDTH, $height);
            imagecopyresampled($thumb, $image, 0, 0, 0, 0, self::THUMB_WIDTH, $height, $this->width, $this->height);

            // just save as JPEG
            //$thumb = imagecreatetruecolor($this->width, $this->height);
            //imagecopyresampled($thumb, $image, 0, 0, 0, 0, $this->width, $this->height, $this->width, $this->height);

            // save it to a new path
            imagejpeg($thumb, $this->path);

            $this->created = TRUE;

            imagedestroy($image);
            imagedestroy($thumb);
        }
    }

    public function isCreated() {
        return $this->created;
    }

    public function destroy() {
        unlink($this->path);
    }

    public function getPath() {
        return $this->path;
    }

    private function upload() {
        move_uploaded_file($this->tmp_name, $this->path);
    }

    private function needsResize() {
        list($this->width, $this->height) = getimagesize($this->path);
        return ($this->width > self::THUMB_WIDTH);
    }
    
}
