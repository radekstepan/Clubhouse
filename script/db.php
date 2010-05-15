#!/usr/bin/env php
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
if (!defined('BASEPATH')) define('BASEPATH', dirname(__FILE__).'/..');

// 'menu'
switch ($action) {
    case "schema":
        if ($parameter !== 'load') {
            message("Usage: php script/db.php schema [load]", 'red');
        } else {
            loadSchema();
        }
        break;
    case '-help':
    case 'help':
    case '':
        message("Usage: php script/db.php [schema] parameter", 'green');
        break;
    default:
        // fail
        message("Couldn't find '{$action}' generator try 'help'", 'red');
}



/********************* create new presenter *********************/



/**
 * Load schema from settings file.
 */
function loadSchema() {
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
                        message("Couldn't execute: '{$schema}'", 'red');
                    } else {
                        message("Executed: '{$schema}'", 'green');
                    }
                } else {
                    // a comment...
                    message($schema, 'gray');
                }
            }
        }
    }
}



/********************* helpers *********************/



/**
 * Display a message in the terminal.
 * @param string $string to display
 * @param string $color to use
 */
function message($string, $color='black') {
    // color switcher
    switch ($color) {
        case "magenta":
            echo "[1;36;1m{$string}[0m\n";
            break;
        case "violet":
            echo "[1;35;1m{$string}[0m\n";
            break;
        case "blue":
            echo "[1;34;1m{$string}[0m\n";
            break;
        case "yellow":
            echo "[1;33;1m{$string}[0m\n";
            break;
        case "green":
            echo "[1;32;1m{$string}[0m\n";
            break;
        case "red":
            echo "[1;31;1m{$string}[0m\n";
            break;
        case "gray":
            echo "[1;30;1m{$string}[0m\n";
            break;
        case "black":
        default:
            echo "[1;29;1m{$string}[0m\n";
    }
}
