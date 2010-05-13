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
 * Presenter has access to Request and decides on the Response taken.
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\Application
 */
abstract class Fari_ApplicationPresenter {

	/** @var Fari_Bag of values to display in a view */
	protected $bag;

    /** @var Fari_ApplicationRequest */
    public $request;

    /** @var Fari_ApplicationResponse */
    public $response;

    /** @var enabled through renderCache() */
    private $cache = FALSE;

	/**
     * Set registry when new object gets instantiated and use classname when filterStartup().
     * @param Fari_ApplicationRoute setup in the Request
	 */
	function __construct(Fari_ApplicationRoute $route) {
        // construct the Request & Response
        $this->request = new Fari_ApplicationRequest($route);
        $this->response = new Fari_ApplicationResponse();

        // initialize a bag of values for our use
        $this->bag = new Fari_Bag();

        // initialization function called before anything else if is defined
		if (method_exists($this->request->getPresenter(), filterStartup)) $this->filterStartup();
	}

	/**
	 * Abstract function that every child-class is required to have. Interface could be used as well.
	 * @param string Takes in a maximum of one parameter,
	 */
	abstract function actionIndex($parameter);



    /********************* render action using Fari_ApplicationView *********************/



	/**
	 * Render cache file if it exists.
	 * @param $viewName Prefix/viewName tuple
     * @param $parameters Optionally set if a view has variable data in it.
	 */
    function renderCache($viewName, $parameters=NULL) {
        $view = implode('/', $this->getViewPrefix($viewName));

        // check if we can render the cached file, if not game as usual
        $this->cache = $this->response->renderCache($view, $parameters);

        // we obviously haven't died so we will be caching a file
        $this->cache = TRUE;
    }

	/**
	 * If caching is enabled save view to a cache and render it.
	 */
    function __destruct() {
        if ($this->cache === TRUE) {
            // save cached data into a file &dump
            $this->response->saveCache(&$this->bag->values);
        }
    }

    /**
     * We want to use a view to render our data? Calls a render method if defined.
     * @param string $viewName
     * @param mixed $parametres Parameters to pass to the function
     */
    function render($viewName=NULL, $parameters=NULL) {
        // define view prefix and name
        $view = $this->getViewPrefix($viewName);

        // do we have a render function to call?
        // It is always called in our presenter!
        if (method_exists($this->request->getPresenter(), 'render' . $view['name'])) {
            // add the 'render' prefix to the view name
            $renderAction = 'render' . ucwords($view['name']);

            // reflect on the method
            $method = new Fari_ApplicationReflection(
                $this->request->getPresenter(), 'render' . $view['name']
            );

            // set parameters
            if ($method->hasParameters()) $method->setParameters(&$parameters);
            // call the render method
            $method->call($this);
        }

        // strip prefix Presenter suffix :)
        if (($suffixStart = stripos($view['prefix'], 'Presenter')) !== FALSE) {
            $view['prefix'] = substr($view['prefix'], 0, $suffixStart);
        }

        // render file through a view
        $this->response->renderView(implode('/', $view), &$this->bag->values);

        // we don't want any further code to be executed in the Presenter
        die();
    }



    /********************* response function *********************/



    /**
     * Response to the browser.
     * @param mixed $values Values to output
     * @param string $type HTML, JSON, download
     * @param mixed $parametres Extra parametres
     */
    function response($values, $type='html', $parameters=NULL) {
        // call the Response
        $this->response->response($values, strtolower($type), $parameters);

        // we don't want any further code to be executed in the Presenter
        die();
    }



    /********************* helpers *********************/



    /**
     * Get prefix and name from view name.
     * @param string $viewName
     * @return array
     */
    private function getViewPrefix($viewName=NULL) {
        // if view is not specified get the action that was called...
        if (!isset($viewName)) {
            // ... and strip action prefix, then lowercase
            $viewName = strtolower(substr($this->request->getAction(), 6));
        }

        // get the prefix of the view if present
        if (($slash = stripos($viewName, '/')) !== FALSE) {
            // the prefix Presenter named directory where our view resides
            $prefix = ucwords(substr($viewName, 0, $slash));
            // the actual name of the view we have requested
            $viewName = substr($viewName, $slash + 1);
        } else {
            // set default prefix as this presenter
            $prefix = $this->request->getPresenter();
        }

        // form an array pair
        return array('prefix' => $prefix, 'name' => $viewName);
    }

}