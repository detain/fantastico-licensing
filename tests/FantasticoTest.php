<?php

use Detain\Fantastico\Fantastico;
use PHPUnit\Framework\TestCase;

class FantasticoTest extends TestCase {
	/**
	 * @var Fantastico
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		$this->object = new Fantastico(getenv('FANTASTICO_USERNAME'), getenv('FANTASTICO_PASSWORD'));
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown() {
	}

	/**
	 * @covers Detain\Fantastico\Fantastico::connect
	 */
	public function testConnect() {
		$this->object->connect();
		$this->assertTrue($this->object->connected, 'test to make sure it connected ok');
	}

	/**
	 * @covers Detain\Fantastico\Fantastico::getIpTypes
	 */
	public function testGet_ip_types() {
		$types = $this->object->getIpTypes();
		$this->assertTrue(is_array($types), 'returns an array of types');
	}

	/**
	 * @covers Detain\Fantastico\Fantastico::isType
	 * @todo   Implement testIs_type().
	 */
	public function testIs_type() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Detain\Fantastico\Fantastico::getIpList
	 * @todo   Implement testGetIpList().
	 */
	public function testGetIpList() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Detain\Fantastico\Fantastico::getIpListDetailed
	 * @todo   Implement testGetIpListDetailed().
	 */
	public function testGetIpListDetailed() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Detain\Fantastico\Fantastico::validIp
	 * @todo   Implement testValid_ip().
	 */
	public function testValid_ip() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Detain\Fantastico\Fantastico::getIpDetails
	 * @todo   Implement testGetIpDetails().
	 */
	public function testGetIpDetails() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Detain\Fantastico\Fantastico::editIp
	 * @todo   Implement testEditIp().
	 */
	public function testEditIp() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Detain\Fantastico\Fantastico::addIp
	 * @todo   Implement testAddIp().
	 */
	public function testAddIp() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Detain\Fantastico\Fantastico::deactivateIp
	 * @todo   Implement testgetDeactivateIp().
	 */
	public function testgetDeactivateIp() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Detain\Fantastico\Fantastico::reactivateIp
	 * @todo   Implement testReactivateIp().
	 */
	public function testReactivateIp() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Detain\Fantastico\Fantastico::deleteIp
	 * @todo   Implement testDeleteIp().
	 */
	public function testDeleteIp() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}
}
