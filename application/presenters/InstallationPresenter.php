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
 * Starts the installation if database not present.
 * Access: public, for installation only
 *
 * @copyright Copyright (c) 2010 Radek Stepan
 * @package   Clubhouse\Presenters
 */
final class InstallationPresenter extends Fari_ApplicationPresenter {

    /**
     * Applied automatically before any action is called.
     */
    public function filterStartup() {
        if (Fari_DbSqLite::isDbWritable()) $this->response->redirectTo('/error404/');
    }

    public function actionIndex($p) {
        new Installation();
        $this->response->redirectTo('/');
    }

}