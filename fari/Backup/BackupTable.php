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

    /**
     * Returns an array of all items in a table.
     * @param string $tableName name of the table
     * @return array
     */
    final function items($tableName) {
        $table = new Table($tableName);
        return $table->findAll();
    }
    
}