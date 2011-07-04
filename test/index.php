<?php

include_once('check.php');
require_once('simpletest/autorun.php');

class SulfideTests extends TestSuite {
	function __construct() {
		parent::__construct('All Sulfide Tests');
		
		// Add the root directory to the include path to facilitate including sulfide components
		set_include_path(get_include_path().PATH_SEPARATOR.dirname(__file__).'/../');
		
		$this->collect(dirname(__FILE__), new SimplePatternCollector('/.unit.php/'));
	}
}