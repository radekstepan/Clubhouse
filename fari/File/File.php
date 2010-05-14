<?php

/**
 * Fari Framework
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link      http://radekstepan.com
 * @category  Fari Framework
 */



/**
 * File related functions template.
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\File
 */
class Fari_File {

    /**
     * Returns listing of files in a directory
     * @param string $directory, basepath will be prepended
     */
    public function listDirectory($directory) {
        assert('defined(\'BASEPATH\'); // base path not defined');

        // prepend slash?
        $directory = (substr($file, 0, 1) !== '/') ? "/{$directory}" : $directory;
        
        // traverse files in the directory
        $listing = array();
        // use SPL dammit
        foreach (new DirectoryIterator(BASEPATH . $directory) as $item) {
            $this->pushItem($listing, $item);
        }
        
        return $listing;
    }

    /**
     * Overwrite in children to parse files according to our style.
     * @param array $listing
     * @param DirectoryIterator $item
     */
    public function pushItem(&$listing, DirectoryIterator $entry) {
        array_push($listing, $entry);
    }

}