<?php if (!defined('FARI')) die();

/**
 * Fari Framework
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link      http://radekstepan.com
 * @category  Fari Framework
 */



// custom URL rewriting
$customRoutes = array(
    '/login/' => 'auth/login/',
    '/logout/' => 'auth/logout/',
    '/javascript/' => 'error404/ajax/',
    '/room\/([\0-9])/' => 'room/index/\1',
    '/account\/invitation\/([\0-9a-z]*)/' => 'account/create/\1',
    '/g\/([\0-9a-z]*)/' => 'guest/index/\1',
    '/transcripts\/page\/([\0-9a-z]*)/' => 'transcripts/index/\1',
    '/guest\/new/' => 'guest/create/'
);