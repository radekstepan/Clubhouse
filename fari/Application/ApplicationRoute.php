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
 * Route contains a parsed request defined in terms of Presenter, Action and optional parameters.
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\Application
 */
class Fari_ApplicationRoute extends Fari_Bag {
	
    /**#@+ $_GET variable containing route (coming from .htaccess) */
	const ROUTE = 'route';
    /**#@-*/

    /**#@+ default action and error presenter */
    const DEFAULT_ACTION = 'actionIndex';
	const ERROR_PRESENTER = 'Error404';
    /**#@-*/

	/**
	 * Parses the request and forms route components.
	 */
    public function __construct() {
        $request = @$_GET[self::ROUTE];
        if (!empty($request)) $request = $this->checkRoutes($request);
        $this->route = $this->cleanup(explode('/', $request));

        if (empty($this->route[0])) {
            $this->setDefaultPresenter();
            $this->setDefaultAction();
        } else if (empty($this->route[1])) {
            $this->presenter = ucwords($this->route[0]) . 'Presenter';
            $this->setDefaultAction();
        } else {
            $this->presenter = ucwords($this->route[0]) . 'Presenter';
            $this->action = 'action' . ucwords($this->route[1]);
            
            $this->parameters = array_slice($this->route, 2);
        }
    }

	/**
	 * Default presenter set for the application.
	 */
    public function setDefaultPresenter() {
        $this->presenter = DEFAULT_PRESENTER . 'Presenter';
    }

	/**
	 * Default action (actionIndex).
	 */
    public function setDefaultAction() {
        $this->action = self::DEFAULT_ACTION;
    }

	/**
	 * Error Presenter & action setting.
	 */
    public function setErrorRoute() {
        $this->presenter = self::ERROR_PRESENTER . 'Presenter';
        $this->setDefaultAction();
    }

    /**
	 * Check if we have the routes config file and if so include it and check for routes matches on the request.
	 * @param string $request Original unsplit to check for match in custom routes
	 * @return string Optionally updated route with a custom one
	 */
	private function checkRoutes($request) {
		// form the path to the routes file
		$routesFile = BASEPATH . '/config/routes' . EXT;
		// do we have the routes file?
		if (is_readable($routesFile)) {
			// include, now we have the $customRoutes array
			include($routesFile);
			// if we have $customRoutes to traverse
			if (is_array($customRoutes)) {
                // traverse routes
                foreach ($customRoutes as $pattern => $result) {
                    // we have a match
                    if (preg_match($pattern, $request)) {
                        // 'change' the request by replacing the pattern match
                        $request = preg_replace($pattern, $result, $request);
                        // don't bother with more routes, first come first served basis
						break;
                    }
                }
            }
		}
		return $request;
	}

    /**
     * Sanitize input route array.
     * @param array $route
     * @return array
     */
    private function cleanup(array $route) {
		// cleanup URL to only include the following:
		foreach ($route as &$part) {
			// Fari_Filter::url()
			$part = filter_var($part, FILTER_SANITIZE_ENCODED);
		}
		return $route;
	}
    
}