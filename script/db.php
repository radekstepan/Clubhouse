<?php

/**
 * Fari Framework
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link      http://radekstepan.com
 * @category  Fari Framework
 */



/**
 * Creates database tables from schemas.
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Scripts\
 */



// check we are actually using cli
if (PHP_SAPI !== 'cli') die();

// action
$action = @$argv[1];
// parameter
$parameter = @$argv[2];

// base path to application
if (!defined('BASEPATH')) define('BASEPATH', dirname(__FILE__) . '/..');

include_once('helpers.php');

// 'menu'
switch ($action) {
    case "build":
        buildSchema();
        break;
    case '-help':
    case 'help':
    case '':
        message("Usage: php script/db.php [build]", 'green');
        break;
    default:
        // fail
        message("Couldn't find '{$action}' generator try 'help'", 'red');
}



/********************* build sql schema *********************/



/**
 * Build schema from settings file.
 */
function buildSchema() {
    // does the settings file exist?
    if (!file_exists(BASEPATH . '/config/config.php')) {
        message("Couldn't find settings file config/config.php", 'red');
    } else {
        // include file
        if (!defined('FARI')) define('FARI', 'Fari Framework CLI');
        require BASEPATH . '/config/config.php';
        
        // check database settings are present
        if (!defined('DB_DRIVER')) message("Database driver 'DB_DRIVER' needs to be specified", 'red');
        if (!defined('DB_HOST')) message("Database host 'DB_HOST' needs to be specified", 'red');
        if (!defined('DB_NAME')) message("Database name 'DB_NAME' needs to be specified", 'red');

        // try connecting to the database
        try {
            switch (strtolower(DB_DRIVER)) {
                // MySQL
                case 'mysql':
                    $pdoInstance = new PDO('mysql:host=' .
                                  DB_HOST . ';dbname=' .
                                  DB_NAME .
                                  ';unix_socket=/var/run/mysqld/mysqld.sock',
                                  DB_USER, DB_PASS);
                    break;

                // PostgreSQL (untested)
                case 'pgsql':
                    $pdoInstance = new PDO('pgsql:dbname=' . DB_NAME . ';host=' .
                                  DB_HOST, DB_USER, DB_PASS);
                    break;

                // SQLite 3
                case 'sqlite3':
                case 'sqlite':
                    $pdoInstance = new PDO('sqlite:' . BASEPATH . '/' . DB_NAME);
                    break;

                // SQLite 2
                case 'sqlite2':
                    $pdoInstance = new PDO('sqlite2:' . BASEPATH . '/' . DB_NAME);
                    break;
            }
        } catch (PDOException $exception) {
            message('Cannot connect to DB: ' . $exception->getMessage(), 'red');
            die();
        }

        // can we open schema file?
        if (!is_readable($f = BASEPATH . '/db/schema.sql')) {
            message("Couldn't find schema file db/schema.sql", 'red');
        } else {
            $schemas = explode("\n", file_get_contents($f));

            // traverse executing each line
            foreach ($schemas as $schema) {
                // a query (maybe) :)
                if (substr(trim($schema), 0, 1) !== '#') {
                    if ($pdoInstance->exec($schema) === FALSE) {
                        // did we try to create a table?
                        $schema = explode(' ', $schema);
                        if ($schema[0] == 'CREATE' && $schema[1] == 'TABLE') {
                            // check if table exists
                            if ($pdoInstance->exec("SELECT * FROM {$schema[2]} LIMIT 1") !== FALSE) {
                                $schema = implode(' ', $schema);
                                message("      exists  '{$schema}'", 'gray');
                                continue;
                            }
                        }
                        message("      failed  '{$schema}'", 'red');
                    } else {
                        message("      create  '{$schema}'", 'black');
                        // were we trying to create table?
                        $schema = explode(' ', $schema);
                        if ($schema[0] == 'CREATE' && $schema[1] == 'TABLE') {
                            $tableName = ucfirst(preg_replace("/[^a-zA-Z0-9\s]/", "", $schema[2]));

                            // determine primary key in the table
                            $schema = explode(',', implode(' ', $schema));
                            foreach ($schema as $element) {
                                // do we have primary key?
                                if (strpos($element, 'PRIMARY KEY') !== FALSE) {
                                    $element = substr($element, strpos($element, '(') + 1);
                                    $fields = explode(', ', $element);
                                    foreach ($fields as $field) {
                                        // column field with PRIMARY KEY defined
                                        if (strpos($field, 'PRIMARY KEY') !== FALSE) {
                                            $primaryKey = preg_replace("/[^a-zA-Z0-9\s]/",
                                                "",
                                                current(explode(' ', $field)));
                                            break;
                                        }
                                    }
                                    break;
                                }
                            }

                            if (!defined('BACKSTAGE')) define('BACKSTAGE', TRUE);
                            include_once('generate.php');

                            // create model
                            newModel($tableName, $primaryKey);
                        }
                    }
                } else {
                    // a comment...
                    message($schema, 'gray');
                }
            }
        }
    }
}