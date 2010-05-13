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
 * An Object Relational Mapper (ORM) working on a PDO data source.
 * @example $table->findFirst->where(array('id' => '> 1'));
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\Db
 */
class Table {

    /** @var PDO */
    private $db;

    /** @var string table name */
    public $table;

    /** @var array of data to save */
    public $data;

    /** @var limit the fields we return in find* statements */
    private $select;

    /** @var array of data in the where clause */
    private $where;

    /** @var string private name of method to call from a where clause */
    private $method;
    
    /** @var ordering and result set limit */
    private $order;
    private $limit;

    /** @var string join table string */
    private $join;

    /** @Fari_DbSubject observer subject for logging */
    private $logger;

    /**
	 * Setup a database connection (to a table)
	 * @param string an optional table name, optional only in PHP <= 5.3.0
	 */
    public function __construct($table=NULL) {
        // db connection
        $this->db = Fari_DbPdo::getConnection(); 

        // table name exists?
        if (isset($table)) {
            $this->table = $table;
        } else {
            // are we using high enough version of PHP for late static binding?
            try { if (version_compare(phpversion(), '5.3.0', '<=') == TRUE) {
                throw new Fari_Exception('Table name can automatically only be resolved in PHP 5.3.0.'); }
            } catch (Fari_Exception $exception) { $exception->fire(); }

            // ... yes, get the name of the class as the name of the table
            $this->table = get_called_class();
        }

        // attach an observer
        $this->logger = new Fari_DbSubject();
        $this->logger->attach(new Fari_ApplicationLogger());
    }

    /**
     * Rails-like calls, captures undefined methods
     * @link http://techportal.ibuildings.com/2010/01/11/learning-php-5-3-by-writing-your-own-orm/
     * @param string $method
     * @param mixed $params
     */
    //public static function __callStatic($method, $params) {
    //    try {
    //        // determine the method called
    //        if (!preg_match('/^(find|findFirst|count)By(\w+)$/', $method, $matches)) {
    //            throw new Fari_Exception("Call to undefined method {$method}");
    //        }
    //    } catch (Fari_Exception $exception) { $exception->fire(); }
    //
    //    $criteriaKeys = explode('_And_', preg_replace('/([a-z0-9])([A-Z])/', '$1_$2', $matches[2]));
    //    $criteriaKeys = array_map('strtolower', $criteriaKeys);
    //    $criteriaValues = array_slice($params, 0, count($criteriaKeys));
    //    $criteria = array_combine($criteriaKeys, $criteriaValues);
    //
    //    $method = $matches[1];
    //    return static::$method($criteria);
    //}

    /**
     * Magic setter.
     * @param mixed $column in a table
     * @param mixed $value to save
     */
    public function __set($column, $value) {
        $this->data[$column] = $value;
 	}

    /**
     * Save a whole array of items for a query.
     * @param array of key:value
     * @return Table, call with add()
     */
    public function set(array $values) {
        foreach ($values as $key => $value) $this->$key = $value;

        return $this;
    }

    /**
     * A where clause.
     * @param mixed $where array(column => value pair) or string ID
     * @return result from a db method called
     */
    public function where($where) {
        // are we passing a formed array?
        if (is_array($where)) {
            // set the values
            $this->where = $where;
        // we must mean an ID then...
        } else {
            try {
                // check that we have actually passed an int
                if (!Fari_Filter::isInt($where)) {
                    throw new Fari_Exception("'{$where}' is not a valid ID value.");
                // ...otherwise set the value under ID column
                } else $this->where = array('id' => $where);
            } catch (Fari_Exception $exception) { $exception->fire(); }
        }

        // have we defined the db method first?
        try { if (!isset($this->method)) {
            throw new Fari_Exception('First specify the method you would like to execute, then the where clause.'); }
        } catch (Fari_Exception $exception) { $exception->fire(); }
        
        // method call
        $result = $this->{$this->method}();
        // if we are finding first result...
        if (($this->limit == 1 && $this->method == '_find')) {
            // set result internally (so we can easily update rows etc...)
            $this->set($result);
            // return a bag of values
            $bag = new Fari_Bag();
            $bag->set($result);
            return $bag;
        }
        // return the result from a method call
        return $result;
    }

