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
				'databaseName' => 'cms',
				'tablePrefixes' => '',
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
	 * based on it's setting name, or 'key'.
	 *
	 * @return The setting value if the setting is found.
	 * @throws ConfigKeyException 
	 *		A ConfigKeyException is thrown if there is no setting
	 *		with the passed name.
	 */
	public static function get($key) {
		if (array_key_exists($key, self::$cfg))
			return self::$cfg[$key];
		else
			throw new ConfigKeyException('The configuration setting \''.$key.'\' does not exist.');
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