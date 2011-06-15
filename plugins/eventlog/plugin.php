<?php

class eventlog extends Plugin {

	protected $pluginName = 'eventlog';
	private $functionCalls = array();
	
	public function __call($name, $args) {
		$this->functionCalls[] = array('name' => $name, 'args' => $args);
	}
	
	public function fetch() {
		return $this->functionCalls;
	}
	
}