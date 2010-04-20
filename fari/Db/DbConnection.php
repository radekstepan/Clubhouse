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
 * DB connection functions.
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\Db
 */
class Fari_DbConnection {

    /** @var PDO */
    private $connection;

    public function __construct($pdoInstance) {
        try {
            if (!$pdoInstance instanceof PDO) {
                throw new Fari_Exception('Use Fari_Db::getConnection() to connect to a database.');
            } else {
                $this->connection = $pdoInstance;
            }
        } catch (Fari_Exception $exception) { $exception->fire(); }
    }



    /********************* select *********************/



    /**
     * Select from a table and return an array.
     * @param string $table Database table we work with
	 * @param string $columns Columns to return
	 * @param array/string $where Where clause in a form array('column' => 'value')
	 * @param string $order Order by clause
	 * @param string $limit Limit by clause
	 * @return array Table
     */
    public function select($table, $columns='*', $where=NULL, $order=NULL, $limit=NULL, $toString=FALSE) {
        // form sql statement
		try {
            $sql = 'SELECT ' . $columns . ' FROM ' . $table;
            // the WHERE clause
            if (isset($where)) {
                // it is an array, do binding
                if (is_array($where)) $sql .= ' WHERE ' . $this->buildColumns(array_keys($where));
                // a string passed, no binding!
                else $sql .= ' WHERE ' . $where;
            }

            // add ordering and limit clauses
            if (isset($order)) $sql .= ' ORDER BY ' . $order;
			if (isset($limit)) $sql .= ' LIMIT ' . $limit;
            
			// prepare statement
            $statement = $this->connection->prepare($sql);

			// bind id parametres
            if (is_array($where)) $statement = $this->bindParameters($where, $statement);

            if ($toString) $this->toString($statement->queryString, NULL, $where);
            else {
                // execute query
                $statement->execute();
                // return associative array
                return $statement->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $exception) {
            try {
                throw new Fari_Exception('Cannot select from ' . $table . ': ' . $exception->getMessage() . '.');
            } catch (Fari_Exception $exception) { $exception->fire(); }
		}
    }

    /**
     * Select from a table and return an array (echo query string).
     * @param string $table Database table we work with
	 * @param string $columns Columns to return
	 * @param array/string $where Where clause in a form array('column' => 'value')
	 * @param string $order Order by clause
	 * @param string $limit Limit by clause
	 * @return echo Query string to the view
     */
    public function selectString($table, $columns='*', $where=NULL, $order=NULL, $limit=NULL) {
        $this->select($table, $columns, $where, $order, $limit, TRUE);
    }

    /**
     * Select a single row from a table and return as a one-dimensional array.
     * @param string $table Database table we work with
	 * @param string $columns Columns to return
	 * @param array/string $where Where clause in a form array('column' => 'value')
	 * @param string $order Order by clause
	 * @param string $limit Limit by clause
	 * @return array Table
     */
    public function selectRow($table, $columns='*', $where=NULL, $order=NULL, $limit=NULL, $toString=FALSE) {
        if ($toString) $this->select($table, $columns, $where, $order, $limit, TRUE);
        else {
            $result = $this->select($table, $columns, $where, $order, $limit);
            return $result[0];
        }
    }

    /**
     * Select a single row from a table and return as a one-dimensional array (echo query string).
     * @param string $table Database table we work with
     * @param string $columns Columns to return
     * @param array/string $where Where clause in a form array('column' => 'value')
     * @param string $order Order by clause
     * @param string $limit Limit by clause
     * @return array Table
     */
    public function selectRowString($table, $columns='*', $where=NULL, $order=NULL, $limit=NULL) {
        $this->selectRow($table, $columns, $where, $order, $limit, TRUE);
    }



    /********************* insert *********************/



	/**
     * Insert into a table.
     * @param string $table Database table we work with
	 * @param array $values Values to insert in a form array('column' => 'value')
	 * @return integer Id of the row inserted
     */
     public function insert($table, array $values, $toString=FALSE) {
		// can't reuse param binding easily as we have (columns) VALUES (values) and not column = value
		$columns = implode(', ', array_keys($values));
		$valuesQuoted = implode(', ', self::addQuotes($values));

		// form sql statement
		try {
            $sql = 'INSERT INTO ' . $table . ' (' . $columns . ') VALUES (' . $valuesQuoted . ')';
            
            // prepare statement
			$statement = $this->connection->prepare($sql);

            if ($toString) $this->toString($statement->queryString, $values);
            else {
                // execute query
                $statement->execute();
                return $this->connection->lastInsertId();
                }
        } catch (PDOException $exception) {
            try {
                throw new Fari_Exception('Cannot insert into ' . $table . ': ' . $exception->getMessage() . '.');
            } catch (Fari_Exception $exception) { $exception->fire(); }
		}
    }

    /**
     * Insert into a table (echo query string).
     * @param string $table Database table we work with
     * @param array $values Values to insert in a form array('column' => 'value')
     */
    public function insertString($table, array $values) {
        $this->insert($table, $values, TRUE);
    }



    /********************* update *********************/



	/**
     * Update a table.
     * @param string $table Database table we work with
	 * @param array $values Values to insert in a form array('column' => 'value')
	 * @param array/string $where Where clause in a form array('column' => 'value')
     */
     public function update($table, array $values, $where=NULL, $toString=FALSE) {
		// form sql statement
		try {
			// use set0, set1 for parameter preparation for binding
            $sql = 'UPDATE ' . $table . ' SET ' . $this->buildColumns(array_keys($values), 'set', ',');

            // the WHERE clause
            if (isset($where)) {
                // it is an array, do binding
                if (is_array($where)) $sql .= ' WHERE ' . $this->buildColumns(array_keys($where));
                // a string passed, no binding!
                else $sql .= ' WHERE ' . $where;
            }

            // prepare statement
			$statement = $this->connection->prepare($sql);

			// bind set and id parametres
			$statement = $this->bindParameters($values, $statement, 'set');
           if (is_array($where)) $statement = $this->bindParameters($where, $statement);

            if ($toString) $this->toString($statement->queryString, $values, $where);
            else return $statement->execute(); // execute query
        } catch (PDOException $exception) {
            try {
                throw new Fari_Exception('Cannot update ' . $table . ': ' . $exception->getMessage());
            } catch (Fari_Exception $exception) { $exception->fire(); }
		}
    }

    /**
     * Update a table (echo query string).
     *
     * @param string $table Database table we work with
     * @param array $values Values to insert in a form array('column' => 'value')
     * @param array $where Where clause in a form array('column' => 'value')
     * @return void
     */
    public function updateString($table, array $values, array $where=NULL) {
            $this->update($table, $values, $where, TRUE);
    }



    /********************* delete *********************/



    /**
     * Delete from a table.
     * @param table name
     * @param string $table Database table we work with
	 * @param array/string $where Where clause in a form array('column' => 'value')
     */
    public function delete($table, $where=NULL, $toString=FALSE) {
        // form sql statement
		try {
            $sql = 'DELETE FROM ' . $table;

            // the WHERE clause
            if (isset($where)) {
                // it is an array, do binding
                if (is_array($where)) $sql .= ' WHERE ' . $this->buildColumns(array_keys($where));
                // a string passed, no binding!
                else $sql .= ' WHERE ' . $where;
            }

            // prepare statement
			$statement = $this->connection->prepare($sql);

			// bind id parametres
            if (is_array($where)) $statement = $this->bindParameters($where, $statement);

            if ($toString) $this->toString($statement->queryString, NULL, $where);
            else $statement->execute(); // execute query
        } catch (PDOException $exception) {
            try {
                throw new Fari_Exception('Cannot delete from ' . $table . ': ' . $exception->getMessage());
            } catch (Fari_Exception $exception) { $exception->fire(); }
		}
    }

    /**
     * Delete from a table (echo query string).
     * @param table name
     * @param string $table Database table we work with
     * @param array $where Where clause in a form array('column' => 'value')
     */
    public function deleteString($table, array $where=NULL) {
        $this->delete($table, $where, TRUE);
    }



    /********************* generic query *********************/


    /**
     * A generic query
     * @param string $sql statement
     * @param array $values to bind
     */
    public function query($sql, array $values) {
        //$stmt = $db->prepare("insert into images (id, contenttype, imagedata) values (?, ?, ?)");
        $statement = $this->connection->prepare($sql);

        // bind parameters
        $i = 1; foreach ($values as &$value) {
            $statement->bindParam($i++, $value, $this->getType($value));
        }

        // execute
        $statement->execute();
    }

    /**
     * An SQL count of number of rows in a table.
     * @param string $table Table name
     * @param array/string $where Where clause
     * @return integer Number of rows
     */
    public function count($table, $where=NULL) {
        // form sql statement
		try {
            $sql = 'SELECT count(*) FROM ' . $table;
            // the WHERE clause
            if (isset($where)) {
                // it is an array, do binding
                if (is_array($where)) $sql .= ' WHERE ' . $this->buildColumns(array_keys($where));
                // a string passed, no binding!
                else $sql .= ' WHERE ' . $where;
            }

			// prepare statement
            $statement = $this->connection->prepare($sql);

			// bind id parametres
            if (is_array($where)) $statement = $this->bindParameters($where, $statement);

            // execute query
            $statement->execute();
            // return associative array
            return $statement->fetchColumn();
        } catch (PDOException $exception) {
            try {
                throw new Fari_Exception('Cannot select from ' . $table . ': ' . $exception->getMessage() . '.');
            } catch (Fari_Exception $exception) { $exception->fire(); }
		}
    }

    

    /********************* helpers *********************/


    
    /**
     * Take a value from subarray and use it as a key (e.g.: use on 'settings' arrays).
     * @param array $array Array with data
     * @param string $key Key to use
     * @return array formatted
     */
    public function toKeyValues(array $array, $key) {
        // traverse the input
        foreach ($array as $arrayKey => $value) {
            // create a new array entry
            $array[$value[$key]] = $value;
            // unset the original key and redundant subarray key
            unset($array[$arrayKey]); unset($array[$value[$key]][$key]);
        }
        return $array;
    }

	/**
     * Will add quotes to column values when inserting into a database.
	 * @param string/array $values Value string or array of values to 'quoteize'
	 * @return array Array with values in quotes
	 */
	private function addQuotes($values) {
		// in case we work with a string
		if (!is_array($values)) return "'$values'";
		// in case we work with an array
		foreach($values as &$value) {
			$value = "'$value'";
		}
		return $values;
	}

    /**
     * The columns builder. Will create numbered :id params that will can be binded.
     * @param string/array $columns Column = param
     * @param string $id ID parameter that will be binded, e.g., id
     * @param string $separator Separator between columns, e.g., AND
     * @return string Query with prepped prams
     */
    private function buildColumns($columns, $id='id', $separator='AND') {
        $sql = '';
		// are we adding an array of values?
		if (is_array($columns)) {
            // start the WHERE clause
            $count = count($columns);
            // traverse the passed arguments
			for ($i=0; $i<$count; $i++) {
                // add where id0, id1 etc. clauses
                $sql .= $columns[$i] . ' = :' . $id . $i;
                // add AND if we are to add more stuff
                if ($i < $count-1) $sql .= ' ' . $separator . ' ';
            }
        } else $sql .= $columns . ' = :' . $id; // just one parameter

		return $sql;
    }

    /**
     * Bind prepped values in a statement of form :id0, id1 etc.
     * @param string/array $values Values we want to bind instead of numeric :id
     * @param string $statement An SQL statement
     * @param string $id ID parameter that will be binded, e.g., id
     * @return string Statement with values binded
     */
    private function bindParameters($values=NULL, $statement, $id='id') {
        // return if nothing to bind
        if (!isset($values)) return $statement;
        try {
            // in case we pass array on...
            if (is_array($values)) {
                // initialize counter for id0 etc..
				$i = 0;
				// traverse values, keys are not numeric
                foreach ($values as $value) {
                    // set parameter data type integer or string
                    $paramType = $this->getType($value);

					// bind parameter :id0, :id1 etc.
                    $statement->bindValue(':' . $id . $i, $value, $paramType);

					// increase counter
					$i++;
                }
            // just one value passed on
			} else {
                // set parameter data type integer or string
                $paramType = $this->getType($values);

                // bind parameter :id
                $statement->bindParam(':' . $id, $values, $paramType);
            }

            return $statement;
        } catch (PDOException $exception) {
            try {
                throw new Fari_Exception('Cannot bind parametres.');
            } catch (Fari_Exception $exception) { $exception->fire(); }
        }
    }

    /**
     * Get type of a value to bind.
     * @param $value
     * @return PDO::PARAM
     */
    private function getType($value) {
        // is this a file stream?
        if (get_resource_type($value) == 'stream') {
            return PDO::PARAM_LOB;
        // either string or an integer then
        } else {
            return (Fari_Filter::isInt($value)) ? PDO::PARAM_INT : PDO::PARAM_STR;
        }
    }

    /**
     * Echo the SQL statement into the view
     * @param string $statement SQL query string
     * @param array $values The values to insert, update
     * @param array/string $where The where clause
     * @return echo Query string into the view
     */
    private function toString($statement, array $values=NULL, $where=NULL) {
        // traverse the values and where clause arrays
        if (is_array($where)) {
            $binder = 'set'; foreach (array($values, $where) as $array) {
                if (isset($array)) {
                    // replace bound parametres with actual values
                    $i=0; foreach ($array as $value) {
                            // determine value type of string or integer
                            $value = (Fari_Filter::isInt($value)) ? "$value" : "'$value'";
                            // we have a variable binding key
                            $statement = preg_replace("/:$binder$i/", $value, $statement);
                            $i++;
                    }
                }
                // a switch to keep track of which array are we traversing
                $binder = 'id';
            }
        }

        // echo into the view
        Fari_ApplicationDiagnostics::dump($statement, 'Fari_Db Query String');
    }
    
}