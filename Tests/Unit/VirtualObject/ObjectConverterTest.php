<?php
/*
 *  Copyright notice
 *
 *  (c) 2014 Daniel Corn <info@cundd.net>, cundd
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 */

/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 24.03.14
 * Time: 12:26
 */

namespace Cundd\Rest\Test\VirtualObject;

use Cundd\Rest\VirtualObject\Exception\InvalidPropertyException;
use Cundd\Rest\VirtualObject\ObjectConverter;
use Cundd\Rest\VirtualObject\VirtualObject;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;

require_once __DIR__ . '/AbstractVirtualObjectCase.php';


class ObjectCaseConverterTest extends AbstractVirtualObjectCase {
	/**
	 * @var \Cundd\Rest\VirtualObject\ObjectConverter
	 */
	protected $fixture;

	public function setUp() {
		$this->fixture = new ObjectConverter();
		$this->fixture->setConfiguration($this->getTestConfiguration());
		parent::setUp();
	}

	public function tearDown() {
		unset($this->fixture);
		parent::tearDown();
	}

	/**
	 * @test
	 */
	public function prepareDataFromVirtualObjectDataTest() {
		$testObjectData = $this->testObjectData;
		$virtualObject = new VirtualObject($testObjectData);
		$rawData       = $this->fixture->prepareDataFromVirtualObjectData($virtualObject->getData());
		$this->assertEquals($this->testRawData, $rawData);
	}

	/**
	 * @test
	 */
	public function prepareForVirtualObjectDataTest() {
		$testRawData   = $this->testRawData;
		$preparedData = $this->fixture->prepareForVirtualObjectData($testRawData);
		$this->assertEquals($this->testObjectData, $preparedData);
	}

	/**
	 * @test
	 */
	public function prepareDataFromVirtualObjectDataUpdateTest() {
		$testObjectData = $this->testObjectData;
		unset($testObjectData['property1']);
		unset($testObjectData['property6']);

		$testRawData   = $this->testRawData;
		unset($testRawData['property_one']);
		unset($testRawData['property_six']);

		$virtualObject = new VirtualObject($testObjectData);
		$rawData       = $this->fixture->prepareDataFromVirtualObjectData($virtualObject->getData(), FALSE);
		$this->assertEquals($testRawData, $rawData);
	}

	/**
	 * @test
	 */
	public function prepareForVirtualObjectDataUpdateTest() {
		$testRawData   = $this->testRawData;
		unset($testRawData['property_one']);
		unset($testRawData['property_six']);

		$testObjectData = $this->testObjectData;
		unset($testObjectData['property1']);
		unset($testObjectData['property6']);

		$preparedData = $this->fixture->prepareForVirtualObjectData($testRawData, FALSE);
		$this->assertEquals($testObjectData, $preparedData);
	}

	/**
	 * @test
	 */
	public function convertFromVirtualObjectTest() {
		$testObjectData = $this->testObjectData;
		$virtualObject = new VirtualObject($testObjectData);
		$rawData       = $this->fixture->convertFromVirtualObject($virtualObject);
		$this->assertEquals($this->testRawData, $rawData);
	}

	/**
	 * @test
	 */
	public function convertToVirtualObjectTest() {
		$testRawData   = $this->testRawData;
		$virtualObject = $this->fixture->convertToVirtualObject($testRawData);
		$this->assertEquals($this->testObjectData, $virtualObject->getData());
	}

	/**
	 * @test
	 */
	public function convertFromVirtualObjectWithTypeTransformationTest() {
		$testObjectData = $this->testObjectData;
		$testObjectData['property2'] = '0.98';
		$testObjectData['property3'] = 10.002;
		$virtualObject = new VirtualObject($testObjectData);
		$rawData       = $this->fixture->convertFromVirtualObject($virtualObject);
		$this->assertEquals($this->testRawData, $rawData);


		$testObjectData = $this->testObjectData;
		$testObjectData['property2'] = 'Actually this should not be a string';
		$virtualObject = new VirtualObject($testObjectData);
		$rawData       = $this->fixture->convertFromVirtualObject($virtualObject);
		$this->assertNotEquals($this->testRawData, $rawData);
	}

