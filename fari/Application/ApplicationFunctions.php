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
 * Defines global application functions.
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\Application
 */

/**
 * Autoload Model classes when needed (before exception is thrown).
 * @param string $className
 */
function __autoload($className) {
    // are we working with a Fari Classes?
    if (strpos($className, 'Fari_') === FALSE) {
        // the only exception being a heavily used ORM
        if ($className == 'Table') {
            $classFilePath = BASEPATH . '/fari/Db/DbTable' . EXT;
        } else {
            // explode the class name by camel case
            $className = splitCamelCase($className);
            // the first name in a class could be a folder name, is it?
            if (is_dir(BASEPATH . '/'. APP_DIR . '/models/' . $className[0])) {
                // it is, prefix the model with a directory name
                $classFilePath = BASEPATH . '/'. APP_DIR . '/models/' . $className[0] . '/'
                . implode('', $className) . EXT;
            } else {
                // nah, the 'default'
                $classFilePath = BASEPATH . '/'. APP_DIR . '/models/' . implode($className) . EXT;
            }
        }
    } else {
        // remove Fari_ and build path
        $className = splitCamelCase(substr($className, 5));
        $classFilePath = BASEPATH . '/fari/' . $className[0] . '/' . implode('', $className) . EXT;
    }

    try {
        // check that model class exists
        if (!file_exists($classFilePath)) {
            throw new Fari_Exception('Missing Class: ' . $classFilePath . '.');
        } else include($classFilePath); // include file
    } catch (Fari_Exception $exception) { $exception->fire(); }
}

/**
 * Will split a camelCase class name into parts array
 * @param string $string className
 * @return array
 */
function splitCamelCase($string) {
    return preg_split('/(?<=\\w)(?=[A-Z])/', $string);
}

/**
 * A shorthand function to call Diagnostics output
 * @param mixed $mixed
 * @param string $title Title of the output
 */
function dump($mixed, $title='Variables Dump') {
    if (is_string($mixed) && substr($mixed, 0, 5) == '<?xml') {
        // an XML dump
        if (!headers_sent()) header('Content-Type:text/xml');
        echo $mixed;
    } else {
        // standard dump
        Fari_ApplicationDiagnostics::dump($mixed, $title);
    }
}