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
 * Request environment with POST, GET, FILE values.
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\Application
 */
final class Fari_ApplicationRequest {

    /** @var Fari_ApplicationRoute */
    private $route;

    /** @var Fari_Bag of GET queries */
    private $query;

    /** @var Fari_Bag of POST values */
    private $post;

    /** @var Fari_Bag of FILES objects */
    private $files;

	/**
     * Setup the routed request environment.
     * @param Fari_ApplicationRoute setup in the Request
	 */
    public function __construct(Fari_ApplicationRoute $route) {
        // set the route object
        $this->route = $route;

        // GET queries
        $this->query = new Fari_Bag();
        $this->query->set($_GET);
        
        // have we posted vars?
        if ($_POST) {
            // initialize a bag of values
            $this->post = new Fari_Bag();
            // POST values
            $this->post->set($_POST);
        }

        // have we uploaded files?
        if ($_FILES) {
            // initialize a bag of values
            $this->files = new Fari_Bag();
            // FILES objects
            $this->files->set($_FILES);
        }
    }



    /********************* get *********************/


	/**
     * Get GET query with route set as default.
     * @return string
	 */
    function getQuery($key='route') {
        return $this->query->$key;
    }



    /********************* route *********************/



	/**
     * Presenter defined.
     * @return string
	 */
    function getPresenter() {
        return $this->route->presenter;
    }

	/**
     * Action defined.
     * @return string
	 */
    function getAction() {
        return $this->route->action;
    }

	/**
     * Route string.
     * @return array
	 */
    function getRoute() {
        return $this->route->route;
    }

   

    /********************* files *********************/



	/**
     * Uploaded file.
     * @return array
	 */
    function getFile($key='upload') {
        return $this->files->$key;
    }

	/**
     * File name.
     * @return string
	 */
    function getFileName($key='upload') {
        return $this->files->$key['name'];
    }

	/**
     * MIME file type.
     * @return string
	 */
    function getFileMime($key='upload') {
        return $this->files->$key['type'];
    }

    /**
     * Open file into a stream (read binary default).
     * @return stream
	 */
    function openFile($key='upload', $mode='rb') {
        return fopen($this->files->$key['tmp_name'], $mode);
    }

	/**
     * Have we uploaded a file?
     * @return boolean
	 */
    function isUpload() {
        return ($this->files) ? TRUE : FALSE;
    }



    /********************* post *********************/



    /**
     * Have we POSTed?
     * @return boolean TRUE returned if values posted
     */
    function isPost() {
        return ($this->post) ? TRUE : FALSE;
    }

    /**
     * Get POSTed value(s), filtered.
     * @param string $key Key under which values are saved under, otherwise get all (optional)
     * @param string $filter Fari_Escape applied on getting the value (optional)
     * @return mixed Values in $_POST variable
     */
    function getPost($key=NULL, $filter='text') {
        // can we apply the filter passed?
        try {
            if (!method_exists('Fari_Escape', $filter)) {
                // ... throw exception if filter function is invalid
                throw new Fari_Exception('Fari_Escape::' . $filter . ' is not a valid escaping function.');
            }
        } catch (Fari_Exception $exception) { $exception->fire(); }

        // return the value(s), filtered
        if (isset($key)) {
            return ($this->isAjax()) ?
                // decode first in JavaScript context
                Fari_Escape::$filter(Fari_Decode::javascript($this->post->$key)) :
                Fari_Escape::$filter($this->post->$key);
        } else {
            // get the values
            $post = $this->post->values;

            // decode from AJAX?
            if ($this->isAjax()) $post = Fari_Decode::javascript($post);

            // filter them
            foreach ($post as $key => &$value) $value = Fari_Escape::$filter($value);

            return $post;
        }
    }

    /**
     * Get POSTed values, unfiltered.
     * @param string $key Key under which values are saved under
     */
    function getRawPost($key) {
        return $this->post->$key;
    }



    /********************* environment *********************/



	/**
     * Get remote host.
     * @return string
	 */
    function getHost() {
        $host = $_SERVER['HTTP_HOST'];
        if (substr($host, -1) == '/') {
            $host = substr($host, 0, strlen($host)-1);
        }

        assert('strlen($host) > 0; // expected to resolve HTTP_HOST');

        return $host;
    }

	/**
     * Get requested method.
     * @return string
	 */
    function getMethod() {
        return isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : NULL;
    }

	/**
     * Get headers.
     * @return string
	 */
    function getHeaders() {
        return array_change_key_case(apache_request_headers(), CASE_LOWER);
    }

	/**
     * Get user agent making the request.
     * @return string
	 */
    function getAgent() {
        $headers = $this->getHeaders();
        return $headers['user-agent'];
    }

	/**
	 * Check if the request has been done asynchronously.
	 * @return boolean True if Ajax request
	 */
	function isAjax() {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
    }

	/**
	 * Is this a secure request via HTTPS?
	 * @return boolean True if HTTPS
	 */
    function isSecured() {
		return isset($_SERVER['HTTPS']) && strcasecmp($_SERVER['HTTPS'], 'off');
	}
	
}