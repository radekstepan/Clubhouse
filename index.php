<?php

/**
 * Fari Framework
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link      http://radekstepan.com
 * @category  Fari Framework
 */



// start session
if (!isset($_SESSION)) session_start();



// Step 1a: Define absolute environment values
// set so that we can check if PHP pages have been accessed directly
if (!defined('FARI')) define('FARI', 'Fari Framework 2.2.2.1 (May 10, 2010)');

// get absolute pathname and define it as a constant (server install path)
if (!defined('BASEPATH')) define('BASEPATH', dirname(__FILE__));
// www root dir (path for links in your views)
if (!defined('WWW_DIR')) {
	// now we can have the app in the root
	dirname($_SERVER['SCRIPT_NAME']) == '/' ? define('WWW_DIR', '') : define('WWW_DIR', dirname($_SERVER['SCRIPT_NAME']));
}

// default file extension (.php)
if (!defined('EXT')) define('EXT', '.' . pathinfo(__FILE__, PATHINFO_EXTENSION));



// Step 1b: Include database and application specific settings
require BASEPATH . '/config/config' . EXT;



// Step 2a: Initialize Error & Exceptions handling and check environment
require BASEPATH . '/fari/Application/ApplicationDiagnostics' . EXT;

// check that we have a high enough version of PHP (5.2.0)
try { if (version_compare(phpversion(), '5.2.0', '<=') == TRUE) {
	throw new Fari_Exception('Fari Framework requires PHP 5.2.0, you are using ' . phpversion() . '.'); }
} catch (Fari_Exception $exception) { $exception->fire(); }

// check if user is using Apache user-directory found on temporary links to web hosting (e.g., http://site.com/~user/)
try { if (substr_count(WWW_DIR, '~') > 0) {
	throw new Fari_Exception('Apache user-directory ' . WWW_DIR . ' not supported by Fari Framework.'); }
} catch (Fari_Exception $exception) { $exception->fire(); }

// Step 2b: Setup contracts handling
require BASEPATH . '/fari/Application/ApplicationContracts' . EXT;


// Step 3: Define global functions and those required for framework start
function splitCamelCase($string) {
    return preg_split('/(?<=\\w)(?=[A-Z])/', $string);
}

// a shorthand function to call Diagnostics output
function dump($mixed, $title='Variables Dump') {
    Fari_ApplicationDiagnostics::dump($mixed, $title);
}

// echo URL to the (cached) View
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



// Step 3b: Autoload Model classes when needed (before exception is thrown)
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



// Step 4: Start the whole shebang
Fari_ApplicationRouter::loadRoute();
