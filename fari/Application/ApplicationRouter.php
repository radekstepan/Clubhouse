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
 * Router loads up the appropriate Presenter and Action based on the route in the incoming URL.
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\Application
 */
final class Fari_ApplicationRouter {

	/** @var string with Fari_ApplicationPresenters */
	private static $presenterDir = '';

	private function __construct() { }
	private final function __clone() { }

	/**
	 * Load the Presenter and Action from route requested.
	 * @return void
	 */
	public static function loadRoute() {
        // first make sure we have the Presenter directory right
		$presenterDir = BASEPATH . '/' . APP_DIR . '/presenters/';
		try {
			// throw an error if is not a directory
			if (!is_dir($presenterDir)) {
				throw new Fari_Exception('Not a Presenter directory: ' . $presenterDir . '.');
			// else set the path
			} else self::$presenterDir = $presenterDir;
		} catch (Fari_Exception $exception) { $exception->fire(); }
		
		// get route as passed by .htaccess
        $route = new Fari_ApplicationRoute();
		
		// set file requested
		$presenterFile = self::$presenterDir . $route->presenter . EXT;
		
		// now check if the Presenter file is readable...
		if (!is_readable($presenterFile)) {
            // ... not, so update Presenter path to the error page 404
            $route->setErrorRoute();
            // update the path to file
			$presenterFile = self::$presenterDir . $route->presenter . EXT;
			
			// throw an exception if the error Presenter doesn't exist either
			try { if (!is_readable($presenterFile)) {
				throw new Fari_Exception('Missing 404 Error Presenter: ' . $presenterFile); }
			} catch (Fari_Exception $exception) { $exception->fire(); }
		}
		// now we know the Presenter path exists so include
        include($presenterFile);
        
		// from variable to instance of an object
        $class = ucwords($route->presenter);
		$presenter = @new $class(&$route);
		
		// check that the Presenter object extends Fari_ApplicationPresenter
		try { if (!$presenter instanceof Fari_ApplicationPresenter) {
			throw new Fari_Exception('Presenter object ' . $route->presenter .
						 ' does not extend Fari_ApplicationPresenter.'); }
		} catch (Fari_Exception $exception) { $exception->fire(); }
		
		// Presenter is set, now check we can call the Action in it with the appropriate prefix
		if (!is_callable(array($presenter, $route->action))) {
            $route->setDefaultAction();
		} else {
            // we have implemented method overloading in the presenter so make a little checkup first
            try {
                // reflect the action we are calling
                $method = new Fari_ApplicationReflection($route->presenter, $route->action);
            } catch (ReflectionException $e) {
                // set default
                $route->setDefaultAction();
            }
        }

        // reflect the action we are calling
        // save us some trouble if we've instantiated already
        if (!$method instanceof Fari_ApplicationReflection) {
            $method = new Fari_ApplicationReflection($route->presenter, $route->action);
        }
        // ...and optionally pass variable number of parametres
        if ($method->hasParameters()) $method->setParameters(&$route->parameters);
        // call the action (method) in the Presenter
        $method->call(&$presenter);
 	}
	
}