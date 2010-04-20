<?php if (!defined('FARI')) die();

/**
 * Fari Framework
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link      http://radekstepan.com
 * @category  Fari Framework
 */



/**
 * An implementation of SQlite3 interface.
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\Db
 */
final class Fari_DbSqLite3 {

    /**#@+ SQLite version to use */
	const SQLITE_VERSION = 3;
    /**#@-*/

    /** @var for some reason saving SQLite3 instance doesn't work, so save path */
    private $path;

    /**
     * Creates an SQLite database.
     * @param string $path to the database file
     */
    public function __construct($path) {
        try {
            // SQLite 3 class is available >= PHP 5.3.0
            $sqlite = new SQLite3($path, SQLITE3_OPEN_CREATE);
            if (!$sqlite instanceof SQLite3) {
                throw new Fari_Exception('Cannot create an SQLite3 DB.');
            }
        } catch (Fari_Exception $exception) { $exception->fire(); }

        $this->path = $path;
        unset($sqlite);
    }

    /**
     * Run a query on the database;
     * @param string $query
     * @return boolean TRUE on success
     */
    public function query($query) {        
        $sqlite = new SQLite3($this->path);
        // escape query
        $query = $sqlite->escapeString($query);
        // run it and return result
        return $sqlite->query($query);
    }

}