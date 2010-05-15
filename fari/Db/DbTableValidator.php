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
 * Thrown on data validation.
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\Db
 */
class TableException extends Exception {}



/**
 * Validates queries, entirely customizable.
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\Db
 */
class Fari_DbTableValidator {

    /**
     * Validates queries set in a Table object.
     * @param Table $table
     */
    public function validate(Table $table) {
        // validates that column data are set and not empty
        if (isset($table->validatesPresenceOf)) {
            $this->validatePresenceOf($table);
        }
        // validates that column data are of minimum length
        if (isset($table->validatesLengthOf)) {
            $this->validateLengthOf($table);
        }
        // validates uniqueness of a column
        if (isset($table->validatesUniquenessOf)) {
            $this->validateUniquenessOf($table);
        }
        // validates regex format of a column
        if (isset($table->validatesFormatOf)) {
            $this->validateFormatOf($table);
        }
    }

    /**
     * Validates that column data are set and not empty.
     * @param Table $table object
     * @throws Fari_DbTableValidatorException
     */
    private function validatePresenceOf(Table &$table) {
        assert('is_array($table->validatesPresenceOf); // needs to be an array');
        foreach ($table->validatesPresenceOf as $column) {

            // exists?
            if (!array_key_exists($column, $table->data)
                // not empty?
                || empty($table->data[$column])
                )
                    throw new TableException("'$column' column needs to be set");
        }
    }

    /**
     * Validates that column data are of minimum length.
     * @param Table $table object
     * @throws Fari_DbTableValidatorException
     */
    private function validateLengthOf(Table &$table) {
        assert('is_array($table->validatesLengthOf); // needs to be an array');
        foreach ($table->validatesLengthOf as $length) {
            assert('is_array($length); // needs to be an array, column => length');
            $column = key($length);
            $length = end($length);
            assert('is_int($length); // needs to be an integer');

            // exists?
            if (!array_key_exists($column, $table->data)
                // not empty?
                || empty($table->data[$column])
                // length?
                || strlen($table->data[$column]) < $length
                )
                    throw new TableException("'$column' column is too short");
        }
    }

    /**
     * Validates uniqueness of a column value in a table.
     * @param Table $table object
     * @throws Fari_DbTableValidatorException
     */
    private function validateUniquenessOf(Table &$table) {
        assert('is_array($table->validatesUniquenessOf); // needs to be an array');
        foreach ($table->validatesUniquenessOf as $column) {

            // exists?
            if (!array_key_exists($column, $table->data)) {
                throw new TableException("'$column' column does not exist");
            } else {
                // need to construct a new Table not to overwrite anything in the object we are validating
                $test = new Table($table->table);
                // query for first occurence of the same column value
                $result = $test->findFirst()->where(array($column => $table->data[$column]));
                unset($test);
                if (!empty($result)) {
                    // we better have primary key defined
                    assert('!empty($table->primaryKey); // primary key has to be defined');
                    // we might be editing 'us' thus check for ID... not perfect all!
                    if (array_key_exists($table->primaryKey, $table->where)) {
                        if ($table->where[$table->primaryKey] != $result[$table->primaryKey]) {
                            throw new TableException("'$column' value is not unique");
                        }
                    }
                }
            }
        }
    }

    /**
     * Validates regex format of a column.
     * @param Table $table object
     * @throws Fari_DbTableValidatorException
     */
    private function validateFormatOf(Table &$table) {
        assert('is_array($table->validatesFormatOf); // needs to be an array');
        foreach ($table->validatesFormatOf as $regex) {
            assert('is_array($regex); // needs to be an array, column => regex');
            $column = key($regex);
            $regex = end($regex);

            // exists?
            if (!array_key_exists($column, $table->data)) {
                throw new TableException("'$column' column does not exist");
            } else {
                // regex match?
                if (preg_match($regex, $table->data[$column]) != 1)
                    throw new TableException("'$column' column does not match the format");
            }
        }
    }

}