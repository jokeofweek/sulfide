<?php defined('APP_DIR') or die('Cannot access file.');

require_once APP_DIR.implode(DIRECTORY_SEPARATOR, array('core','ext','smarty','Smarty.class.php'));

/**
 * This class extends the {@link Smarty} class and provides a default constructor which
 * automatically loads a Smarty object with all the settings defined in the Config 
 * key:
 *	'template' => 'smarty'
 *
 * @author Dominic Charley-Roy
 * @package core
 */
class Template extends Smarty {
	/**
	 * This constructor creates a new Smarty object using the configuration settings
	 * specified in the Config class.
	 *
	 * @param boolean $caching
	 *		This is by default set to false, and is an optional parameter. You may 
	 *		set this to true to enable caching on a given page.
	 */
	public function __construct($caching = FALSE) {
		$config = Config::get('template');
		$this->template_dir = $config['template_dir'];
		$this->compile_dir = $config['smarty']['compile_dir'];
		$this->config_dir = $config['smarty']['lib_dir'];
		$this->cache_dir = $config['smarty']['cache_dir'];
		$this->caching = $caching;
	}
}