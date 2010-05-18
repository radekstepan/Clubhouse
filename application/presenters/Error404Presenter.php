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
 * Errors.
 * Access: public
 *
 * @copyright Copyright (c) 2010 Radek Stepan
 * @package   Clubhouse\Presenters
 */
final class Error404Presenter extends Fari_ApplicationPresenter {

    /**
	 * Page has not been found
	 */
	public function actionIndex($p) {
        $this->render('error404');
	}

    /**
	 * Asynchronous request not called from JavaScript
	 */
	public function actionAjax() {
        $this->render('javascript');
	}

}
