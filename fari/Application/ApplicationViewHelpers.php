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
 * Helper class containing values etc.
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\Application
 */
final class Fari_ApplicationViewHelper {

    /** @var array of values coming from the presenter */
    private static $values;

    /** @var string presenter name */
    private static $presenter;

    /**
     * Set values.
     * @param array $values
     */
    public static function setValues(array $values) {
        self::$values = $values;
    }

    /**
     * Get values
     * @return array
     */
    public static function getValues() {
        return self::$values;
    }

    /**
     * Set presenter name.
     * @param string $presenterName
     */
    public static function setPresenter($presenterName) {
        self::$presenter = $presenterName;
    }

    /**
     * Get presenter name.
     * @return string
     */
    public static function getPresenter() {
        return self::$presenter;
    }

}



/********************* helpers accessible from the template/partial *********************/



/**
 * Helper functions to use in views.
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\Application
 */

/**
 * Render a view template partial.
 * @param string $name to load without the prefix
 */
function renderPartial($name) {
    assert('!empty($name); // provide a name for the partial');

    // are we using a custom partial or default?
    if (strpos($name, '/') !== FALSE) {
        $partial = BASEPATH . '/' . APP_DIR . '/views/' . ucfirst(current($name = explode('/', $name)))
            . '/_' . end($name) . '.phtml';
    } else {
        // build up a path using underscore prefix for a partial
        $partial = BASEPATH . '/' . APP_DIR . '/views/' . Fari_ApplicationViewHelper::getPresenter()
            . '/_' . $name . '.phtml';
    }

    // is it valid?
    try {
        // check if file path exists
        if (!file_exists($partial)) {
            throw new Fari_Exception('Partial could not be located in: ' . $partial);
        }
    } catch (Fari_Exception $exception) { $exception->fire(); }

    // extract values so we have access to them
    extract(Fari_ApplicationViewHelper::getValues(), EXTR_SKIP);

    // include it finaly with comments around, but only in development mode
    $devMode = Fari_ApplicationEnvironment::isDevelopment();
    if ($devMode) echo "\n<!-- begin {$partial} -->\n";
    include $partial;
    if ($devMode) echo "\n<!-- end {$partial} -->\n";
}

/**
 * Retrieve flashed messages.
 * @return array (key => text)
 */
function flash() {
    $messages = array();
    // traverse the whole session looking for messages
    foreach ($_SESSION as $key => $value) {
        // our messages
        if (strstr($key, 'Fari\Flash\\' . APP_SALT) !== FALSE) {
            // 'save' message to the array
            $messages[$key] = $value;
            // 'delete' the message
            unset($_SESSION[$key]);
        }
    }
    return $messages;
}

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

    // append timestamp only in dev environment
    $mktime = Fari_ApplicationEnvironment::isDevelopment() ? '?' . mktime() : '';

    // echo with trailing timestamp
    echo '<link href="' . WWW_DIR . "/public{$css}" . $mktime .
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

    // append timestamp only in dev environment
    $mktime = Fari_ApplicationEnvironment::isDevelopment() ? '?' . mktime() : '';

    // echo with trailing timestamp
    echo '<script src="' . WWW_DIR . "/public{$js}" . $mktime .
         '" type="text/javascript"></script>' . "\n";
}

/**
 * Form an atom RSS feed
 * @param string $link
 * @param boolean $echo echo output immediatelly?
 */
function atomTag($link, $echo=TRUE) {
    $url = url($link, FALSE);
    $tag = '<link href="'.$url.'" rel="alternate" title="ATOM" type="application/atom+xml" />';
    if ($echo) echo $tag; else return $tag;
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

    // append timestamp only in dev environment
    $mktime = Fari_ApplicationEnvironment::isDevelopment() ? '?' . mktime() : '';

    // echo with trailing timestamp
    echo "<img alt=\"{$file}\" src=\"" . WWW_DIR . "/public{$img}" . $mktime . '" />';
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
 * Echo URL.
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

/**
 * Will apply a highlight class to text.
 * @param string $string input text
 * @param string $highlight regex used in preg_replace
 * @param boolean $echo echo output immediatelly?
 */
function highlight($string, $highlight, $echo=TRUE) {
    // preconditions
    assert('!empty($highlight); // you need to define what to highlight');
    // apply highlight
    $string = preg_replace("/($highlight)/i", "<strong class='highlight'>$1</strong>", $string);
    if ($echo) {
        echo $string;
    } else {
        return $string;
    }
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