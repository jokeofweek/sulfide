<?php defined('APP_DIR') or die('Cannot access file.');

/**
 * The Config class allows the application to store
 * all settings globaly available under one class.
 * The settings can now be accessed from any other class
 * through the {@link Config::get($key)} static method.
 *
 * @see Config::get($key)
 * @package core
 * @author Dominic Charley-Roy
 */
class Config {
	private static $cfg;

	public static function initialize(){
		self::$cfg = array (
			/*
			 * The database connection settings
			 */
			'database' => array(
				'username' => 'root',
				'password' => '',
				'host' => 'localhost',
				'database_name' => 'cms',
				'table_prefixes' => '',
				'driver' => 'mysql'
			), 
			
			/*
			 * The smarty template settings
			 */
			'template' => array(
				'template_dir' => APP_DIR.'templates',
				'cache_dir' => APP_DIR.'templates'.DIRECTORY_SEPARATOR.'cache',
				'compile_dir' => APP_DIR.'templates'.DIRECTORY_SEPARATOR.'compile',
				'config_dir' => APP_DIR.'core'.implode(DIRECTORY_SEPARATOR, array('core', 'ext', 'smarty'))
			),
			
			/*
			 * The routing system settings
			 */
			'routing' => array(
				'default_controller' => 'home',
				'default_action' => 'index',
				'controller_dir' => APP_DIR.'pages'.DIRECTORY_SEPARATOR,
				'error_controller' => 'error',
				'error_action' => 'error',
			),
			
			/*
			 * Plugin system settings
			 */
			'plugins' => array(
				'dir' => APP_DIR.'plugins'.DIRECTORY_SEPARATOR,
			)
		);
	}
	
	/**
	 * This function will fetch a given configuration setting
	 * based on it's setting name, or 'key', or a path to a key.
	 *
	 * @param mixed $key 
	 *		The function accepts a variable number of arguments,
	 *		and can be used to drill down the configuration tree.
	 *		For example, if you wanted to fetch the username setting
	 *		of the database group, you would call:
	 *				get('database', 'username');
	 * @return The setting value if the setting is found.
	 * @throws ConfigKeyException 
	 *		A ConfigKeyException is thrown if there is no setting
	 *		with the passed name.
	 */
	public static function get($key) {
		$keys = func_get_args();
		$current = self::$cfg;
		
		foreach($keys as $key) {
			if (is_array($current) && array_key_exists($key, $current))
				$current = $current[$key];
			else
				throw new ConfigKeyException('The configuration setting \''.implode('=>', $keys).'\' does not exist.');
		}
		
		return $current;
	}
}

/**
 * Basic Exception used through the Config object for things
 * such as invalid keys
 *
 * @package core
 * @author Dominic Charley-Roy
 */
class ConfigKeyException extends Exception { }

Config::initialize();