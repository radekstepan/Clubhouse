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
        // one-to-one
        if (isset($table->hasOne)) {
            $this->hasOne($table);
        }
        // one-to-many
        if (isset($table->hasMany)) {
            $this->hasMany($table);
        }
    }

    /**
     * Has one relationship through "foreign_id" key.
     * @param Table $table
     */
    private function hasOne($table) {
        assert('!empty($table->hasOne); // table name needs to not be empty');
        $foreignTableName = strtolower($table->hasOne);
        assert('!empty($table->primaryKey); // table needs to have primary key defined for table join');

        // can we load the foreign table?
        if (class_exists(ucfirst($foreignTableName))) {
            // capitalize
            $foreignTableName = ucfirst($foreignTableName);
            // note the foreign table might have relationships of its own...
            $foreignTable = new $foreignTableName();
            assert('!empty($foreignTable->primaryKey); // foreign table needs to have primary key defined');

            // create the join on custom primary key and (optionally) custom table name
            $table->join($foreignTable->table, array(
                    "{$table->table}.{$foreignTable->table}_{$table->primaryKey}"
                        => "{$foreignTable->table}.{$foreignTable->primaryKey}"
            ));
            // as an added bonus, we might have a relationship in the foreign table, add it...
            $table->join .= $foreignTable->join;
        } else {
            // we will have to rely on our primary key
            // create the join where "foreignTable_id = id"
            $table->join($foreignTableName, array(
                    "{$table->table}.{$foreignTableName}_{$table->primaryKey}"
                        => "{$foreignTableName}.{$table->primaryKey}"
            ));
        }
    }

    /**
     * One-to-many relationship, e.g. a blog post has many comments.
     * @param Table $table
     */
    private function hasMany($table) {
        assert('!empty($table->hasMany); // table name of children can not be empty');
        $foreignTableName = strtolower($table->hasMany);
        assert('!empty($table->primaryKey); // table needs to have primary key defined for table join');

        // can we load the foreign table?
        if (class_exists(ucfirst($foreignTableName))) {
            // capitalize
            $foreignTableName = ucfirst($foreignTableName);
            // note the foreign table might have relationships of its own...
            $foreignTable = new $foreignTableName();
            assert('!empty($foreignTable->primaryKey); // foreign table needs to have primary key defined');

            // create the join where "ourTable.ourId=foreignTable.ourTable_ourId"
            $table->join($foreignTable->table, array(
                    "{$table->table}.{$table->primaryKey}"
                        => "{$foreignTable->table}.{$table->table}_{$table->primaryKey}"
            ));
            // as an added bonus, we might have a relationship in the foreign table, add it...
            $table->join .= $foreignTable->join;
        } else {
            // we will have to rely on our primary key
            // create the join where "ourTable.ourId=foreignTable.ourTable_ourId"
            $table->join($foreignTableName->table, array(
                    "{$table->table}.{$table->primaryKey}"
                        => "{$foreignTableName->table}.{$table->table}_{$table->primaryKey}"
            ));
        }
    }

}