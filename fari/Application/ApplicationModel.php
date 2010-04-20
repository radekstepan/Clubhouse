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
 * Application model creates a database connection.
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\Application
 */
class Fari_ApplicationModel {

    /** @var declare public to have easy access to the connection from subclasses */
    public $db;

    /**
     * Get instance of database connection.
     */
    public function __construct() {
        $this->db = Fari_Db::getConnection();
    }
	
}