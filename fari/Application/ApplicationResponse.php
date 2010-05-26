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
 * Response caching or displaying a view, rendering in JSON format etc.
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\Application
 */
final class Fari_ApplicationResponse {

    /** @var default response code */
    private $responseCode = '200';

    /** @var Fari_ApplicationViewCache */
    private $cache;



    /********************* html context *********************/



    /**
     * We want to use a view to render our data?
     * @param string $viewName
     * @param mixed $values Parameters to pass to the function
     */
    function renderView($viewName, $values) {
        $this->setContentType('text/html');

        // create new view
        $view = new Fari_ApplicationView($values);
        // render data
        $view->render($viewName);
    }

    /**
     * Render cached file if present.
     * @param string $viewName
     * @param mixed $parametres Parameters to pass to the function
     */
    function renderCache($viewName, $parameters) {
        $this->setContentType('text/html');

        // instantiate cache
        if (!$this->cache instanceof Fari_ApplicationViewCache) $this->cache = new Fari_ApplicationViewCache();
        $this->cache->renderCache($viewName, $parameters);
    }

    /**
     * Save data into a cache file and dump it.
     * @param array $values to extract in the view
     */
    function saveCache($values) {
        $this->setContentType('text/html');
        $this->cache->saveCache($values);
    }



    /********************* response function *********************/



    /**
     * Response to the browser.
     * @param mixed $data to output
     * @param string $type text, download, json, xml
     */
    function response($data, $type) {
        try {
            // determine type of response
            switch ($type) {
                // plain text
                case 'text':
                    $this->setContentType('text/html');
                    assert('is_string($data); // you can only display a string');
                    echo $data;
                    break;

                // file download
                case 'download':
                case 'file':
                    $this->setHeader('Cache-Control', 'public');
                    $this->setHeader('Content-Description', 'File Transfer');

                    if (!isset($data['filename'])) $data['filename'] = 'file';
                    $this->setHeader('Content-Disposition', 'attachment; filename=' . $data['filename']);
                    $this->setContentType($data['mime']);

                    // echo file data
                    echo $data['data'];
                    break;

                // JSON response
                case 'json':
                    $this->setContentType('application/json');
                    // encode values
                    echo json_encode($data);
                    break;

                // XML response
                case 'xml':
                    $this->setContentType('text/xml');
                    // echo values
                    echo $data;
                    break;

                default:
                    throw new Fari_Exception('This response type if undefined.');
                    break;
            }
        } catch (Fari_Exception $exception) { $exception->fire(); }
    }



    /********************* header & content type *********************/



    /**
     * Set response header.
     * @param string $name
     * @param string $value if not defined will clear the header
     */
    function setHeader($name, $value=NULL) {
        if (!headers_sent()) {
            // can we remove header?
            if ($value === NULL && function_exists('header_remove')) {
                header_remove($name);
            // set it with the default response code
            } else {
                header($name . ': ' . $value, TRUE, $this->responseCode);
            }
        }
    }



    /**
     * Set content type.
     * @param string
     */
    function setContentType($type) {
        $this->setHeader('Content-Type', $type);
    }



    /********************* redirect response *********************/



    /**
	 * Will redirect to a URL (/presenter/action). Works with both synchronous/asynchronous calls.
	 * @param string $url URL to redirect to
	 */
	function redirectTo($url) {
		// add forward slash if not specified
		if ($url[0] !== '/') $url = "/{$url}";

		// is this an AJAX call?
		if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
			// implement a browser-end redirect here
		} else {
			// if headers haven't been already sent provide redirect via header()
			if (!headers_sent()) header('Location: ' . WWW_DIR . $url);
		}

        // die to 'protect' the presenter calling us
        die();
	}

}