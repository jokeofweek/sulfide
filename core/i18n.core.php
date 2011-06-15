<?php defined('APP_DIR') or die('Cannot access file.');

// Set up support for UTF  
ini_set('default_charset','utf-8');

/**
 * The Language object provides internationalization functionality
 * by loading *.lang.php files. It is optimized for multiple language 
 * loading as it caches all loaded language files. It also allows
 * for easy translation of a string from the default language
 * to another language.
 *
 * @package core
 * @author Dominic Charley-Roy
 */
class Language extends Observable {
	protected $package = 'core';
	protected $class = 'language';
	
	private static $instance;

	private static $langData = array();
	private static $lang = '';
	private static $_cache = array();
	
	/**
	 * This function fetches an instance of the Language class
	 * to allow plugin hooking. 
	 *
	 * @return Observable 
	 *		An instance of the class is returned as an Observable
	 *		object to allow plugins to hook to the Language manager.
	 * 
	 */
	public static function getHookable() {
		if (!isset(self::$instance))
			self::$instance = new Language();
		return self::$instance;
	}
	
	/**
	 * This function loads a language file and sets it as the 
	 * current language in use. Note that if a language has been
	 * previously loaded, it is cached to optimize re-loading
	 * of the same language. A proper language file must be 
	 * located in the lang sub-directory of the application root,
	 * and there must be a file named $name.php, where $name is the
	 * name passed to this function.
	 *
	 * Events raised by this method:
	 *		loading(name) - This is called before a language file is
	 *					    loaded, and the name of the language is passed.
	 *		loaded(name)  - This is called once a language file has been
	 *						loaded, and the name of the language is passed.
	 *
	 * @param string $name This is the name of the language file
	 * @throws FileNotFoundException
	 *		This is thrown if there is no file with the name passed
	 *		in the lang folder.
	 * @throws LanguageException
	 *		This is thrown if the language file passed has no
	 *		$lang array containing the translated strings
	 *
	 */
	public static function load($name) {
		// Cache current language for future re-loading
		if (self::lang()) {
			self::$_cache[self::$lang] = self::$langData;
		}
		
		// Raise 'loading' event
		self::getHookable()->raiseEvent('loading', array($name));
		
		// If the language has been cached, re-load it, or else
		// load it from the file.
		if (array_key_exists($name, self::$_cache)) {
			self::$langData = self::$_cache[$name];
		} else {
			if (!file_exists(APP_DIR.'lang/'.$name.'.lang.php')) {
				throw new FileNotFoundException('No language file found for the language '.$name);
			}
			
			include_once(APP_DIR.'lang/'.$name.'.lang.php');

			if (!isset($lang)) {
				throw new LanguageException('There must be an array named $lang in the '.$name.' language file which holds all the translated strings.');
			}
			
			self::$langData = $lang;
		}
		
		self::$lang = $name;
		
		// Raise 'loaded' event
		self::getHookable()->raiseEvent('loaded', array($name));
	}
	
	/**
	 * This function returns the name of the current language in use.
	 *
	 * @return string The name of the current language
	 */
	public static function lang() {
		return self::$lang;
	}
	
	/**
	 * This function attempts to translate a string based on the 
	 * current loaded language. It also supports in-string
	 * parameters. For example, if you called
	 *
	 * 		translate('Hello, {user}', array('{user}' => 'Joe'));
	 *
	 * It would return the string Hello, Joe. Note however that the 
	 * parameter replacing is done <b>after</b> the string is translated,
	 * therefore any language file would need to require an array key
	 * in the $lang array like so:
	 *
	 * $lang = array(
	 *		...
	 *		'Hello, {user}' => 'Bonjour {user}!'
	 *		...);
	 *
	 * Note that the _($string, $values) function is provided as a 
	 * globally available function as a convenience, however 
	 * these two functions have the same functionality.
	 *
	 * @param string $string 
	 *		This is the string to be translated
	 * @param array $values
	 *		This is an optional parameter which allows you to pass
	 *		pairs of key-values which will be replaced in the string
	 *		once it has been translated
	 * @return string 
	 *		The fully translated string based on the loaded language file.
	 *		If no translation is found for the string in the loaded
	 *		language, the passed string will be returned with the replaced
	 *		parameters.
	 */
	public static function translate($string, array $values = NULL) {
		if (isset(self::$langData[$string]))
			$string = self::$langData[$string];
		
		if (!empty($values))
			$string = strtr($string, $values);
			
		return $string;
	}
}

if (!function_exists('_')) {
	function _($string, array $values = NULL ) {
		return Language::translate($string, $values);
	}
}

/**
 * Basic exception used to specify there was a problem with 
 * Language / I18n functionality
 * @package core
 * @author Dominic Charley-Roy
 */
class LanguageException extends Exception { }
