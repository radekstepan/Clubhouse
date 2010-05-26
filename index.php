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
if (!defined('FARI')) define('FARI', 'Fari Framework 2.3.14.1 (May 27, 2010)');

// get absolute pathname and define it as a constant (server install path)
if (!defined('BASEPATH')) define('BASEPATH', dirname(__FILE__));
// www root dir (path for links in your views)
if (!defined('WWW_DIR')) {
	// now we can have the app running in the root
	dirname($_SERVER['SCRIPT_NAME']) == '/' ? define('WWW_DIR', '')
        : define('WWW_DIR', dirname($_SERVER['SCRIPT_NAME']));
}

// default file extension (.php)
if (!defined('EXT')) define('EXT', '.' . pathinfo(__FILE__, PATHINFO_EXTENSION));



// Step 1b: Include database and application specific settings
require BASEPATH . '/config/config' . EXT;



// Step 2a: Initialize Error & Exceptions handling and check environment
require BASEPATH . '/fari/Application/ApplicationDiagnostics' . EXT;
require BASEPATH . '/fari/Application/ApplicationEnvironment' . EXT;
Fari_ApplicationEnvironment::startupCheck();

// Step 2b: Setup contracts handling
require BASEPATH . '/fari/Application/ApplicationContracts' . EXT;


// Step 3: Define global functions, autoloading and those required for framework start
require BASEPATH . '/fari/Application/ApplicationFunctions' . EXT;



// Step 4: Start the whole shebang
Fari_ApplicationRouter::loadRoute();
