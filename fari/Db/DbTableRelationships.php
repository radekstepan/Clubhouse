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

    /** @var array that might have relationship set */
    private $relationship;

    /**
     * Has one relationship through "foreign_id" key.
     * @param string $foreignTableName
     * @param Table $table
     */
    private function hasOne($table, $foreignTableName) {
        $foreignTableName = strtolower($foreignTableName);
        assert('!empty($table->primaryKey); // table needs to have primary key defined for table join');

        // can we load the foreign table?
        if (class_exists(ucfirst($foreignTableName))) {
            // capitalize
            $foreignTableName = ucfirst($foreignTableName);
            // note the foreign table might have relationships of its own...
            $foreignTable = new $foreignTableName();
            assert('!empty($foreignTable->primaryKey); // foreign table needs to have primary key defined');

            // create the join on custom primary key and (optionally) custom table name
            $table->join($foreignTable->tableName, array(
                    "{$table->tableName}.{$foreignTable->tableName}_{$table->primaryKey}"
                        => "{$foreignTable->tableName}.{$foreignTable->primaryKey}"
            ));
            // as an added bonus, we might have a relationship in the foreign table, add it...
            $table->join .= $foreignTable->join;
        } else {
            // we will have to rely on our primary key
            // create the join where "foreignTable_id = id"
            $table->join($foreignTableName, array(
                    "{$table->tableName}.{$foreignTableName}_{$table->primaryKey}"
                        => "{$foreignTableName}.{$table->primaryKey}"
            ));
        }
    }

    /**
     * One-to-many relationship, e.g. a blog post has many comments.
     * @param string $foreignTableName
     * @param Table $table
     */
    private function hasMany($table, $foreignTableName) {
        $foreignTableName = strtolower($foreignTableName);
        assert('!empty($table->primaryKey); // table needs to have primary key defined for table join');

        // can we load the foreign table?
        if (class_exists(ucfirst($foreignTableName))) {
            // capitalize
            $foreignTableName = ucfirst($foreignTableName);
            // note the foreign table might have relationships of its own...
            $foreignTable = new $foreignTableName();
            assert('!empty($foreignTable->primaryKey); // foreign table needs to have primary key defined');

            // create the join where "ourTable.ourId=foreignTable.ourTable_ourId"
            $table->join($foreignTable->tableName, array(
                    "{$table->tableName}.{$table->primaryKey}"
                        => "{$foreignTable->tableName}.{$table->tableName}_{$table->primaryKey}"
            ));
            // as an added bonus, we might have a relationship in the foreign table, add it...
            $table->join .= $foreignTable->join;
        } else {
            // we will have to rely on our primary key
            // create the join where "ourTable.ourId=foreignTable.ourTable_ourId"
            $table->join($foreignTableName->tableName, array(
                    "{$table->tableName}.{$table->primaryKey}"
                        => "{$foreignTableName->tableName}.{$table->tableName}_{$table->primaryKey}"
            ));
        }
    }

    /**
     * Check that I can haz a query to other table I loolz to.
     * @param Table $table
     * @param string $foreignTableName
     * @throws Fari_Exception ... throws up big lulz
     */
    public function iCanHazQuery(Table $table, $foreignTableName) {
        try {
            // one-to-one
            if (isset($table->hasOne)) {
                if (is_array($table->hasOne)) {
                    // $hasOne array
                    if (in_array($foreignTableName, $table->hasOne)) {
                        $this->relationship = array('hasOne' => $foreignTableName);
                    } else {
                        // fail
                        $stringHasArray = implode('|', $table->hasOne);
                        throw new Fari_Exception("Table relationship with '{$foreignTableName}' is not defined
                            did you mean [{$stringHasArray}]?");
                    }
                } else {
                    // $hasOne string
                    if ($foreignTableName == $table->hasOne) {
                        $this->relationship = array('hasOne' => $foreignTableName);
                    } else {
                        // fail
                        throw new Fari_Exception("Table relationship with '{$foreignTableName}' is not defined
                            did you mean [{$table->hasOne}]?");
                    }
                }
            }
            // one-to-many
            else if (isset($table->hasMany)) {
                if (is_array($table->hasMany)) {
                    // $hasMany array
                    if (in_array($foreignTableName, $table->hasMany)) {
                        $this->relationship = array('hasMany' => $foreignTableName);
                    } else {
                        // fail
                        $stringHasArray = implode('|', $table->hasMany);
                        throw new Fari_Exception("Table relationship with '{$foreignTableName}' is not defined
                            did you mean [{$stringHasArray}]?");
                    }
                } else {
                    // $hasMany string
                    if ($foreignTableName == $table->hasMany) {
                        $this->relationship = array('hasMany' => $foreignTableName);
                    } else {
                        // fail
                        throw new Fari_Exception("Table relationship with '{$foreignTableName}' is not defined
                            did you mean [{$table->hasMany}]?");
                    }
                }
            }
            // fail...
            else {
                throw new Fari_Exception("Table relationship with '{$foreignTableName}' is not defined");
            }
        } catch (Fari_Exception $exception) { $exception->fire(); }
    }

    /**
     * Will query "has*" relationships of this table. We call this fce after WHERE clause has been built.
     * @param Table $table
     */
    public function checkHazQuery(Table $table) {
        // you haz WHERE clause?
        assert('is_array($table->where); // you need to define the WHERE clause first');

        // do we have a relationship set?
        if (is_array($this->relationship)) {
            foreach ($this->relationship as $rs => $foreignTableName) {
                // I haz
                $this->$rs($table, $foreignTableName);
            }

            // fixup where clause by prepending our table name if not present
            assert('is_array($table->where); // where clause needs to be set');
            if (array_key_exists($table->primaryKey, $table->where)) {
                // save under new key with our table name prepended
                $table->where["{$table->tableName}.{$table->primaryKey}"] = $table->where[$table->primaryKey];
                // unset
                unset($table->where[$table->primaryKey]);
            }
        }
    }

}