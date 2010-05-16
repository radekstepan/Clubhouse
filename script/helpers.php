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
 * Helpers.
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Scripts\
 */



// check we are actually using cli
if (PHP_SAPI !== 'cli') die();

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