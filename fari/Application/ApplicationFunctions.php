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
    Fari_ApplicationDiagnostics::dump($mixed, $title);
}

/**
 * Echo URL to the (cached) View.
 * @param string $link Controller/Action to call
 * @param boolean $echo echo output immediatelly?
 * @param boolean $domain prepend domain?
 */
function url($link, $echo=TRUE, $domain=FALSE) {
    // we want a full domain name
    if ($domain) {
        // assume we are either using HTTP or HTTPS
        $url = ($_SERVER['HTTPS'] != 'on') ? 'http://' . $_SERVER['HTTP_HOST'] . WWW_DIR . '/' . $link :
        'https://' . $_SERVER['HTTP_HOST'] . WWW_DIR . '/' . $link;
    } else {
        // default link
        $url = ($link[0] == '/') ? WWW_DIR . $link : WWW_DIR . '/' . $link;
    }

    // echo to the view or return as a string
    if ($echo) echo $url; else return $url;
}