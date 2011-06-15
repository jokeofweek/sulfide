<?php defined('APP_DIR') or die('Cannot access file.');

include_once(APP_DIR.implode(DIRECTORY_SEPARATOR, array('core','ext','smarty','Smarty.class.php')));

class Template extends Smarty {
	public function __construct($caching = FALSE) {
		$config = Config::get('template');
		$this->template_dir = $config['template_dir'];
		$this->compile_dir = $config['compile_dir'];
		$this->config_dir = $config['config_dir'];
		$this->cache_dir = $config['cache_dir'];
		$this->caching = $caching;
	}
}