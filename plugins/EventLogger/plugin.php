<?php defined('APP_DIR') or die('Cannot access file.');

class EventLogger extends Plugin {

	protected $pluginName = 'EventLogger';
	private $functionCalls = array();
	
	public function __call($name, $args) {
		$this->functionCalls[] = array('name' => $name, 'args' => $args);
	}
	
	public function fetch() {
		return $this->functionCalls;
	}
	
}