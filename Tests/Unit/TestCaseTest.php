<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2007-2013 Kasper Ligaard (kasperligaard@gmail.com)
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

require_once(t3lib_extMgm::extPath('phpunit') .  'Tests/Unit/Fixtures/ProtectedClass.php');

/**
 * Test case.
 *
 * @author Kasper Ligaard <kasperligaard@gmail.com>
 * @author Nicole Cordes <nicole.cordes@googlemail.com>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Phpunit_TestCaseTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Phpunit_Tests_Fixtures_ProtectedClass
	 */
	private $proctectedClassInstance = NULL;

	/**
	 * @var Tx_Phpunit_BackEnd_Module|PHPUnit_Framework_MockObject_MockObject|Tx_Phpunit_Tests_Fixtures_ProtectedClass
	 */
	private $mock = NULL;

	/**
	 * @var Tx_Phpunit_BackEnd_Module|PHPUnit_Framework_MockObject_MockObject|Tx_Phpunit_Interface_AccessibleObject
	 */
	private $accessibleMock = NULL;

	/**
	 * Backup of static property of the accessible mock.
	 *
	 * @var string
	 */
	private $staticProperty = '';

	public function setUp() {
		$this->proctectedClassInstance = new Tx_Phpunit_Tests_Fixtures_ProtectedClass();
		$this->mock = $this->getMock('Tx_Phpunit_Tests_Fixtures_ProtectedClass', array('dummy'));
		$this->accessibleMock = $this->getAccessibleMock('Tx_Phpunit_Tests_Fixtures_ProtectedClass', array('dummy'));
		$this->staticProperty = Tx_Phpunit_Tests_Fixtures_ProtectedClass::getStaticProperty();
	}

	public function tearDown() {
		Tx_Phpunit_Tests_Fixtures_ProtectedClass::setStaticProperty($this->staticProperty);
		unset($this->proctectedClassInstance, $this->mock, $this->accessibleMock);
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 */
	public function getAccessibleMockWithEmptyClassNameThrowsException() {
		$this->getAccessibleMock('');
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 */
	public function callForEmptyMethodNameInAccessibleMockObjectThrowsException() {
		$this->accessibleMock->_call('');
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 */
	public function callRefForEmptyMethodNameInAccessibleMockObjectThrowsException() {
		$this->accessibleMock->_callRef('');
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 */
	public function setForEmptyPropertyNameInAccessibleMockObjectThrowsException() {
		$this->accessibleMock->_set('', '');
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 */
	public function setRefForEmptyPropertyNameInAccessibleMockObjectThrowsException() {
		$value = '';
		$this->accessibleMock->_setRef('', $value);
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 */
	public function setStaticForEmptyPropertyNameInAccessibleMockObjectThrowsException() {
		$this->accessibleMock->_setStatic('', '');
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 */
	public function getForEmptyPropertyNameInAccessibleMockObjectThrowsException() {
		$this->accessibleMock->_get('');
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 */
	public function getStaticForEmptyPropertyNameInAccessibleMockObjectThrowsException() {
		$this->accessibleMock->_getStatic('');
	}

	/**
	 * @test
	 */
	public function protectedPropertyForFixtureIsNotDirectlyAccessible() {
		$this->assertFalse(
			in_array('protectedProperty', get_object_vars($this->proctectedClassInstance))
		);
	}

	/**
	 * @test
	 */
	public function protectedStaticPropertyForFixtureIsNotDirectlyAccessible() {
		$this->assertFalse(
			array_key_exists('protectedStaticProperty', get_class_vars(get_class($this->proctectedClassInstance)))
		);
	}

	/**
	 * @test
	 */
	public function publicPropertyForFixtureIsDirectlyAccessible() {
		$this->assertSame(
			'This is a public property.',
			$this->proctectedClassInstance->publicProperty
		);
	}

	/**
	 * @test
	 */
	public function protectedMethodForFixtureIsNotDirectlyCallable() {
		$this->assertFalse(
			is_callable(array($this->proctectedClassInstance, 'protectedMethod'))
		);
	}

	/**
	 * @test
	 */
	public function publicMethodForFixtureIsDirectlyCallable() {
		$this->assertTrue(
			is_callable(array($this->proctectedClassInstance, 'publicMethod'))
		);
	}

	/**
	 * @test
	 */
	public function protectedPropertyForMockObjectIsNotDirectlyAccessible() {
		$this->assertFalse(
			in_array('protectedProperty', get_object_vars($this->mock))
		);
	}

	/**
	 * @test
	 */
	public function protectedStaticPropertyForMockObjectIsNotDirectlyAccessible() {
		$this->assertFalse(
			array_key_exists('protectedStaticProperty', get_class_vars(get_class($this->mock)))
		);
	}

	/**
	 * @test
	 */
	public function publicPropertyForMockObjectIsDirectlyAccessible() {
		$this->assertSame(
			'This is a public property.',
			$this->mock->publicProperty
		);
	}

	/**
	 * @test
	 */
	public function protectedMethodForMockObjectIsNotDirectlyCallable() {
		$this->assertFalse(
			is_callable(array($this->mock, 'protectedMethod'))
		);
	}

	/**
	 * @test
	 */
	public function publicMethodForMockObjectIsDirectlyCallable() {
		$this->assertTrue(
			is_callable(array($this->mock, 'publicMethod'))
		);
	}

	/**
	 * @test
	 */
	public function protectedPropertyForAccessibleMockObjectIsDirectlyAccessible() {
		$this->assertSame(
			'This is a protected property.',
			$this->accessibleMock->_get('protectedProperty')
		);
	}

	/**
	 * @test
	 */
	public function publicPropertyForAccessibleMockObjectIsDirectlyAccessible() {
		$this->assertSame(
			'This is a public property.',
			$this->accessibleMock->_get('publicProperty')
		);
	}

	/**
	 * @test
	 */
	public function protectedStaticPropertyForAccessibleMockObjectIsDirectlyAccessible() {
		$this->assertSame(
			'This is a protected static property.',
			$this->accessibleMock->_getStatic('protectedStaticProperty')
		);
	}

	/**
	 * @test
	 */
	public function protectedStaticPropertyForAccessibleMockObjectCanBeSet() {
		$newValue = 'New value ' . microtime();
		$this->accessibleMock->_setStatic('protectedStaticProperty', $newValue);

		$this->assertSame(
			$newValue,
			$this->accessibleMock->_getStatic('protectedStaticProperty')
		);
	}

	/**
	 * @test
	 */
	public function protectedStaticPropertyForAccessibleMockObjectCanSetOriginal() {
		$newValue = 'New value ' . microtime();
		$this->accessibleMock->_setStatic('protectedStaticProperty', $newValue);

		$this->assertSame(
			$newValue,
			Tx_Phpunit_Tests_Fixtures_ProtectedClass::getStaticProperty()
		);
	}

	/**
	 * @test
	 */
	public function protectedMethodForAccessibleMockObjectIsDirectlyCallable() {
		$this->assertTrue(
			$this->accessibleMock->_callRef('protectedMethod')
		);
	}

	/**
	 * @test
	 */
	public function publicMethodForAccessibleMockObjectIsDirectlyCallable() {
		$this->assertTrue(
			$this->accessibleMock->_call('publicMethod')
		);
	}

	/**
	 * @test
	 */
	public function callCanPassEightParametersToMethod() {
		$this->assertSame(
			'8: 1, 2, 3, 4, 5, 6, 7, 8',
			$this->accessibleMock->_call('argumentChecker', 1, 2, 3, 4, 5, 6, 7, 8)
		);
	}

	/**
	 * @test
	 */
	public function callCanPassNineParametersToMethod() {
		$this->assertSame(
			'9: 1, 2, 3, 4, 5, 6, 7, 8, 9',
			$this->accessibleMock->_call('argumentChecker', 1, 2, 3, 4, 5, 6, 7, 8, 9)
		);
	}

	/**
	 * @test
	 */
	public function callCanPassTenParametersToMethod() {
		$this->assertSame(
			'10: 1, 2, 3, 4, 5, 6, 7, 8, 9, 10',
			$this->accessibleMock->_call('argumentChecker', 1, 2, 3, 4, 5, 6, 7, 8, 9, 10)
		);
	}

	/**
	 * @test
	 */
	public function callRefCanCallMethodWithoutParameter() {
		$this->assertSame(
			'0: ',
			$this->accessibleMock->_callRef(
				'argumentChecker'
			)
		);
	}

	/**
	 * @test
	 */
	public function callRefCanPassOneParametersToMethod() {
		$parameter = 1;
		$this->assertSame(
			'1: 1',
			$this->accessibleMock->_callRef(
				'argumentChecker',
				$parameter
			)
		);
	}

	/**
	 * @test
	 */
	public function callRefCanPassTwoParametersToMethod() {
		$parameter = 1;
		$this->assertSame(
			'2: 1, 1',
			$this->accessibleMock->_callRef(
				'argumentChecker',
				$parameter, $parameter
			)
		);
	}

	/**
	 * @test
	 */
	public function callRefCanPassThreeParametersToMethod() {
		$parameter = 1;
		$this->assertSame(
			'3: 1, 1, 1',
			$this->accessibleMock->_callRef(
				'argumentChecker',
				$parameter, $parameter, $parameter
			)
		);
	}

	/**
	 * @test
	 */
	public function callRefCanPassFourParametersToMethod() {
		$parameter = 1;
		$this->assertSame(
			'4: 1, 1, 1, 1',
			$this->accessibleMock->_callRef(
				'argumentChecker',
				$parameter, $parameter, $parameter, $parameter
			)
		);
	}

	/**
	 * @test
	 */
	public function callRefCanPassFiveParametersToMethod() {
		$parameter = 1;
		$this->assertSame(
			'5: 1, 1, 1, 1, 1',
			$this->accessibleMock->_callRef(
				'argumentChecker',
				$parameter, $parameter, $parameter, $parameter, $parameter
			)
		);
	}

	/**
	 * @test
	 */
	public function callRefCanPassSixParametersToMethod() {
		$parameter = 1;
		$this->assertSame(
			'6: 1, 1, 1, 1, 1, 1',
			$this->accessibleMock->_callRef(
				'argumentChecker',
				$parameter, $parameter, $parameter, $parameter, $parameter, $parameter
			)
		);
	}

	/**
	 * @test
	 */
	public function callRefCanPassSevenParametersToMethod() {
		$parameter = 1;
		$this->assertSame(
			'7: 1, 1, 1, 1, 1, 1, 1',
			$this->accessibleMock->_callRef(
				'argumentChecker',
				$parameter, $parameter, $parameter, $parameter, $parameter, $parameter, $parameter
			)
		);
	}

	/**
	 * @test
	 */
	public function callRefCanPassEightParametersToMethod() {
		$parameter = 1;
		$this->assertSame(
			'8: 1, 1, 1, 1, 1, 1, 1, 1',
			$this->accessibleMock->_callRef(
				'argumentChecker',
				$parameter, $parameter, $parameter, $parameter, $parameter, $parameter, $parameter, $parameter
			)
		);
	}

	/**
	 * @test
	 */
	public function callRefCanPassNineParametersToMethod() {
		$parameter = 1;
		$this->assertSame(
			'9: 1, 1, 1, 1, 1, 1, 1, 1, 1',
			$this->accessibleMock->_callRef(
				'argumentChecker',
				$parameter, $parameter, $parameter, $parameter, $parameter, $parameter, $parameter, $parameter, $parameter
			)
		);
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 */
	public function callRefForTenParametersThrowsException() {
		$this->accessibleMock->_callRef(
			'argumentChecker',
			$parameter, $parameter, $parameter, $parameter, $parameter, $parameter, $parameter, $parameter, $parameter, $parameter
		);
	}
}
?>