	/**
	 * @test
	 */
	public function convertToVirtualObjectWithTypeTransformationTest() {
		$testRawData   = $this->testRawData;
		$testRawData['property_two'] = '0.98';
		$testRawData['property_three'] = 10.002;
		$virtualObject = $this->fixture->convertToVirtualObject($testRawData);
		$this->assertEquals($this->testObjectData, $virtualObject->getData());


		$testRawData   = $this->testRawData;
		$testRawData['property_two'] = 'Actually this should not be a string';
		$virtualObject = $this->fixture->convertToVirtualObject($testRawData);
		$this->assertNotEquals($this->testObjectData, $virtualObject->getData());
	}

	/**
	 * @test
	 */
	public function convertFromVirtualObjectWithSkippedUndefinedPropertyTest() {
		$this->fixture->getConfiguration()->setSkipUnknownProperties(TRUE);

		$testObjectData = $this->testObjectData;
		$testObjectData['property7'] = 'What ever - this must not be in the result';
		$virtualObject = new VirtualObject($testObjectData);
		$rawData       = $this->fixture->convertFromVirtualObject($virtualObject);

		$this->assertFalse(isset($rawData['property7']));
		$this->assertEquals($this->testRawData, $rawData);
	}

	/**
	 * @test
	 */
	public function convertToVirtualObjectWithSkippedUndefinedPropertyTest() {
		$this->fixture->getConfiguration()->setSkipUnknownProperties(TRUE);

		$testRawData   = $this->testRawData;
		$testRawData['property_seven'] = 'What ever - this must not be in the result';
		$virtualObject = $this->fixture->convertToVirtualObject($testRawData);

		$virtualObjectData = $virtualObject->getData();
		$this->assertFalse(isset($virtualObjectData['property_seven']));
		$this->assertEquals($this->testObjectData, $virtualObjectData);
	}

	/**
	 * @test
	 * @expectedException \Cundd\Rest\VirtualObject\Exception\InvalidPropertyException
	 */
	public function convertFromVirtualObjectWithUndefinedPropertyTest() {
		$testObjectData = $this->testObjectData;
		$testObjectData['property7'] = 'What ever - this must not be in the result';
		$virtualObject = new VirtualObject($testObjectData);
		$rawData       = $this->fixture->convertFromVirtualObject($virtualObject);

		$this->assertFalse(isset($rawData['property7']));
		$this->assertEquals($this->testRawData, $rawData);
	}

	/**
	 * @test
	 * @expectedException \Cundd\Rest\VirtualObject\Exception\InvalidPropertyException
	 */
	public function convertToVirtualObjectWithUndefinedPropertyTest() {
		$testRawData   = $this->testRawData;
		$testRawData['property_seven'] = 'What ever - this must not be in the result';
		$virtualObject = $this->fixture->convertToVirtualObject($testRawData);

		$virtualObjectData = $virtualObject->getData();
		$this->assertFalse(isset($virtualObjectData['property_seven']));
		$this->assertEquals($this->testObjectData, $virtualObjectData);
	}

	/**
	 * @test
	 * @expectedException \Cundd\Rest\VirtualObject\Exception\MissingConfigurationException
	 */
	public function throwExceptionIfConfigurationIsNotSet() {
		$virtualObject = new VirtualObject();
		$converter     = new ObjectConverter();
		$converter->convertFromVirtualObject($virtualObject);
	}

	protected $testRawData = array(
		'property_one'   => 'Property 1 value',
		'property_two'   => 0.98,
		'property_three' => 10,
		'property_four'  => PHP_INT_MAX,
		'property_five'  => TRUE,
		'property_six'   => FALSE,
	);

	protected $testObjectData = array(
		'property1' => 'Property 1 value',
		'property2' => 0.98,
		'property3' => 10,
		'property4' => PHP_INT_MAX,
		'property5' => TRUE,
		'property6' => FALSE,
	);

}
