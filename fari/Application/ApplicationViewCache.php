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
 * Caching a view to a file and fetching it back again.
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\Application
 */
class Fari_ApplicationViewCache {
	
    /**#@+ cache files directory & extension */
	const CACHE_DIR = '/tmp/';
    const CACHE_EXT = '.html';
    /**#@-*/

    /** @var cache expiry in minutes */
	private $expiry = 5;

    /** @var fileId used when caching a file */
    private $fileId;

    /** @var view name to cache */
    private $viewName;

    /**
     * We want to use a view to render our data?
     * @param string $viewName
     * @param mixed $parameters Parameters to pass to the function that identify the view
     */
    public function renderCache($viewName, $parameters=NULL) {
        assert('strlen($viewName) > 0; // view name cannot be empty');

        $this->viewName = $viewName;
        // build file id from (folder(s) and) file name
        $this->fileId = $this->getFileId($viewName, $parameters);

        // check if dir writable
        $this->isDirWritable(self::CACHE_DIR);

        // everything went OK, 'build' cache filename path
        $cacheFile = BASEPATH . self::CACHE_DIR . $this->fileId . self::CACHE_EXT;

        // serve the cached file if it exists and is not too old
		if (file_exists($cacheFile) && !$this->isCacheOld($cacheFile)) {
            readfile($cacheFile);
            die();
        }
    }

    /**
     * Save a view into a cache and dump it
     * @param array values to extract into the file
     */
 	public function saveCache(array $values) {
        // we need to have the fileId and name set!
        try { if (!isset($this->fileId)) {
            throw new Fari_Exception('Fari requires you to call renderCache() from a Presenter first.'); }
        } catch (Fari_Exception $exception) { $exception->fire(); }

        // everything went OK, 'build' cache filename path
        $cacheFile = BASEPATH . self::CACHE_DIR . $this->fileId . self::CACHE_EXT;

        // import key:value array into symbol table
        extract($values, EXTR_SKIP);

        // start the output buffer, compress if possible
        ob_start();

        // create template file path
        $viewFile = BASEPATH . '/' . APP_DIR . '/views/' . $this->viewName . '.tpl' . EXT;
        // check if view exists
        $this->isViewValid($viewFile);
        // all went fine, include
        include $viewFile;

        // get view/template contents to a variable
        $contentOutput = ob_get_contents();

        // write into cache file
        $this->writeCache($contentOutput, $cacheFile);

        // send the output to the browser
        ob_end_flush();
	}

	/**
	 * Build file id as file might be in a sub directory etc.
	 * @param string $viewName View name that we are working with
	 * @param string $extraParam Extra parameter to find the view by
	 * @return string MD5 hashed view file id
	 */
	private function getFileId($viewName, $extraParam) {
		// calculate based on md5
		if (isset($extraParam)) return md5($viewName.$extraParam);
		return md5($viewName);
	}
	
	/**
 	 * Write contents of a view/template to a file.
 	 * @param string $contentOutput Content of the view we want to write
 	 * @param string $cacheFile Filename we will write to
 	 */
 	private function writeCache($contentOutput, $cacheFile) {
		// open file for writing
		$cacheFile = fopen($cacheFile, 'w');
		// write file
		fwrite($cacheFile, $contentOutput);
		// close file
		fclose($cacheFile);
	}
	
	/**
	 * Will check if a cache directory is writable. Will throw Fari_Exception on error.
	 * @param string $cacheDir Directory that stores our cached files
	 * @return void
	 */
	private function isDirWritable($cacheDir) {
		try {
			if (!is_writable(BASEPATH . $cacheDir)) {
				throw new Fari_Exception('Cache directory ' . $cacheDir . ' is not writable.');
			}
		} catch (Fari_Exception $exception) { $exception->fire(); }
	}
	
	/**
	 * Check if view file can be included. Will throw Fari_Exception on error.
	 * @param string $viewFile to check for existence
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
	 * Determines whether cache file is too old to be used.
	 * @param string $cacheFile Filename to check for 'freshness'
	 * @return boolean Whether we need to get a fresh copy (TRUE) or not (FALSE)
	 */
	private function isCacheOld($cacheFile) {
		// time now - cache lifetime
		$goodTime = time() - ($this->expiry * 60);
		$cacheAge = filemtime($cacheFile);
		// return TRUE if file age 'within' good time
		return ($goodTime < $cacheAge) ? FALSE : TRUE;
	}
	
}