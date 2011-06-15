<?php

define('CORE_DIR', dirname(__FILE__).DIRECTORY_SEPARATOR);
define('APP_DIR', realpath(CORE_DIR.'..'.DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR);

// Include the config and plugins core modules
require_once(CORE_DIR.'config.core.php');
require_once(CORE_DIR.'plugins.core.php');

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
function requires()
{	
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
 * Basic Exception to specify a File I/O error
 * @package core
 * @author Dominic Charley-Roy
 */
class FileNotFoundException extends Exception { }
