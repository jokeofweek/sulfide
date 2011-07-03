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
			 * Application specific connection settings
			 */
			'application' => array(
				'name' => 'Basic Sulfide Application'
			),
			
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
			 * The template settings
			 */
			'template' => array(
				/*
				 * General settings
				 */ 
				'template_dir' => './templates',
				
				/*
				 * Settings for twig
				 */
				'twig' => array(
					'settings' => array(
						'strict_variables' => TRUE,
						'cache' => APP_DIR.'templates/cache',
					),
					'lib_dir' => APP_DIR.implode(DIRECTORY_SEPARATOR, array('core', 'ext', 'twig')),
				),
				/*
				 * Settings for smarty
				 */
				'smarty' => array(
					'cache_dir' => APP_DIR.'templates/cache',
					'compile_dir' => APP_DIR.'templates/compile',
					'lib_dir' => APP_DIR.implode(DIRECTORY_SEPARATOR, array('core', 'ext', 'smarty')),
				)
			),
			
			/*
			 * The routing system settings
			 */
			'routing' => array(
				'default_controller' => 'home',
				'controller_dir' => APP_DIR.'pages'.DIRECTORY_SEPARATOR,
				'error_controller' => 'error',
				'error_action' => 'error',
			),
			
			/*
			 * Plugin system settings
			 */
			'plugins' => array(
				'dir' => APP_DIR.'plugins'.DIRECTORY_SEPARATOR,
			),
			
			/*
			 * Sulfide-related settings
			 */
			'sulfide_version' => '0.0.1'
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
	
	/**
	 * This function allows you to add a value or array to the
	 * Config tree, which can then be fetched using the 
	 * {@link Config::get()} method.
	 *
	 * @param mixed $values
	 *		This is the values to add to the designated path
	 * @param mixed $path
	 *		This is the path of keys to which the values will be 
	 *		added. If a string is passed, it will attempt to 
	 *		create a key based on the string in the root of the
	 *		Config tree, and then it will add the values. If it is
	 *		an array, it will traverse down the path, adding the values
	 *		to the last member of the path
	 * @throws ConfigKeyException
	 *		This is thrown in a variety of situations. It is thrown
	 *		if there already exists a value at the given path, either
	 *		at the end of the path or along the way (for example, one of
	 *		the path keys has an integer already associated with it).
	 * @throws Exception
	 *		This broader exception is thrown if a path contains an empty
	 *		key. For example, calling add('test', '') would throw this exception.
	 */
	public static function add($values, $path) {
		if (is_array($path)) {
			// Must add through reference
			$current = &self::$cfg;
			
			foreach($path as $value) {
				if (is_array($current)) {
					if (empty($value))
						throw new Exception('The configuration values could not be added to the path \''.implode('=>', $path).'\' as the path includes an empty key.');
				
					if (!array_key_exists($value, $current)) 
						$current[$value] = array();
					$current = &$current[$value];
				} else {
					throw new ConfigKeyException('The configuration values could not be added to the path \''.implode('=>', $path).'\' as one of the values in the path does not refer to an array.');
				}
			}
			
			if (empty($current)) 
				$current = $values;
			else
				throw new ConfigKeyException('The configuration values could not be added to the path \''.implode('=>', $path).'\' as there were already non-array values in the path.');

		} else if (is_string($path)) {
			if (empty($path))
				throw new Exception('The configuration values could not be added to the path \'\' as the path includes an empty key.');
		
			if (!array_key_exists($path, self::$cfg))
				self::$cfg[$path] = $values;
			else
				throw new ConfigKeyException('There already exists a group of configuration settings under the key '.$path.'.');
		}
	}
	
	/**
	 * This function will check whether a given key exists, as well as
	 * whether a path exists. It can be safely assumed that, for any
	 * value which returns true when passed to this function, there exists
	 * an object which can be fetched using the {@link Config::get()} method.
	 * 
	 * @param mixed $keys 
	 *		The function accepts a variable number of arguments,
	 *		and can be used to drill down the configuration tree.
	 *		For example, if you wanted to check whether the key 
	 *		located at 'database' => 'credentials' => 'username'
	 *		existed, you would do
	 *				keyExists('database', 'credentials', 'username');
	 *		You can also check whether a path exists in the same manner
	 *
	 * @returns boolean
	 *		A boolean is returned stating whether the passed key/gorup exists
	 *		in the configuration tree.
	 */
	public static function keyExists($keys) {
		$keys = func_get_args();
		$start = self::$cfg;
		
		foreach ($keys as $key)
			if (is_array($start) && array_key_exists($key, $start))
				$start = $start[$key];
			else
				return false;
		
		return true;
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