<?php

define('CORE_DIR', dirname(__FILE__).DIRECTORY_SEPARATOR);
define('APP_DIR', realpath(CORE_DIR.'..'.DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR);

// Include the config and plugins core modules
require_once(CORE_DIR.'config.core.php');
require_once(CORE_DIR.'plugins.core.php');

/**
 * The Core class is a singleton object which is globally available
 * and allows system events to be triggered from external code
 * through the use of the {@link Observable->raiseEvent()} function.
 * It also provides core functionality, such as including and depending
 * on files.
 *
 * @author Dominic Charley-Roy
 * @package core
 */
class Core extends Observable {
	protected $package = 'core';
	protected $class = 'core';
	
	private static $instance;
	
	/**
	 * This function fetches a hookable instance of the Core class
	 * in order to allow {@link Observer} objects to watch the
	 * system for any events.
	 *
	 * @return Observable 
	 *		An instance of the class is returned as an Observable
	 *		object to allow plugins to hook to the Core system.
	 * 
	 */
	public static function getHookable() { 
		if (!isset(self::$instance))
			self::$instance = new Core();
		return self::$instance;
	}
	
	/**
	 * Acts as a convience method which calls the internal raiseEvent
	 * method of the Observable object.
	 *
	 * @see Observable::raiseEvent()
	 */
	public static function raise($event, array $args = NULL, $collect = FALSE)  {
		self::getHookable()->raiseEvent($event, $args, $collect);
	}
	
	/**
	 * This is a globally available function which allows the explicit
	 * inclusion of core components (/core/*.core.php). The function
	 * supports a variable number of arguments, and will include
	 * every listed file. Note that the files are loaded in passed order,
	 * and therefore extra steps must be taken to ensure proper loading
	 * of all classes.
	 *
	 * An example of a possible order is the following:
	 * 		requires('database', 'i18n', 'routing')
	 *
	 * Note that many of the core components are dependent on the 'config'
	 * and 'plugins' libraries, and therefore these components are loaded
	 * by default
	 *
	 * Also note that if a file is based on an interface, such as the
	 * db.session component, the session component will be automatically
	 * loaded first and then the db.session component.
	 *
	 * @throws FileNotFoundException
	 *		A FileNotFoundException is thrown if one of the filenames
	 *		passed could not be loaded or was not found.
	 * @package core
	 * @author Dominic Charley-Roy
	 */
	public static function requires() {	
		$includes = func_get_args();

		foreach ($includes as $file) {
			// Load any extending modules
			if (strrpos($file, '.') != FALSE) {
				$parts = explode('.', $file);
				$path = 'core.php';
				
				for ($i = count($parts) - 1; $i >= 1; $i--) {
					$path = $parts[$i].'.'.$path;
					require_once CORE_DIR.$path;
				}
			}
			
			$file_path = CORE_DIR.$file.'.core.php';
			
			if (file_exists($file_path))
				require_once $file_path;
			else
				throw new FileNotFoundException('The core module '.$file.' was not found.');
		}
	}

	/**
	 * This is a basic function which checks whether specified core components are already
	 * included, and if not returns false. This function can be used to denote that a 
	 * given class or file is dependent on core components, and the file should therefore
	 * handle a dependence appropriately.
	 *
	 * Note that in the case of plugins, it is against the Sulfide mindset to load core 
	 * components as they are included and excluded to the developper's discretion.
	 *
	 * @param mixed $includes
	 *		This is a variable-length parameter, allowing you to specify as many
	 *		core components as you wish. It uses the same argument naming as in
	 *		the {@link requires()} function. So for example, if you wanted to state
	 *		that a plugin depended on the database and routing class, you would
	 *		do:
	 *			depends('database', 'routing');
	 *      Note that these represent core components, which follow the naming 
	 *		convention {component}.core.php.
	 * @return boolean 
	 *		Returns a stating whether all the passed files are included or 
	 *	    not.
	 *
	 */
	public static function depends() {
		$includes = func_get_args();
		
		foreach ($includes as $file) {
			if (!in_array(CORE_DIR.$file.'.core.php', get_included_files())) {
				return false;
			}
		}
		
		return true;
	}
}

/**
 * Basic exception to specify a File I/O error
 * @package core
 * @author Dominic Charley-Roy
 */
class FileNotFoundException extends Exception { }

/**
 * Basic exception used to specify that there was a dependency related error,
 * such as a component not being included that a plugin is dependent on.
 * @package core
 * @author Dominic Charley-Roy
 */

class DependencyException extends Exception { }