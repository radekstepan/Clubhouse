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
 * @example $table->findFirst()->where(array('id' => '> 1'));
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\Db
 */
class Table {

    /** @var PDO */
    private $db;

    /** @var string table name */
    public $tableName;

    /** @var string primary key to use when determining if row column value is unique etc */
    public $primaryKey = 'id';

    /** @var array of data to save */
    public $data;

    /** @var limit the fields we return in find* statements */
    public $select;

    /** @var array of data in the where clause */
    public $where;

    /** @var string private name of method to call from a where clause */
    private $method;

    /** @var ordering and result set limit */
    private $order;
    private $limit;

    /** @var string join table string */
    public $join;

    /** @Fari_DbLogger observer subject for logging */
    private $logger;

    /** @Fari_DbValidator observer subject for query validation */
    private $validator;

    /** @Fari_DbTableRelationships prep relationships so we can relate */
    private $relationships;

    /**
	 * Setup a database connection (to a table)
	 * @param string an optional table name, optional only in PHP <= 5.3.0
	 */
    public function __construct($tableName=NULL) {
        // db connection
        $this->db = Fari_DbPdo::getConnection();

        // table name exists?
        if (isset($tableName)) {
            $this->tableName = $tableName;
        } else if (isset($this->tableName)) {
            assert('!empty($this->tableName); // table name needs to be provided');
        } else {
            // are we using high enough version of PHP for late static binding?
            try { if (version_compare(phpversion(), '5.3.0', '<=') == TRUE) {
                throw new Fari_Exception('Table name can automatically only be resolved in PHP 5.3.0.'); }
            } catch (Fari_Exception $exception) { $exception->fire(); }

            // ... yes, get the name of the class as the name of the table
            $this->tableName = get_called_class();
        }

        // attach observers
        $this->logger = new Fari_DbLogger();
        $this->logger->attach(new Fari_ApplicationLogger());

        // attach validator
        $this->validator = $this->attachValidator();

        // prep relationships
        $this->relationships = new Fari_DbTableRelationships();
    }

    /**
     * Returns a validator object, return NULL to switch it off.
     * @return Fari_DbTableValidator
     */
    private function attachValidator() {
        return new Fari_DbTableValidator();
    }

    /**
     * Rails-like calls, captures undefined methods via method overloading.
     * @param string $method
     * @param mixed $params
     */
    public function __call($method, $params) {
        try {
            // determine the method called
            if (!preg_match('/^(findFirst|findLast|find)(\w+)$/', $method, $matches)) {
                throw new Fari_Exception("Call to undefined method {$method}");
            }

            // what do you want?
            switch ($matches[1]) {
                // setup relationship if is possible
                case 'findFirst':
                case 'findLast':
                case 'find':
                    $this->relationships->iCanHazQuery(&$this, strtolower($matches[2]));
                    // then call find as usual...
                    return $this->$matches[1]();
                    break;
                default:
                    throw new Fari_Exception("Call to undefined parameter {$matches[1]}");
            }

        } catch (Fari_Exception $exception) { $exception->fire(); }
    }



    /********************* set values *********************/



    /**
     * Magic setter.
     * @param mixed $column in a table
     * @param mixed $value to save
     */
    public function __set($column, $value) {
        // save
        $this->data[$column] = $value;
 	}

