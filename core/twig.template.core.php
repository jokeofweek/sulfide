<?php defined('APP_DIR') or die('Cannot access file.');

require_once Config::get('template', 'twig', 'lib_dir').DIRECTORY_SEPARATOR.'Autoloader.php';
Twig_Autoloader::register();

/**
 * This class extends the {@link Twig_Environment} class and provides a default constructor which
 * automatically loads a Twig_Environment object with all the settings defined in the Config 
 * key:
 *	'template' => 'twig'
 *
 * @author Dominic Charley-Roy
 * @package core
 */
class TemplateManager extends Twig_Environment {
	/**
	 * This constructor creates a new Twig_Environment object automatically loaded with all the
	 * settings specified in the Config class.
	 *
	 */
	public function __construct() {
		parent::__construct(new Twig_Loader_Filesystem(Config::get('template','template_dir')), Config::get('template', 'twig', 'settings'));
	}
}