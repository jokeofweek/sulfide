<?php defined('APP_DIR') or die('Cannot access file.');

/**
 * The Database class acts as a PDO Object factory.
 * The Factory is available globaly through the static
 * scope, and is lazily created in order to prevent 
 * wasted resources. The PDO connection can be obtained
 * through the factory, and is also created lazily.
 *
 * @package core
 * @author Dominic Charley-Roy
 */
class Database extends Observable {
	
	protected $package = 'core';
	protected $class = 'database';
	
	/**
	 * Holds a singleton 'factory' which produces
	 * database connections.
	 * @see Database::getFactory()
	 * @access protected
	 */
	protected static $factory;
	
	private $db;
	
	/**
	 * This function will fetch a PDO object factory, which
	 * follows the singleton pattern. Only one instance of
	 * the factory is created, and it is created when the
	 * method is called for the first time. 
	 *
	 * @return Database A PDO connection factory
	 * @see getConnection()
	 */
	public static function getFactory() {
		if (!self::$factory)
			self::$factory = new Database();
		return self::$factory;
	}
	
	/**
	 * This function defines the table prefixing function. By default,
	 * it replaces 3 tildes ('~~~') with the table prefix defined in
	 * the {@link Config} key :
	 *		database => table_prefixes
	 *
	 * @param string $query
	 *		The query where prefixing must be applied
	 * @return string
	 *		The query with prefixing applied
	 */
	public static function prefixTables($query) {
		return str_replace('~~~', Config::get('database','table_prefixes'), $query);
	}
	
	/**
	 * This function is used by the factory to create a
	 * database connection which can be used globally.
	 *
	 * Events raised by this method:
	 *		connected()	- This is raised when the connection is first created,
	 *					  in other words the first time this method is called.
	 *					  Event handlers are not expect to return anything.
	 * 		accessed() - This is raised when the connection is fetched
	 *					  and any event calls are expected to return
	 *					  nothing.This event will be raised every time the
	 *					  method is called.
	 *
	 * @return PDO A reference to a PDO database connection
	 * @throws DatabaseException
	 *		This is thrown if the configuration file provides
	 *		a bad database driver.	
	 */
	public function getConnection() {
		if (!$this->db) {
			// Create a new database object based on the
			// type of driver selected in the config.core.php file
			$dbSettings = Config::get('database');
			switch (strtolower($dbSettings['driver'])) {
				case 'mysql':
					$this->db = new PDO('mysql:host='.$dbSettings['host'].';dbname='.$dbSettings['database_name'].';charset=UTF-8',
									$dbSettings['username'],
									$dbSettings['password']);
					break;
				default:
					throw new DatabaseException('An invalid database driver was selected in the configuration file.');
					
			}
			
			// Notify plugins that a connection was made
			$this->raiseEvent('connected');
		}
		
		// Notify plugins that the connection was accessed
		$this->raiseEvent('accessed');
		
		return $this->db;
	}
	
	/**
	 * This function facilitates querying the database by allowing
	 * you to pass the query, and optionally arguments. The function
	 * handles all prefixing of tables ({@link Database::prefixTables}) as well as
	 * the appropriate method of preparing and executing the query.
	 *
	 * @param string $query
	 *		This is the query to be executed
	 * @param array $args
	 *		This is an optional array of arguments which are based on
	 *		positional or named parameters within the query itself.
	 * @param boolean $args_assoc
	 *		This is an optional parameter which you must set to true if
	 *		you wish to use named parameters in the $args variable. If this
	 *		is left as false, it assumes that the array pssed in $args is
	 *		a traditionally indexe array and uses positional parameters.
	 * @param int $fetch_style
	 *		This is an optional parameter allowing you to specify a fetch type.
	 *		For a list of all possible types, see the $fetch_style argument
	 *		of the {$link PDOStatement::Fetch()} method. By default, it is set
	 *		to PDO::FETCH_BOTH
	 */
	public function query($query, $args = array(), $args_assoc = FALSE, $fetch_type = PDO::FETCH_BOTH) {
		$db = $this->getConnection();
		
		$query = Database::prefixTables($query);
		
		$stmt = $db->prepare($query);
		
		if (!empty($args)) {
			if ($args_assoc)
				foreach ($args as $key => $val)
					$stmt->bindValue($key, $val, is_numeric($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
			else 
				foreach ($args as $key => $val) 
					$stmt->bindValue($key + 1, $val, is_numeric($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
		}
		
		$stmt->execute();
		return $stmt->fetchAll($fetch_type);
	}
	
	private function __construct() { }
	
	public function __destruct() {
		if ($this->db)
			$this->db = null;
	}
}

/**
 * Exception used to specify there was a problem with the Database functionality
 * @package core
 * @author Dominic Charley-Roy
 */
class DatabaseException extends Exception { }