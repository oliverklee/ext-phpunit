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
	 * @var t3lib_beUserAuth
	 */
	private $backEndUserBackup = NULL;

	/**
	 * backup of $_POST
	 *
	 * @var array
	 */
	private $postBackup = array();

	/**
	 * backup of $_GET
	 *
	 * @var array
	 */
	private $getBackup = array();

	/**
	 * the output of the module
	 *
	 * @var string
	 */
	private $output = '';

	public function setUp() {
		$this->backEndUserBackup = $GLOBALS['BE_USER'];
		$this->postBackup = $_POST;
		$this->getBackup = $_GET;
		$_POST = array();
		$_GET = array();

		$this->fixture = $this->getMock(
			$this->createAccessibleProxy(), array('output')
		);
		$this->fixture->expects($this->any())->method('output')
			->will($this->returnCallback(array($this, 'outputCallback')));
	}

	public function tearDown() {
		$this->fixture->__destruct();

		$_POST = $this->postBackup;
		$_GET = $this->getBackup;

		$GLOBALS['BE_USER'] = $this->backEndUserBackup;

		unset($this->fixture, $this->backEndUserBackup);
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
				'  public function getTestFinder() {' .
				'    return parent::getTestFinder();' .
				'  }' .
				'  public function runTests_render() {' .
				'    parent::runTests_render();' .
				'  }' .
				'  public function loadRequiredTestClasses(array $paths) {' .
				'    parent::loadRequiredTestClasses($paths);' .
				'  }' .
				'  public function isExtensionLoaded($extensionKey) {' .
				'    return parent::isExtensionLoaded($extensionKey);' .
				'  }' .
				'  public function createIconStyle($extensionKey) {' .
				'    return parent::createIconStyle($extensionKey);' .
				'  }' .
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
	public function getTestFinderReturnsTestFinderInstance() {
		$this->assertInstanceOf(
			'Tx_Phpunit_Service_TestFinder',
			$this->fixture->getTestFinder()
		);
	}

	/**
	 * @test
	 */
	public function mainForNoAdminBackEndUserShowsAdminRightsNeeded() {
		$GLOBALS['BE_USER']->user['admin'] = FALSE;

		$this->fixture->main();

		$this->assertContains(
			$GLOBALS['LANG']->getLL('admin_rights_needed'),
			$this->output
		);
	}

	/**
	 * @test
	 */
	public function mainForAdminBackEndUserRunsTests() {
		$GLOBALS['BE_USER']->user['admin'] = TRUE;

		$fixture = $this->getMock(
			$this->createAccessibleProxy(), array('output', 'runTests_render')
		);
		$fixture->expects($this->any())->method('output')
			->will($this->returnCallback(array($this, 'outputCallback')));
		$fixture->expects($this->once())->method('runTests_render');

		$fixture->main();
	}

	/**
	 * @test
	 */
	public function runTests_renderForEmptyCommandRendersIntro() {
		$fixture = $this->getMock(
			$this->createAccessibleProxy(),
			array('runTests_renderIntro', 'runTests_renderRunningTest', 'output')
		);
		$fixture->MOD_SETTINGS = array('extSel' => 'phpunit');

		$fixture->expects($this->once())->method('runTests_renderIntro');

		$_GET['command'] = '';

		$fixture->runTests_render();
	}

	/**
	 * @test
	 */
	public function runTests_renderForEmptyCommandNotRunsTests() {
		$fixture = $this->getMock(
			$this->createAccessibleProxy(),
			array('runTests_renderIntro', 'runTests_renderRunningTest', 'output')
		);
		$fixture->MOD_SETTINGS = array('extSel' => 'phpunit');

		$fixture->expects($this->never())->method('runTests_renderRunningTest');

		$_GET['command'] = '';

		$fixture->runTests_render();
	}

	/**
	 * @test
	 */
	public function runTests_renderForInvalidCommandRendersIntro() {
		$fixture = $this->getMock(
			$this->createAccessibleProxy(),
			array('runTests_renderIntro', 'runTests_renderRunningTest', 'output')
		);
		$fixture->MOD_SETTINGS = array('extSel' => 'phpunit');

		$fixture->expects($this->once())->method('runTests_renderIntro');

		$_GET['command'] = 'invalid';

		$fixture->runTests_render();
	}

	/**
	 * @test
	 */
	public function runTests_renderForInvalidCommandNotRunsTests() {
		$fixture = $this->getMock(
			$this->createAccessibleProxy(),
			array('runTests_renderIntro', 'runTests_renderRunningTest', 'output')
		);
		$fixture->MOD_SETTINGS = array('extSel' => 'phpunit');

		$fixture->expects($this->never())->method('runTests_renderRunningTest');

		$_GET['command'] = 'invalid';

		$fixture->runTests_render();
	}

	/**
	 * @test
	 */
	public function runTests_renderForRunAllTestsCommandRendersIntroAndTests() {
		$fixture = $this->getMock(
			$this->createAccessibleProxy(),
			array('runTests_renderIntro', 'runTests_renderRunningTest', 'output')
		);
		$fixture->MOD_SETTINGS = array('extSel' => 'phpunit');

		$fixture->expects($this->once())->method('runTests_renderIntro');
		$fixture->expects($this->once())->method('runTests_renderRunningTest');

		$_GET['command'] = 'runalltests';

		$fixture->runTests_render();
	}

	/**
	 * @test
	 */
	public function runTests_renderForRunTestCaseFileCommandRendersIntroAndTests() {
		$fixture = $this->getMock(
			$this->createAccessibleProxy(),
			array('runTests_renderIntro', 'runTests_renderRunningTest', 'output')
		);
		$fixture->MOD_SETTINGS = array('extSel' => 'phpunit');

		$fixture->expects($this->once())->method('runTests_renderIntro');
		$fixture->expects($this->once())->method('runTests_renderRunningTest');

		$_GET['command'] = 'runTestCaseFile';

		$fixture->runTests_render();
	}

	/**
	 * @test
	 */
	public function runTests_renderForRunSingleTestCommandRendersIntroAndTests() {
		$fixture = $this->getMock(
			$this->createAccessibleProxy(),
			array('runTests_renderIntro', 'runTests_renderRunningTest', 'output')
		);
		$fixture->MOD_SETTINGS = array('extSel' => 'phpunit');

		$fixture->expects($this->once())->method('runTests_renderIntro');
		$fixture->expects($this->once())->method('runTests_renderRunningTest');

		$_GET['command'] = 'runsingletest';

		$fixture->runTests_render();
	}

	/**
	 * @test
	 */
	public function loadRequiredTestClassesLoadsFileInFirstPath() {
		$this->fixture->loadRequiredTestClasses(
			array(
				t3lib_extMgm::extPath('phpunit') . 'Tests/BackEnd/Fixtures/' => array(
					'LoadMe.php',
				),
			)
		);

		$this->assertTrue(
			class_exists('Tx_Phpunit_BackEnd_Fixtures_LoadMe', FALSE)
		);
	}

	/**
	 * @test
	 */
	public function loadRequiredTestClassesLoadsSecondFileInFirstPath() {
		$this->fixture->loadRequiredTestClasses(
			array(
				t3lib_extMgm::extPath('phpunit') . 'Tests/BackEnd/Fixtures/' => array(
					'LoadMe.php',
					'LoadMeToo.php',
				),
			)
		);

		$this->assertTrue(
			class_exists('Tx_Phpunit_BackEnd_Fixtures_LoadMeToo', FALSE)
		);
	}

	/**
	 * @test
	 */
	public function loadRequiredTestClassesLoadsFileInSecondPath() {
		$this->fixture->loadRequiredTestClasses(
			array(
				t3lib_extMgm::extPath('phpunit') . 'Tests/BackEnd/Fixtures/' => array(
					'LoadMe.php',
				),
				t3lib_extMgm::extPath('phpunit') . 'Tests/Fixtures/' => array(
					'LoadMe.php',
				),
			)
		);

		$this->assertTrue(
			class_exists('Tx_Phpunit_Fixtures_LoadMe', FALSE)
		);
	}

	/**
	 * @test
	 */
	public function isExtensionLoadedEmptyKeyReturnsFalse() {
		$this->assertFalse(
			$this->fixture->isExtensionLoaded('')
		);
	}

	/**
	 * @test
	 */
	public function isExtensionLoadedForLoadedExtensionReturnsTrue() {
		$this->assertTrue(
			$this->fixture->isExtensionLoaded('phpunit')
		);
	}

	/**
	 * @test
	 */
	public function isExtensionLoadedForNotLoadedExtensionReturnsFalse() {
		$this->assertFalse(
			$this->fixture->isExtensionLoaded('not_loaded_extension')
		);
	}

	/**
	 * @test
	 */
	public function isExtensionLoadedForCoreWithExistingTestsReturnsTrue() {
		$testFinder = t3lib_div::makeInstance('Tx_Phpunit_Service_TestFinder');
		if (!$testFinder->hasCoreTests()) {
			$this->markTestSkipped('This test can only be run if the TYPO3 Core unit tests are present.');
		}

		$this->assertTrue(
			$this->fixture->isExtensionLoaded(Tx_Phpunit_TestableCode::CORE_KEY)
		);
	}

	/**
	 * @test
	 */
	public function isExtensionLoadedForCoreWithoutExistingTestsReturnsFalse() {
		$testFinder = t3lib_div::makeInstance('Tx_Phpunit_Service_TestFinder');
		if ($testFinder->hasCoreTests()) {
			$this->markTestSkipped('This test can only be run if no TYPO3 Core unit tests are present.');
		}

		$this->assertFalse(
			$this->fixture->isExtensionLoaded(Tx_Phpunit_TestableCode::CORE_KEY)
		);
	}

	/**
	 * @test
	 */
	public function createIconStyleForLoadedExtensionReturnsExtensionIcon() {
		$this->assertContains(
			'url(' . t3lib_extMgm::extRelPath('phpunit') . 'ext_icon.gif)',
			$this->fixture->createIconStyle('phpunit')
		);
	}

	/**
	 * @test
	 */
	public function createIconStyleForCoreReturnsTypo3Icon() {
		$testFinder = t3lib_div::makeInstance('Tx_Phpunit_Service_TestFinder');
		if (!$testFinder->hasCoreTests()) {
			$this->markTestSkipped('This test can only be run if the TYPO3 Core unit tests are present.');
		}

		$this->assertContains(
			'url(' . t3lib_extMgm::extRelPath('phpunit') . 'Resources/Public/Icons/Typo3.png)',
			$this->fixture->createIconStyle(Tx_Phpunit_TestableCode::CORE_KEY)
		);
	}

	/**
	 * @test
	 *
	 * @expectedException Tx_Phpunit_Exception_NoTestsDirectory
	 */
	public function createIconStyleForNotLoadedExtensionThrowsException() {
		$this->fixture->createIconStyle('not_loaded_extension');
	}

	/**
	 * @test
	 *
	 * @expectedException Tx_Phpunit_Exception_NoTestsDirectory
	 */
	public function createIconStyleForEmptyExtensionKeyThrowsException() {
		$this->fixture->createIconStyle('');
	}

	/**
	 * @test
	 */
	public function outputOutputsOutput() {
		$className = $this->createAccessibleProxy();
		$fixture = new $className();

		$output = 'Hello world!';

		ob_start();
		$fixture->output($output);

		$this->assertSame(
			$output,
			ob_get_contents()
		);

		ob_end_clean();
	}
}
?>