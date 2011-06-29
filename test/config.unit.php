<?php

require_once('simpletest/autorun.php');
require_once('core/core.php');
require_once('core/config.core.php');

class SulfideConfigTest extends UnitTestCase {
	
	function __construct() {
		parent::__construct('Config.core.php Tests');
	}
	
	/*
	 * Test basic 'get()' functionality with single arguments
	 */
	
	function testGetNonExistantRootConfigKeyThrowsConfigKeyException() {
		try {
			Config::get('i-dont-exist');
			$this->fail();
		} catch (ConfigKeyException $e) {
			$this->pass();
		}
	}
	
	function testGetEmptyConfigKeyThrowsConfigKeyException() {
		try {
			Config::get('');
			$this->fail();
		} catch (ConfigKeyException $e) {
			$this->pass();
		}
	}
	
	function testGetValidKeyRepresentingGroupReturnsArray() {
		$this->assertTrue(is_array(Config::get('application')));
	}
	
	function testGetValidKeyRepresentingValueReturnsValue() {
		$value = Config::get('sulfide_version');
		$this->assertTrue(!is_array($value) && isset($value));
	}
	
	/*
	 * Test recursive 'get()' functionality
	 */
	function testPassingMultipleValuesToGetRecursesThroughConfigTree() {
		$this->assertNotNull(Config::get('application', 'name'));
	}
	
	function testGetInvalidKeyInAValueKeyThrowsConfigKeyException() {
		try {
			Config::get('sulfide_version', 'i-dont-exist');
			$this->fail();
		} catch (ConfigKeyException $e) {
			$this->pass();
		}
	}
	
	function testGetInvalidKeyInAValidGroupThrowsConfigKeyException() {
		try {
			Config::get('application', 'i-dont-exist');
			$this->fail();
		} catch (ConfigKeyException $e) {
			$this->pass();
		}
	}
	
	/*
	 * Test basic 'keyExists()' functionality
	 */
	function testKeyExistsReturnsFalseForNonExistingValueOrGroup() {
		$this->assertFalse(Config::keyExists('i-dont-exist'));
	}
	
	function testKeyExistsReturnsTrueForExistingGroup() {
		$this->assertTrue(Config::keyExists('application'));
	}
	
	function testKeyExistsReturnsTrueForExistingKey() {
		$this->assertTrue(Config::keyExists('sulfide_version'));
	}
	
	/*
	 * Test recursive 'keyExists()' functionality
	 */
	function testKeyExistsReturnsFalseForNonExistingKeyInExistingGroup() {
		$this->assertFalse(Config::keyExists('application', 'i-dont-exist'));
	}
	
	function testKeyExistsReturnsFalseForNonExistingKeyInNonExistingGroup() {
		$this->assertFalse(Config::keyExists('i-dont-exist', 'i-also-dont-exist'));
	}
	
	function testKeyExistsReturnsFalseWhenSearchingForAKeyWithinAnotherKey() {
		$this->assertFalse(Config::keyExists('sulfide_version', 'i-dont-exist'));
	}
	
	function testKeyExistsReturnsTrueForExistingKeyInExistingGroup() {
		$this->assertTrue(Config::keyExists('application', 'name'));
	}
	
	/*
	 * Test basic 'add()' functionality
	 */
	function testAddThrowsConfigKeyExceptionIfBlankValuesArePassed() {
		try {
			Config::add('','');
			$this->fail();
		} catch (ConfigKeyException $e) {
			$this->fail();
		} catch (Exception $e) {
			$this->pass();
		}
	}
	
	function testAddValueToExistingStringKeyHoldingvalueThrowsConfigKeyException() {
		try {
			Config::add('this-should-not-work', 'sulfide_version');
			$this->fail();
		} catch (ConfigKeyException $e) {
			$this->pass();
		}
	}
	
	function testAddValueToExistingStringKeyHoldingArrayThrowsConfigKeyException() {
		try {
			Config::add('this-should-not-work', 'application');
			$this->fail();
		} catch (ConfigKeyException $e) {
			$this->pass();
		}
	}
	
	function testAddValueToNonExistingStringKey() {
		Config::add('test-value', 'testing_add_value');
		$this->assertSame(Config::get('testing_add_value'), 'test-value');
	}
	
	function testAddArrayToNonExistingStringKey() {
		$testValues = array(1, 2, array('test a', 'test b'));
		Config::add($testValues, 'testing_add_array');
		$this->assertSame(Config::get('testing_add_array'), $testValues);
	}
	
	function testAddValueToNonExistingStringKeyCreatesNewReference() {
		$testValue = 10;
		Config::add($testValue, 'testing_add_value_reference');
		$testValue = 11;
		$this->assertNotEqual(Config::get('testing_add_value_reference'), $testValue);
	}
	
	function testAddArrayToNonExistingStringKeyCreatesNewReference() {
		$testValue = array(1, 2, 3);
		Config::add($testValue, 'testing_add_array_reference');
		$testValue = array_slice($testValue, 1);
		$this->assertNotEqual(count(Config::get('testing_add_array_reference')), count($testValue));
	}
	
	function testAddValueDoesNotEraseOtherAddedValues() {
		Config::add('test', array('application', 'test-erase'));
		$original = count(Config::get('application')) + 1;
		Config::add('test', array('application', 'test-erase-2'));
		$this->assertEqual($original, count(Config::get('application')));
	}
	
	/*
	 * Test recursive 'add()' functionality
	 */
	function testAddValueToExistingRecursiveStringKeyThrowsConfigKeyException() {
		try {
			Config::add('this-should-not-work', array('application', 'name'));
			$this->fail();
		} catch (ConfigKeyException $e) {
			$this->pass();
		}
	}
	
	function testAddValueToExistingRecursivePathContainingStringKeyThrowsConfigKeyException() {
		try {
			Config::add('this-should-not-work', array('sulfide_version', 'value'));
			$this->fail();
		} catch (ConfigKeyException $e) {
			$this->pass();
		}
	}
	
	function testAddValueToExistingPathContainingEmptyKeyThrowsException() {
		try {
			Config::add('this-should-not-work', array('applicaton', '', 'test'));
		} catch (ConfigKeyException $e) {
			$this->fail();
		} catch (Exception $e) {
			$this->pass();
		}
	}
	
	function testAddValueToExistingRecursivePathWithNonExistingEndKeyAddsValue() {
		Config::add('this-should-work', array('application', 'testing'));
		$this->assertTrue(Config::keyExists('application', 'testing') && 
						  Config::get('application', 'testing') == 'this-should-work');
	}
	
	function testAddValueToNonExistingRecursivePathsCreatesRequiresGroups() {
		Config::add('this-should-work', array('testing', 'testing-a', 'testing-b'));
		$this->assertTrue(Config::keyExists('testing') && 
						  Config::keyExists('testing', 'testing-a') && 
						  Config::keyExists('testing', 'testing-a', 'testing-b') && 
						  Config::get('testing', 'testing-a', 'testing-b') == 'this-should-work');
	}
	
}