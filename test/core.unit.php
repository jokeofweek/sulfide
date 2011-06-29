<?php

require_once('simpletest/autorun.php');
require_once('core/core.php');

class SulfideCoreTest extends UnitTestCase {
	
	/*
	 * Test basic dependency loading
	 */
	function testCoreLoadsConfig() {
		$this->assertTrue(class_exists("Config"));
	}
	
	function testCoreLoadsPlugin() {
		$this->assertTrue(class_exists("Plugin"));
	}
	
	/*
	 * Test basic 'requires()' functionality
	 */
	function testRequiresFileNotFound() {
		try {
			requires('this-should-not-work');
			$this->fail();
		} catch (FileNotFoundException $e) { 
			$this->pass();
		}
	}
	
	function testRequiresEmptyThrowsException() {
		try {
			requires('');
			$this->fail();
		} catch (Exception $e) {
			$this->pass();
		}
	}
	
	function testRequiresSingleFile() {
		requires('database');
		$this->assertTrue(class_exists("Database"));
	}
	
	function testRequiresMultipleFiles() {
		requires('i18n', 'routing');
		$this->assertTrue(class_exists("Language") && class_exists("Routing"));
	}
	
	/*
	 * Test modular 'requires()' functionality
	 */
	 
	function testRequiresLoadsAllSubmoduleFilesInTheAppropriateOrder() {
		// TODO: This requires a working example of submodules
	}

	function testRequiresLoadsAllSubmoduleFiles() {
		$total = count(get_included_files());
		requires('db.session');
		$this->assertEqual($total + 2, count(get_included_files()));
	}
	
}