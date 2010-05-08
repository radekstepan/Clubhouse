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
 * View class contains presentation logic (templating).
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\Application
 */
class Fari_ApplicationView {

    /** @var values from Fari_Bag to extract */
    private $values = NULL;

    /**
     * The constructor extracts the bag of values passed from the Presenter.
     * @param A bag of key:value symbols
     */
    public function __construct(array $values) {
        $this->values = $values;
    }
	
 	/**
     * Displays the view after (optionally) render function in a presenter has been executed.
	 * @param string $viewName View name to display
	 * @param string $contentType Specifies optional content type for the view
	 * @param string $extraParam Extra parameter to find the view by
 	 */
 	public function render($viewName) {
        assert('strlen($viewName) > 0; // view name cannot be empty');

        // import key:value array into symbol table
        extract($this->values, EXTR_SKIP);
        
        // create template file path
        $viewFile = BASEPATH . '/' . APP_DIR . '/views/' . $viewName . '.tpl' . EXT;
        
        // check if view exists
        $this->isViewValid($viewFile);
        // all went fine, include
        include $viewFile;
	}
	
	/**
	 * Check if view file can be included. Will throw Fari_Exception on error.
	 * @param string $viewFile to check for existence
	 * @return void
	 */
	private function isViewValid($viewFile) {
		try {
			// check if file path exists
			if (!file_exists($viewFile)) {
				throw new Fari_Exception('View not located in: ' . $viewFile);
			}
		} catch (Fari_Exception $exception) { $exception->fire(); }
	}
	
}