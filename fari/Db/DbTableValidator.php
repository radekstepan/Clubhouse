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
                    throw new Fari_DbTableValidatorException("'$column' column needs to be set");
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
                // integer?
                || !is_int($table->data[$column])
                // length?
                || $table->data[$column] < $length
                )
                    throw new Fari_DbTableValidatorException("'$column' column is too short");
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
                throw new Fari_DbTableValidatorException("'$column' column does not exist");
            } else {
                // query for first occurence of the same column value
                $result = $table->findFirst()->where(array($column => $table->data[$column]));
                if (!empty($result)) {
                    throw new Fari_DbTableValidatorException("'$column' value is not unique");
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
                throw new Fari_DbTableValidatorException("'$column' column does not exist");
            } else {
                // regex match?
                if (preg_match($regex, $table->data[$column]) != 1)
                    throw new Fari_DbTableValidatorException("'$column' column does not match the format");
            }
        }
    }
    
}