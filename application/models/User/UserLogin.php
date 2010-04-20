<?php if (!defined('FARI')) die();

/**
 * Clubhouse, a 37Signals' Campfire port
 *
 * @copyright Copyright (c) 2010 Radek Stepan
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link      http://radekstepan.com
 * @category  Clubhouse
 */



/**
 * User authentication.
 *
 * @copyright Copyright (c) 2010 Radek Stepan
 * @package   Clubhouse\Models\User
 */
class UserLogin {

    function __construct($username, $password, $token=NULL) {
        $authenticator = new Fari_AuthenticatorSimple();
        // authenticator authenticates...
        if ($authenticator->authenticate($username, $password, $token)) {
            throw new UserNotAuthenticatedException();
        }

        // return the sweet beans
        return new User();
    }

}