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
 * An implementation of SQlite2 interface.
 *
 * @deprecated
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\Db
 */
final class Fari_DbSqLite2 {

    /**#@+ SQLite version to use */
	const SQLITE_VERSION = 2;
    /**#@-*/

    /** @var SQLite3 instance */
    private $sqlite;

    /**
     * Creates an SQLite database.
     * @param string $path to the database file
     */
    public function __construct($path) {
        try {
            $this->sqlite = new SQLite3($path, 0666, $error);
        } catch (Exception $error) {
             try {
                throw new Fari_Exception('Cannot create an SQLite2 DB: ' . $error . '.');
             } catch (Fari_Exception $exception) { $exception->fire(); }
        }
    }

    /**
     * Run a query on the database;
     * @param string $query
     * @return boolean TRUE on success
     */
    public function query($query) {
        return $this->sqlite->execQuery($query);
    }

}
