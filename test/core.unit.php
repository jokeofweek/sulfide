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
		$this->assertTrue(in_array(APP_DIR.'core'.DIRECTORY_SEPARATOR.'database.core.php', get_included_files()));
	}
	
	function testRequiresMultipleFiles() {
		requires('i18n', 'routing');
		$this->assertTrue(in_array(APP_DIR.'core'.DIRECTORY_SEPARATOR.'i18n.core.php', get_included_files()) && 
						  in_array(APP_DIR.'core'.DIRECTORY_SEPARATOR.'routing.core.php', get_included_files()));
	}
	
	/*
	 * Test modular 'requires()' functionality
	 */

	function testRequiresLoadsAllSubmoduleFiles() {
		// Test that it actually loads two files
		$this->assertFalse(in_array(APP_DIR.'core'.DIRECTORY_SEPARATOR.'session.core.php', get_included_files()) || 
						   in_array(APP_DIR.'core'.DIRECTORY_SEPARATOR.'db.session.core.php', get_included_files())); 
						   
		$total = count(get_included_files());
		requires('db.session');
		$includes = get_included_files();
		$this->assertEqual($total + 2, count($includes));
		
		// Test that it loads the submodule files in the appropriate order 
		$this->assertTrue($includes[$total] == APP_DIR.'core'.DIRECTORY_SEPARATOR.'session.core.php'&&
						  $includes[$total + 1] == APP_DIR.'core'.DIRECTORY_SEPARATOR.'db.session.core.php');
	}
	
}