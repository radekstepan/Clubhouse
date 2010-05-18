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

    /** @var Fari_ApplicationPresenterHelpers */
    private $helpers;

    /**
     * Makes for nicer method calls.
     * @param string $method
     * @param mixed $params
     * @throws Fari_Exception
     */
    public function __call($method, $params) {
        try {
            // determine the method called
            if (!preg_match('/^(render|send)(\w+)$/', $method, $matches)) {
                throw new Fari_Exception("Call to undefined method {$method}");
            }

            // what do you want?
            switch ($matches[1]) {
                // render something with a passed type
                case 'send':
                case 'render':
                    // pass on var number of params
                    if (count($params) > 1) {
                        $this->render($params, $matches[2]);
                    } else {
                        $this->render(current($params), $matches[2]);
                    }
                    break;
                default:
                    throw new Fari_Exception("Call to undefined parameter {$matches[1]}");
            }

        } catch (Fari_Exception $exception) { $exception->fire(); }
    }

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

        // setup helpers and potentionally save some values to the newly initialized bag of values
        $this->helpers = new Fari_ApplicationPresenterHelpers(&$this->bag);

        // startup filter is always applied first if defined
        if (method_exists($this->request->getPresenter(), filterStartup)) $this->filterStartup();
        // load filters before actions themselves are executed
        $this->loadFilters('beforeFilter');
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

        // load filters as all actions have been executed already
        $this->loadFilters('afterFilter');
    }

    /**
     * Tries to call your custom render action and then displays the view template.
     * @param string $viewName
     * @param mixed $parametres Parameters to pass to the function
     */
    function renderAction($viewName=NULL, $parameters=NULL) {
        // check we are calling the proper method
        try {
            if (!is_null($viewName) && strpos($viewName, '/') !== FALSE) {
                throw new Fari_Exception('Use renderTemplate if you want to call a custom template.');
            }
        } catch (Fari_Exception $exception) { $exception->fire(); }

        // define view presenter prefix and action/template name
        list ($presenter, $action) = $this->getPresenterAction($viewName);
        
        // do we have a render function to call?
        // It is always called in our presenter!
        if (method_exists($this->request->getPresenter(), 'render' . $action)) {
            // reflect on the method
            $method = new Fari_ApplicationReflection($this, 'render' . ucfirst($action));
            
            // set parameters
            if ($method->hasParameters()) $method->setParameters(&$parameters);
            // call the render method
            $method->call($this);
        }

        // strip prefix Presenter suffix :)
        if (($suffixStart = strpos($presenter, 'Presenter')) !== FALSE) {
            $presenter = ucfirst(substr($action, 0, $suffixStart + 1));
        }

        // render file through a view
        $this->response->renderView("{$presenter}/{$action}", &$this->bag->values);

        // we don't want any further code to be executed in the Presenter
        die();
    }

    /**
     * Renders view template.
     * @param string $viewName
     * @param mixed $parametres Parameters to pass to the function
     */
    function renderTemplate($viewName=NULL, $parameters=NULL) {
        // check we are calling the proper method
        try {
            if (is_null($viewName) || strpos($viewName, '/') != TRUE) {
                throw new Fari_Exception('Use renderAction if you want to call an action.');
            }
        } catch (Fari_Exception $exception) { $exception->fire(); }

        // define view presenter prefix and action/template name
        list ($presenter, $action) = $this->getPresenterAction($viewName);

        // strip prefix Presenter suffix :)
        if (($suffixStart = strpos($presenter, 'Presenter')) !== FALSE) {
            $presenter = substr($action, 0, $suffixStart);
        }

        // render file through a view
        $this->response->renderView("{$presenter}/{$action}", &$this->bag->values);

        // we don't want any further code to be executed in the Presenter
        die();
    }

    /**
     * Catch requests to render data and pass on to response class.
     * @param mixed $data
     */
    private function render($data, $format) {
        $this->response->response($data, strtolower($format));
        die();
    }

    /**
	 * Will redirect to a URL (/presenter/action).
	 * @param string $url URL to redirect to
	 */
	function redirectTo($url) {
        assert('strpos($url, "/") !== FALSE; // malformed URL ' . "'{$url}'");
        
        $this->response->redirectTo($url);
    }



    /********************* helpers *********************/



    /**
     * Get prefix prefix and action/template name from view name.
     * @param string $viewName
     * @return array
     */
    private function getPresenterAction($viewName=NULL) {
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
            $presenter = $this->request->getPresenter();
            $prefix = substr($presenter, 0, stripos($presenter, "Presenter"));
        }

        // form an array pair
        return array($prefix, $viewName);
    }



    /********************* presenter helpers *********************/



    /**
     * Capture requests from the presenter and redirect them to the helper.
     * @param string $method key
     * @param mixed $params
     */
    public function __set($method, $params) {
        // now do magic, parse in helpers...
        $this->helpers->$method = $params;
    }



    /********************* presenter filters *********************/



    /**
     * Will try to load filters.
     * @param string $filtersVar name of variable storing filters.
     */
    private function loadFilters($filtersVar) {
        assert('is_string($filtersVar); // pass variable as a string');
        // now for custom filters... these will throw exception if not found
        // do we have them at all?
        if (isset($this->$filtersVar)) {
            foreach ($this->$filtersVar as $filter) {
                // check we are properly formatted
                $filterName = current(array_keys($filter));
                $applyBefore = $filter[$filterName];
                
                // precondition
                assert('!empty($filterName) || !is_array($applyBefore); // malformed filter format, use
                    "array(\'filterName\' => array(\'new\'))"');

                // determine action called by trimming off "action" prefix
                $actionCalled = strtolower(substr($this->request->getAction(), 6));

                // are we applying the filter?
                if (in_array($actionCalled, $applyBefore)) {
                    // oh yeah
                    $this->applyFilter('filter' . ucfirst($filterName));
                }
            }
        }
    }

    /**
     * Call filter in our presenter.
     * @param string $filterMethod
     */
    private function applyFilter($filterMethod) {
        try {
            if (method_exists($this->request->getPresenter(), $filterMethod)) {
                // call the method
                $this->$filterMethod();
            } else {
                // else... exception land baby
                throw new Fari_Exception("Filter method '{$filterMethod}' not found.");
            }
        } catch (Fari_Exception $exception) { $exception->fire(); }
    }

}