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
 * Implementation of table backup.
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\Backup
 */
class Fari_BackupTable extends Fari_BackupTemplate {

    /** @var string password */
    private $db;

    /**
     * Setup database connection.
     * @param optional db connection, otherwise defaults to Fari_Db
     */
    public function __construct($db=NULL) {
        $this->db = (!isset($db)) ? Fari_Db::getConnection() : $db;
    }

    /**
     * Returns an array of all items in a table.
     * @param name of the table
     * @return array
     */
    public function items($tableName) {
        return $this->db->select($tableName, '*');
    }
    
}