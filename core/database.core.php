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
					$this->db = new PDO('mysql:host='.$dbSettings['host'].';dbname='.$dbSettings['databaseName'].';charset=UTF-8',
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
	
	
	private function __construct() {
	}
	
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