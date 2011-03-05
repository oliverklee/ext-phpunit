<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2011 Oliver Klee (typo3-coding@oliverklee.de)
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Testcase for the Tx_Phpunit_BackEnd_Module class.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Phpunit_BackEnd_ModuleTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Phpunit_BackEnd_Module
	 */
	private $fixture;

	/**
	 * the output of the module
	 *
	 * @var string
	 */
	private $output = '';

	public function setUp() {
		$fixtureClassName = $this->createAccessibleProxy();
		$this->fixture = new $fixtureClassName();
	}

	public function tearDown() {
		$this->fixture->__destruct();
		unset($this->fixture);
	}

	/*
	 * Utility functions
	 */

	/**
	 * Creates a subclass Tx_Phpunit_BackEnd_Module with the protected functions
	 * made public.
	 *
	 * @return string the name of the accessible proxy class
	 */
	private function createAccessibleProxy() {
		$className = 'Tx_Phpunit_BackEnd_ModuleAccessibleProxy';
		if (!class_exists($className, FALSE)) {
			eval(
				'class ' . $className . ' extends Tx_Phpunit_BackEnd_Module {' .
				'  public function output($output) {' .
				'    parent::output($output);' .
				'  }' .
				'}'
			);
		}

		return $className;
	}

	/**
	 * Callback function for collecting the output of the module.
	 *
	 * @param string $text the output to collect, may also be empty
	 *
	 * @return void
	 */
	public function outputCallback($text) {
		$this->output .= $text;
	}

	/**
	 * @test
	 */
	public function createAccessibleProxyCreatesModuleSubclass() {
		$className = $this->createAccessibleProxy();

		$this->assertInstanceOf(
			'Tx_Phpunit_BackEnd_Module',
			new $className()
		);
	}

	/**
	 * @test
	 */
	public function outputCallbackCollectsOutput() {
		$this->outputCallback('Hello world!');

		$this->assertSame(
			'Hello world!',
			$this->output
		);
	}

	/**
	 * @test
	 */
	public function outputCallbackCollectsOutputInAddingOrder() {
		$this->outputCallback('1');
		$this->outputCallback('2');

		$this->assertSame(
			'12',
			$this->output
		);
	}


	/*
	 * Unit tests
	 */

	/**
	 * @test
	 */
	public function outputOutputsOutput() {
		$output = 'Hello world!';

		ob_start();
		$this->fixture->output($output);

		$this->assertSame(
			$output,
			ob_get_contents()
		);

		ob_end_clean();
	}
}
?>