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
 * Helper functions to use in views.
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\Application
 */

/**
 * Generates a link to a stylesheet in /public.
 * @example <link href="/public/style.css?1213337145" media="screen" rel="stylesheet" type="text/css" />
 * @param string $css path
 */
function stylesheetLinkTag($css) {
    // preconditions
    assert('!empty($css); // stylesteet not defined');
    assert('is_string($css); // pass filename as a string');

    // trim whitespace
    $css = trim($css);

    // do we have a suffix .css?
    if (substr($css, -4) != '.css') $css .= '.css';

    // prepend slash?
    if (substr($css, 0, 1) !== '/') $css = "/{$css}";

    // echo with trailing timestamp
    echo '<link href="' . WWW_DIR . "/public{$css}" . '?' . mktime() .
         '" media="screen" rel="stylesheet" type="text/css" />' . "\n";
}

/**
 * Generates a link to a javascript in /public.
 * @example <script src="/javascripts/jquery.js?1213337144" type="text/javascript"></script>
 * @param string $js path
 */
function javascriptIncludeTag($js) {
    // preconditions
    assert('!empty($js); // javascript file not defined');
    assert('is_string($js); // pass filename as a string');
    
    // trim whitespace
    $js = trim($js);

    // do we have a suffix .js?
    if (substr($css, -3) != '.js') $js .= '.js';

    // prepend slash?
    if (substr($js, 0, 1) !== '/') $js = "/{$js}";

    // echo with trailing timestamp
    echo '<script src="' . WWW_DIR . "/public{$js}" . '?' . mktime() .
         '" type="text/javascript"></script>' . "\n";
}

/**
 * Generates a link to an image with alt in /public.
 * @example <img alt="Bg" src="/images/bg.jpg?1213337144" />
 * @param string $img path
 */
function imageTag($img) {
    // preconditions
    assert('!empty($img); // image file not defined');
    assert('is_string($img); // pass filename as a string');

    // trim whitespace
    $img = trim($img);

    // prepend slash?
    if (substr($img, 0, 1) !== '/') $img = "/{$img}";

    // determine file so we can generate alt tag
    $file = ucfirst(str_replace("-", " ", current(explode('.', end(explode('/', $img))))));

    // echo with trailing timestamp
    echo "<img alt=\"{$file}\" src=\"" . WWW_DIR . "/public{$img}" . '?' . mktime() . '" />';
}

/**
 * Generates a link to an icon.
 * @example <link rel="shortcut icon" type="image/x-icon" href="/icon.ico" />
 * @param string $icon path
 */
function faviconTag($icon='favicon') {
    // preconditions
    assert('is_string($icon); // pass filename as a string');

    // do we have a suffix .ico?
    if (substr($icon, -4) != '.ico') $icon .= '.ico';

    // trim whitespace
    $icon = trim($icon);

    // prepend slash?
    if (substr($icon, 0, 1) !== '/') $icon = "/{$icon}";

    // echo with trailing timestamp
    echo '<link rel="shortcut icon" type="image/x-icon" href="' . WWW_DIR . "/public{$icon}" . '" />' . "\n";
}

/**
 * Transforms all URLs or e-mail addresses within the string into clickable HTML links.
 * @param string $string email or url
 * @param string $ref reference to the link (optional)
 * @param string $type link/email (optional)
 */
function autoLink($string, $ref=NULL, $type=NULL) {
    // target reference
    if (!isset($ref)) $ref = $string;

    // it's an email
    if ($type == 'email' || Fari_Filter::isEmail($string)) {
        echo "<a href=\"mailto:{$string}\">{$ref}</a>";
    // or a link
    } else {
        // formed URL, just echo as a link
        if (Fari_Filter::isURL($string)) {
            echo '<a href="' . $string . '">' . $ref . '</a>';
        } else {
            // prefix with BASEPATH so we can link internally
            if (substr($string, 0, 1) !== '/') $string = "/{$string}";
            echo '<a href="' . WWW_DIR . $string . '">' . $ref . '</a>';
        }
    }

}

/**
 * Create a link.
 * @param string $string url
 * @param string $ref reference to the link
 */
function linkTo($link, $ref='link') {
    autoLink($link, $ref, 'link');
}

/**
 * Create an email.
 * @param string $string email
 * @param string $ref reference to the link
 */
function mailTo($link, $ref='email') {
    autoLink($link, $ref, 'email');
}

/**
 * Will apply a highlight class to text.
 * @param string $string input text
 * @param string $highlight regex used in preg_replace
 */
function highlight($string, $highlight) {
    // preconditions
    assert('!empty($highlight); // you need to define what to highlight');
    // apply highlight
    echo preg_replace("/($highlight)/", "<strong class='highlight'>$1</strong>", $string);
}

/**
 * Truncate string to $length characters.
 * @param string $string input text
 * @param integer $length
 */
function truncate($string, $length) {
    // preconditions
    assert('is_int($length); // length needs to be an integer');
    echo substr($string, 0, $length);
}

/**
 * Format number into a human readable size.
 * @param integer/float $number
 */
function numberToHumanSize($number) {
    assert('is_int($number) || is_float($number); // you need to pass a number');
    echo Fari_Format::bytes($number);
}

/**
 * Format number into a currency.
 * @param integer/float $number
 * @param string $format GBP/EUR/CZK
 */
function numberToCurrency($number, $format='GBP') {
    assert('is_int($number) || is_float($number); // you need to pass a number');
    echo Fari_Format::currency($number, $format);
}

/**
 * Create a textual representation of a time in the past/future
 * @param <type> $timestamp
 */
function timeAgoInWords($timestamp) {
    assert('is_int($timestamp); // you need to pass a timestamp');
    echo Fari_Format::age($timestamp);
}