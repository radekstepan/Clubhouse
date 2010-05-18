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
final class Fari_ApplicationView {

    /**#@+ filename suffix */
	const VIEW_SUFFIX = '.phtml';
    /**#@-*/

    /** @var values from Fari_Bag to extract */
    private $values = NULL;

    /**
     * The constructor extracts the bag of values passed from the Presenter.
     * @param A bag of key:value symbols
     */
    public function __construct(array $values) {
        $this->values = $values;

        // include view helpers
        include BASEPATH . "/fari/Application/ApplicationViewHelpers" . EXT;
        // set the values in the helpers so they 'catch' them
        Fari_ApplicationViewHelper::setValues($this->values);
    }

 	/**
     * Displays the view after (optionally) render function in a presenter has been executed.
	 * @param string $viewName View name to display
	 * @param string $contentType Specifies optional content type for the view
	 * @param string $extraParam Extra parameter to find the view by
 	 */
 	public function render($viewName) {
        assert('strlen($viewName) > 0; // view name cannot be empty');

        // form views path
        $path = BASEPATH . '/' . APP_DIR . '/views/';
        // create template file path
        $viewFile = $path . $viewName . self::VIEW_SUFFIX;
        
        // check if view exists
        $this->isViewValid($viewFile);

        // are we using a @layout file?
        $temp = explode('/', $viewName);
        assert('count($temp) == 2; // $viewName needs to consist of "presenter/file"');

        // define presenter name for easy access
        Fari_ApplicationViewHelper::setPresenter($temp[0]);

        // custom layout named after our presenter
        if (file_exists($layout = $path . '@' . strtolower($temp[0]) . self::VIEW_SUFFIX)) {
            $this->includeLayoutAndView($layout, $viewFile);
        // application level layout
        } else if (file_exists($layout = $path . '@application' . self::VIEW_SUFFIX)) {
            $this->includeLayoutAndView($layout, $viewFile);
        } else {
            // import key:value array into symbol table
            extract($this->values, EXTR_SKIP);
            
            // no soup for you!
            $devMode = Fari_ApplicationEnvironment::isDevelopment();
            if ($devMode) echo "\n<!-- begin {$viewFile} -->\n";
            include $viewFile;
            if ($devMode) echo "\n<!-- end {$viewFile} -->\n";
        }
	}
	
	/**
	 * Check if view file can be included. Will throw Fari_Exception on error.
	 * @param string $viewFile to check for existence
     * @throws Fari_Exception
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

    /**
     * Include layout file and view file included in $template var.
     * @param string $layout file path
     * @param string $view file path
     */
    private function includeLayoutAndView($layout, $view) {
        // we are defining our own var $template so check if exists
        assert('!array_key_exists(\'template\', $this->values);
            // $template variable will be overwritten, do not use it');

        // import key:value array into symbol table
        extract($this->values, EXTR_SKIP);

        // display paths to files in development mode
        $devMode = Fari_ApplicationEnvironment::isDevelopment();

        ob_start();
        // save view into template var
        if ($devMode) echo "\n<!-- begin {$view} -->\n";
        include $view;
        if ($devMode) echo "\n<!-- end {$view} -->\n";
        $template = ob_get_contents();
        ob_end_clean();

        // call parent layout
        if ($devMode) echo "<!-- begin {$layout} -->\n";
        include $layout;
        if ($devMode) echo "\n<!-- end {$layout} -->\n";
    }
	
}