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
 * Determines the environment we run under.
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\Application
 */
class Fari_ApplicationEnvironment {

    /**
     * Detect environment type.
     * @param string $type  development/production/cli
     * @return boolean if type recognized
     */
    public static function detect($type) {
        // constant defining application environment
        if (defined('APP_ENVIRONMENT')) {
                return (APP_ENVIRONMENT == '$type');
        }

        // determine if we run on localhost automagically
        $address = explode('.', isset($_SERVER['SERVER_ADDR']) ?
            $_SERVER['SERVER_ADDR'] : $_SERVER['LOCAL_ADDR']);
        assert('!empty($address); // could not determine environment type');

        switch ($type) {
            case "development":
                // 127.x.x.x
                // 192.x.x.x
                return ($address[0] == '127' || $address[0] == '192');
                break;
            case "production":
                return ($address[0] != '127' && $address[0] != '192');
                break;
            case "cli":
                return (PHP_SAPI === 'cli');
                break;
            default:
                return NULL;
        }
    }

    /**
     * Is development environment?
     * @return true if we run in a development environment
     */
    public static function isDevelopment() {
        return self::detect('development');
    }

    /**
     * Is production environment?
     * @return true if we run in a production environment
     */
    public static function isProduction() {
        return self::detect('production');
    }

    /**
     * Environment startup checks.
     */
    public static function startupCheck() {
        // check that we have a high enough version of PHP (5.2.0)
        try {
            if (version_compare(phpversion(), '5.2.0', '<=') == TRUE) {
                throw new Fari_Exception('Fari Framework requires PHP 5.2.0, you are using ' . phpversion() . '.');
            }
        } catch (Fari_Exception $exception) { $exception->fire(); }

        // check if user is using Apache user-directory found on temporary links to web hosting (e.g., http://site.com/~user/)
        try {
            if (substr_count(WWW_DIR, '~') > 0) {
                throw new Fari_Exception('Apache user-directory ' . WWW_DIR . ' not supported by Fari Framework.');
            }
        } catch (Fari_Exception $exception) { $exception->fire(); }
    }
	
}