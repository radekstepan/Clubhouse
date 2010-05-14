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
 * Tiny YAML parser.
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\File
 */
class Fari_FileYaml {

    /** @var result public so we can access data repeatedly */
    public $result = array();

    /**
     * Parses yml file into an array or throws an exception.
     * @param string $file filename, basepath will be prepended
     * @return array or FileNotFoundException
     */
    public function toArray($file) {
        assert('!empty($file); // filename cannot be empty');
        // prepend slash?
        $file = (substr($file, 0, 1) !== '/') ? "/{$file}" : $file;
        
        // read in yml file
        assert('defined(\'BASEPATH\'); // base path not defined');
        if (file_exists($f = BASEPATH . $file)) {
            // read in file and explode on newlines
            $content = explode("\n", file_get_contents($f));
            $size = count($content);
            // traverse
            for ($i=0; $i < $size; $i++) {
                // a comment line? skip...
                if ($this->isComment($content[$i])) continue;

                // parent?
                if ($this->isParent($content[$i])) {
                    // whitespace go away
                    $parent = preg_replace('/ /', '', substr($content[$i], 0, -1));
                    // add new parent element
                    $this->result[$parent] = array();
                // ... or child?
                } else {
                    // format child element
                    $element = $this->formatNode($content[$i]);
                    // add to result set
                    $this->result[$parent][$element[0]] = $element[1];
                }
            }

            // return this baby
            return $this->result;
        } else {
            throw new FileNotFoundException();
        }
    }

    /**
     * Format node trimming excessive whitespace and quotation marks
     * @example hello: "Hello world" => array(hello => "Hello world")
     * @param string $node
     * @return array
     */
    private function formatNode($node) {
        assert('strpos($node, ":") !== FALSE; // node not in proper format');
        $divider = strpos($node, ':');
        return array(
            // give us whatever is before colon and trim whitespace
            trim(substr($node, 0, $divider)),
            // give us whatever is after semicolon, trip whitespace and double quotation marks
            trim(preg_replace("/\"/", '', substr($node, $divider + 1)))
        );
    }

    /**
     * Determine if node is a parent.
     * @param string $node
     * @return true if node is a parent
     */
    private function isParent($node) {
        assert('strpos($node, ":") !== FALSE; // node not in proper format');
        // parent is terminated by colon
        return (substr($node, -1, 1) == ':');
    }

    /**
     * Is this a comment line?.
     * @param string $node
     * @return true if node is a comment line
     */
    private function isComment($node) {
        return (strpos(trim($node), 0, 1) == '#');
    }

}