    /**
     * Specify the columns we want to retrieve.
     * @param mixed $columns values
     * @return Table, need to define a where clause
     */
    public function select($columns) {
        if (is_array($columns)) {
            $this->select = implode(', ', $columns);
        } else {
            $this->select = $columns;
        }

        return $this;
    }

    /**
     * Find item(s) in a table.
     * @param string $order
     * @param integer $limit
     * @return Table, need to define a where clause
     */
    public function find($order=NULL, $limit=NULL) {
        $this->order = $order;
        $this->limit = $limit;
        $this->method = '_find';

        return $this;
    }

    /**
     * Find first occurence of an item in a table.
     * @param string $order
     * @return Table, need to define a where clause
     */
    public function findFirst($order='id ASC') {
        $this->order = $order;
        $this->limit = 1;
        $this->method = '_find';

        return $this;
    }

    /**
     * Find last occurence of an item in a table.
     * @param string $order
     * @return Table, need to define a where clause
     */
    public function findLast($order='id DESC') {
        $this->order = $order;
        $this->limit = 1;
        $this->method = '_find';

        return $this;
    }

    /**
     * Return all items in a table.
     * @param string $order
     * @param integer $limit
     * @return array result set
     */
    public function findAll($order=NULL, $limit=NULL) {
        $this->order = $order;
        $this->limit = $limit;
        return $this->_find();
    }

    /**
     * Remove item(s) from a table.
     * @return Table, need to define a where clause
     */
    public function remove() {
        $this->method = '_remove';

        return $this;
    }

    /**
     * Remove all items from a table.
     * @return integer number of rows affected
     */
    public function removeAll() {
        return $this->_remove();
    }

    /**
     * Update rows in a table.
     * @return Table, need to define a where clause
     */
    public function update() {
        $this->method = '_update';
        
        return $this;
    }

    /**
     * Update all items in a table.
     * @return integer number of rows affected
     */
    public function updateAll() {
        return $this->_update();
    }

    /**
     * Count items in a table.
     * @return Table, need to define a where clause
     */
    public function count() {
        $this->method = '_count';
        
        return $this;
    }

    /**
     * Count all items in a table.
     * @return integer number of rows in a table
     */
    public function countAll() {
        return $this->_count();
    }

    /**
     * Insert data into a table.
     * @param array $values optionally pass them directly instead of using set() first
     * @return id of the inserted row
     */
    public function add(array $values=NULL) {
        // if we've passed an array, save it first
        if (isset($values)) $this->set($values);

        // SQL query, bind data
        $sql = "INSERT INTO {$this->table} ({$this->getColumns()}) VALUES ({$this->prepareData()})";

        // prepare SQL
        $statement = $this->db->prepare($sql);
        // bind data
        $statement = $this->bindData($statement);

        // execute query
        $statement->execute();

        // notify
        $this->logger->notify($this->toString($sql));

        // reset the saved data
        $this->clearData();

        // return id of the row
        return $this->db->lastInsertId();
    }

