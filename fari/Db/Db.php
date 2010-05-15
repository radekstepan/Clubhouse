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
 * Creates an instance of PDO connection.
 *
 * @deprecated
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\Db
 */
class Fari_Db {

    /** @var Fari_DbConnection PDO wrapper */
	private static $dbConnection;

	private function __construct() { }
	private final function __clone() { }

	/**
	 * Connect to the database or return connection instance.
	 * @return PDO Instance of PDO connection
	 */
	public static function getConnection() {
		// do we have an instance already?
		if (!self::$dbConnection instanceof Fari_DbConnection) {
            try {
                // which driver are we using?
				switch (strtolower(DB_DRIVER)) {
					// MySQL
					case 'mysql':
						$pdoInstance = new PDO('mysql:host=' .
									  DB_HOST . ';dbname=' .
									  DB_NAME .
									  ';unix_socket=/var/run/mysqld/mysqld.sock',
									  DB_USER, DB_PASS);
						break;

                    // PostgreSQL (untested)
					case 'pgsql':
						$pdoInstance = new PDO('pgsql:dbname=' . DB_NAME . ';host=' .
									  DB_HOST, DB_USER, DB_PASS);
						break;

                    // SQLite 3
                    case 'sqlite3':
                    case 'sqlite':
						$pdoInstance = new PDO('sqlite:' . BASEPATH . '/' . DB_NAME);
						break;

                    // SQLite 2
					case 'sqlite2':
						$pdoInstance = new PDO('sqlite2:' . BASEPATH . '/' . DB_NAME);
						break;
				}
                
				// error mode on, throw exceptions
				$pdoInstance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // create Fari_DbConnection using the PDO instance
                self::$dbConnection = new Fari_DbConnection($pdoInstance);
                
            } catch (PDOException $exception) {
                try {
                    throw new Fari_Exception('Cannot connect to DB: ' . $exception->getMessage() . '.');
                } catch (Fari_Exception $exception) { $exception->fire(); }
			}
		}

        // return Fari_DbConnection
        return self::$dbConnection;
	}

}