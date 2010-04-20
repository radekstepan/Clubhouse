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
 * Creates and setups an SQLite database.
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\Db
 */
class Fari_DbSqLite {

    /** @var SQLite3 or SQLite2 instance */
    private $instance;

    /**
     * Creates an SQLite database.
     * @param string $path to the database file or use default route from config.php
     */
    public function __construct($path=NULL) {
        // create path to the file
        if (!isset($path)) $path = BASEPATH . '/' . DB_NAME;

        // determine SQLite version we are going to use
        switch(DB_DRIVER) {
            // SQLite 3 class is available >= PHP 5.3.0
            case 'sqlite3':
                $this->instance = new Fari_DbSqLite3($path);
                break;
            case 'sqlite2':
                $this->instance = new Fari_DbSqLite2($path);
                break;
        }
    }

    /**
     * Checks if a database file is writable.
     * @param $path to the database file or use default route from config.php
     * @return boolean TRUE if database is writable
     */
    public static function isDbWritable($path=NULL) {
        if (!isset($path)) $path = BASEPATH . '/' . DB_NAME;
        return (is_writable($path));
    }

    /**
     * Create a table query
     * @param string $table
     * @param array $columns keys specify columns with values specifying type of the column
     */
    public function createTable($table, array $columns) {
        // query
        $query = 'CREATE TABLE ' . $table . ' (';
        // build columns query
        foreach ($columns as $name => $type) {
            $query .= $name . ' ' . $type . ', ';
        }
        // strip trailing ', '
        $query = substr($query, 0, -2) . ')';
        
        // execute query
        try {
            if (!$this->instance->query($query)) {
                throw new Fari_Exception('Cannot create table.');
            }
        } catch (Fari_Exception $exception) { $exception->fire(); }
    }

}