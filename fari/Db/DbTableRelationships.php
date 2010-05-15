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
 * Relationships between tables through keys.
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\Db
 */
class Fari_DbTableRelationships {

    /**
     * Relates table to other tables.
     * @param Table $table
     */
    public function relate(Table $table) {
        if (isset($table->hasOne)) {
            $this->hasOne($table);
        }
    }

    /**
     * Has one relationship through "foreign_id" key.
     * @param Table $table
     */
    private function hasOne($table) {
        assert('!empty($table->hasOne); // table name needs to not be empty');
        $foreignTable = strtolower($table->hasOne);
        assert('!empty($table->primaryKey); // table needs to have primary key defined for table join');
        // create the join where "foreignTable_id = id"
        //$join = "{$table->table}.{$foreignTable}_{$table->primaryKey}={$foreignTable}.{$table->primaryKey}";
        //$table->join .= " JOIN {$foreignTable} ON {$join}";
        $table->join($foreignTable, array(
                "{$table->table}.{$foreignTable}_{$table->primaryKey}"
                    => "{$foreignTable}.{$table->primaryKey}"
        ));
    }

}