    /**
     * Save a whole array of items for a query.
     * @param array of key:value
     * @return Table, call with add()
     */
    public function set(array $values) {
        foreach ($values as $key => $value) {
            // set
            $this->$key = $value;
        }

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
                } else {
                    // primary key I can haz?
                    assert('!empty($this->primaryKey); // primary key needs to be defined');
                    // do we have a join?
                    if (!empty($this->join)) {
                        // prepend our table name then...
                        $this->where = array("{$this->tableName}.{$this->primaryKey}" => $where);
                    } else {
                        $this->where = array($this->primaryKey => $where);
                    }
                }
            } catch (Fari_Exception $exception) { $exception->fire(); }
        }

        // have we defined the db method first?
        try { if (!isset($this->method)) {
            throw new Fari_Exception('First specify the method you would like to execute, then the where clause.'); }
        } catch (Fari_Exception $exception) { $exception->fire(); }

        // any relationships?
        $this->relationships->checkHazQuery(&$this);

        // method call
        $result = $this->{$this->method}();
        // if we are finding first result...
        //if (($this->limit == 1 && $this->method == '_find')) {
        //    // set result internally (so we can easily update rows etc...)
        //    $this->set($result);
        //    // return a bag of values
        //    $bag = new Fari_Bag();
        //    $bag->set($result);
        //}
        //

        // return the result from a method call
        return $result;
    }



    /********************* columns select *********************/



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



    /********************* limit & order *********************/



    /**
     * ORDER BY clause,
     * @param string $order
     * @return Table, need to define a where clause
     */
    public function orderBy($order) {
        $this->order = $this->checkOrder($order);
        return $this;
    }

    /**
     * LIMIT clause,
     * @param string $limit
     * @return Table, need to define a where clause
     */
    public function limit($limit) {
        if (!empty($limit)) {
            assert("is_int(\$limit); // limit needs to be an integer");
            $this->limit = $limit;
        }
        return $this;
    }



    /********************* find queries *********************/



    /**
     * Find item(s) in a table.
     * @return Table, need to define a where clause
     */
    public function find() {
        $this->method = '_find';
        return $this;
    }

    /**
     * Find first occurence of an item in a table.
     * @return Table, need to define a where clause
     */
    public function findFirst() {
        $this->limit = 1;
        assert('isset($this->primaryKey); // primary key needs to be set');
        $this->orderBy("{$this->primaryKey} ASC");
        $this->method = '_find';
        return $this;
    }

    /**
     * Find last occurence of an item in a table.
     * @return Table, need to define a where clause
     */
    public function findLast() {
        $this->limit = 1;
        assert('isset($this->primaryKey); // primary key needs to be set');
        $this->orderBy("{$this->primaryKey} DESC");
        $this->method = '_find';
        return $this;
    }

    /**
     * Return all items in a table.
     * @return array result set
     */
    public function findAll() {
        return $this->_find();
    }



    /********************* delete queries *********************/



    /**
     * Remove item(s) from a table.
     * @return Table, need to define a where clause
     */
    public function delete() {
        $this->method = '_delete';

        return $this;
    }

    /**
     * Remove all items from a table.
     * @return integer number of rows affected
     */
    public function deleteAll() {
        return $this->_delete();
    }



    /********************* update queries *********************/



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



    /********************* counter queries *********************/



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



    /********************* insert queries *********************/



    /**
     * Insert data into a table.
     * @param array $values optionally pass them directly instead of using set() first
     * @return id of the inserted row
     */
    public function save(array $values=NULL) {
        return $this->add($values);
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
        $sql = "INSERT INTO {$this->tableName} ({$this->getColumns()}) VALUES ({$this->prepareData()})";

        // prepare SQL
        $statement = $this->db->prepare($sql);
        // bind data
        $statement = $this->bindData($statement);

        // notify
        $this->logger->notify($this->toString($sql));

        // execute query
        $statement->execute();

        // reset the saved data
        $this->clearData();

        // return id of the row
        return $this->db->lastInsertId();
    }



    /********************* generic query *********************/



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



    /********************* internal *********************/



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

        // we are going to clear the limit so...
        $limit = $this->limit;

        // reset data
        $this->clearData();

        // execute query and return an array result
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        return ($limit == 1) ? (empty($result)) ? array() : current($result) : $result;
    }

    /**
     * Remove items from a table.
     * @return integer number of rows affected
     */
    private function _delete() {
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
                $leftTable = $this->tableName;
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

            $this->join .= " JOIN {$table} ON {$this->tableName}.{$on}={$table}.{$on}";
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
            return $this->tableName . $this->join;
        } else {
            return $this->tableName;
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
     * Bind data to a statement and optionally run a validation.
     * @param <type> $statement
     * @return <type>
     */
    private function bindData($statement) {
        foreach ($this->data as $column => $value) {
            $statement->bindValue(":{$column}", $value, $this->valueType($value));
        }

        // is validator set?
        // TODO: instanceof might change in the future!!
        if ($this->validator instanceof Fari_DbTableValidator) {
            // run validation on us
            $this->validator->validate($this);
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
            // WHERE (NOT) IN ()
            if (strpos($operator = $this->findOperator($value), 'IN') !== FALSE) {
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
        foreach ($this->where as $column => &$value) {
            // (NOT)LIKE statements?
            if (substr($value, 0, 1) == '!') $value = substr($value, 1);
            if (substr($value, 0, 1) == '*') $value = str_replace('*', '%', $value);
            // skip WHERE (NOT) IN
            else if (substr($value, 0, 2) == 'IN' || substr($value, 0, 6) == 'NOT IN') continue;
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
        if (substr($value, 0, 1) == '*') { return 'LIKE';
        // NOT LIKE
        } elseif (substr($value, 0, 2) == '!*') { return 'NOT LIKE';
        // >=
        } elseif (substr($value, 0, 2) == '>=') { return '>=';
        // <=
        } elseif (substr($value, 0, 2) == '<=') { return '<=';
        // >
        } elseif (substr($value, 0, 1) == '>') { return '>';
        // <
        } elseif (substr($value, 0, 1) == '<') { return '<';
        // !=
        } elseif (substr($value, 0, 2) == '!=') { return '!=';
        // NOT IN
        } elseif (substr($value, 0, 6) == 'NOT IN') { return 'NOT IN';
        // IN
        } elseif (substr($value, 0, 2) == 'IN') { return 'IN';
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
        $this->join = NULL;
        $this->limit = NULL;
        $this->order = NULL;
        $this->select = NULL;
        $this->where = NULL;
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

    /**
     * Will check that ORDER clause is properly formatted and prepend table name if needed.
     * @param string $order
     */
    private function checkOrder($order=NULL) {
        if (isset($order)) {
            assert("strpos(\$order, 'ASC') !== FALSE OR strpos(\$order, 'DESC'); // malformed ORDER clause");
        } else {
            // order by primary key
            assert("!empty(\$this->primaryKey); // primary key needs to be set");
            // do we have a join?
            if (isset($this->join) && !empty($this->join)) {
                // prepend a table name
                $order = "{$this->tableName}.{$this->primaryKey} ASC";
            } else {
                $order = "{$this->primaryKey} ASC";
            }
        }

        return $order;
    }

}