    /**
     * You can run a generic SQL SELECT query.
     * @param string $sql (unescaped, unfiltered, unchecked)
     * @return array result set
     */
    public function findBySql($sql) {
        // prepare SQL
        $statement = $this->db->prepare($sql);
        // bind where clause
        $statement = $this->bindWhere($statement);

        // notify
        $this->logger->notify($this->toString($sql));

        // reset data
        $this->clearData();

        // execute query and return an array result
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find items in a table and return them.
     * @return array result set
     */
    private function _find() {
        // SQL query
        $sql = "SELECT {$this->getSelectedColumns()} FROM {$this->getTableQuery()} {$this->getWhereQuery()}";
        if (isset($this->order)) $sql .= " ORDER BY {$this->order}";
        if (isset($this->limit)) $sql .= " LIMIT {$this->limit}";

        // prepare SQL
        $statement = $this->db->prepare($sql);
        // bind where clause
        $statement = $this->bindWhere($statement);

        // notify
        $this->logger->notify($this->toString($sql));

        // reset data
        $this->clearData();

        // execute query and return an array result
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        return ($this->limit == 1) ? (empty($result)) ? array() : end($result) : $result;
    }

    /**
     * Remove items from a table.
     * @return integer number of rows affected
     */
    private function _remove() {
        // SQL query
        $sql = "DELETE FROM {$this->getTableQuery()} {$this->getWhereQuery()}";

        // prepare SQL
        $statement = $this->db->prepare($sql);
        // bind where clause
        $statement = $this->bindWhere($statement);

        // notify
        $this->logger->notify($this->toString($sql));

        // reset data
        $this->clearData();

        // execute query & return row count
        $statement->execute();
        return $statement->rowCount();
    }

    /**
     * Update rows with data.
     * @return integer number of rows affetced
     */
    private function _update() {
        // SQL query
        $sql = "UPDATE {$this->getTableQuery()} SET {$this->prepareSet()} {$this->getWhereQuery()}";

        // prepare SQL
        $statement = $this->db->prepare($sql);
        // bind data
        $statement = $this->bindData($statement);
        // bind where clause
        $statement = $this->bindWhere($statement);

        // notify
        $this->logger->notify($this->toString($sql));

        // reset data
        $this->clearData();

        // execute query and return rows affected count
        $statement->execute();
        return $statement->rowCount();
    }

    /**
     * Count number of items in a table.
     * @return integer row count
     */
    private function _count() {
        // SQL query
        $sql = "SELECT COUNT (*) FROM {$this->getTableQuery()} {$this->getWhereQuery()}";

        // prepare SQL
        $statement = $this->db->prepare($sql);
        // bind where clause
        $statement = $this->bindWhere($statement);

        // notify
        $this->logger->notify($this->toString($sql));

        // reset data
        $this->clearData();

        // execute statement and return number of items
        $statement->execute();
        return $statement->fetchColumn();
    }

    /**
     * A simple table JOIN.
     * @param mixed $table string name we want to join to or a Table object
     * @param mixed $on array of left => right column or a string if column is shared in both tables
     * @return Table so we can call method (and add where clause)
     */
    public function join($table, $on) {
        // fetch table name from an object
        if ($table instanceof Table) $table = $table->table;

        // joining columns have different names
        if (is_array($on)) {
            foreach ($on as $left => $right) {
                $leftTable = $this->table;
                $rightTable = $table;

                // are we passing a table name?
                if (strpos($left, '.') !== FALSE) {
                    list($leftTable, $left) = explode('.', $left);
                }
                if (strpos($right, '.') !== FALSE) {
                    list($rightTable, $right) = explode('.', $right);
                }

                // filter out extra spaces
                $left = preg_replace('/\s\s+]/', '', $left);
                $right = preg_replace('/\s\s+]/', '', $right);

                $join[] = "{$leftTable}.{$left}={$rightTable}.{$right}";
            }
            // implode the join ON params with a separator
            $join = implode(' AND ', $join);
            // create query
            $this->join .= " JOIN {$table} ON {$join}";
        // column names the same in both tables
        } else {
            try { if (strpos($on, '.') !== FALSE) {
                throw new Fari_Exception('Are you trying to specify a table name? Use an array() instead.'); }
            } catch (Fari_Exception $exception) { $exception->fire(); }

            // filter out extra spaces
            $on = preg_replace('/\s\s+]/', '', $on);

            $this->join .= " JOIN {$table} ON {$this->table}.{$on}={$table}.{$on}";
        }
        
        return $this;
    }

    /**
     * Define the columns we want to retrieve in a select statement
     * @return string
     */
    private function getSelectedColumns() {
        if (isset($this->select)) {
            return $this->select;
        } else {
            return '*';
        }
    }


    /**
     * Get a table query with optional join(s).
     * @return string
     */
    private function getTableQuery() {
        if (isset($this->join)) {
            return $this->table . $this->join;
        } else {
            return $this->table;
        }
    }

    /**
     * Get a where clause.
     * @return string
     */
    private function getWhereQuery() {
        return (isset($this->where)) ? "WHERE {$this->prepareWhere()}" : '';
    }

    /**
     * Column names of data.
     * @return string "column1, column2"
     */
    private function getColumns() {
        return implode(', ', array_keys($this->data));
    }

    /**
     * Prepare data.
     * @return string "`column`=:column"
     */
    private function prepareSet() {
        $result = '';
        foreach ($this->data as $column => $value) {
            $result .= "`$column`=:{$column}, ";
        }
        return substr($result, 0, -2);
    }

    /**
     * Prepare data.
     * @return string ":column1, :column2, :column3"
     */
    private function prepareData() {
        return ':' . implode(', :', array_keys($this->data));
    }

    /**
     * Bind data to a statement.
     * @param <type> $statement
     * @return <type>
     */
    private function bindData($statement) {
        foreach ($this->data as $column => $value) {
            $statement->bindValue(":{$column}", $value, $this->valueType($value));
        }
        
        return $statement;
    }

    /**
     * Prepare a where clause.
     * @return <type>
     */
    private function prepareWhere() {
        $result = '';
        $i = 0;
        foreach ($this->where as $column => $value) {
            // WHERE IN ()
            if (($operator = $this->findOperator($value)) == 'IN') {
                // no binding occurs...
                $result .= "{$column} {$value} AND ";
            // the rest...
            } else {
                $result .= "{$column} {$operator} :id{$i} AND ";
                $i++;
            }
        }
        
        return substr($result, 0, -5);
    }

    /**
     * Bind where clause.
     * @param <type> $statement
     * @return <type>
     */
    private function bindWhere($statement) {
        $i = 0;
        foreach ($this->where as $column => $value) {
            // LIKE statement?
            if (strpos($value, '*') !== FALSE) $value = str_replace('*', '%', $value);
            // strip any operators and whitespace from the value
            $value = preg_replace('/[>|<|=|\s\s+]/', '', $value);
            
            $statement->bindValue(":id{$i}", $value, $this->valueType($value));
            $i++;
        }
        
        return $statement;
    }

    /**
     * Find operator defined with the value.
     * @param <type> $value
     * @return <type>
     */
    private function findOperator($value) {
        // LIKE
        if (strpos($value, '*') !== FALSE) { return 'LIKE';
        // >=
        } elseif (strpos($value, '>=') !== FALSE) { return '>=';
        // <=
        } elseif (strpos($value, '<=') !== FALSE) { return '<=';
        // >
        } elseif (strpos($value, '>') !== FALSE) { return '>';
        // <
        } elseif (strpos($value, '<') !== FALSE) { return '<';
        // !=
        } elseif (strpos($value, '!=') !== FALSE) { return '!=';
        // IN
        } elseif (strpos($value, 'IN') !== FALSE) { return 'IN';
        // =
        } else return '=';
    }

    /**
     * Determine a type of the value.
     * @param <type> $value
     * @return <type>
     */
    private function valueType($value) {
        // a file
        if (get_resource_type($value) == 'stream') {
            return PDO::PARAM_LOB;
        // a string or an integer
        } else {
            return (Fari_Filter::isInt($value)) ? PDO::PARAM_INT : PDO::PARAM_STR;
        }
    }

    /**
     * Clear data.
     */
    private function clearData() {
        $this->data = array();
    }

    /**
     * Bind SQL query into a string.
     * @param string $sql statement
     * @return string
     */
    private function toString($sql) {
        $i = 0;
        // traverse WHERE clause and get binded values...
        foreach ($this->where as $column => $value) {
            // ... replace them with actual values
            $sql = preg_replace("/:id{$i}/", $value, $sql);
            $i++;
        }

        // and data during INSERT
        foreach ($this->data as $column => $value) {
            $sql = preg_replace("/:{$column}/", $value, $sql);
        }
        
        return $sql;
    }
    
}
