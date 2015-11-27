<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Test case for Tx_Phpunit_ViewHelpers_AbstractTagViewHelper.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Felix Rauch <rauch@skaiamail.de>
 */
class Tx_Phpunit_Tests_Unit_ViewHelpers_AbstractTagViewHelperTest extends Tx_Phpunit_TestCase
{

    /**
     * @test
     */
    public function classIsSubclassOfAbstractViewHelper()
    {
        $subject = new Tx_Phpunit_Tests_Unit_ViewHelpers_Fixtures_TestingTagViewHelper(
            'tag',
            array(),
            ''
        );

        self::assertInstanceOf(
            'Tx_Phpunit_ViewHelpers_AbstractViewHelper',
            $subject
        );
    }

    /**
     * @test
     */
    public function fixtureClassUsedForTestingIsASubclassOfAbstractTagViewHelper()
    {
        $subject = new Tx_Phpunit_Tests_Unit_ViewHelpers_Fixtures_TestingTagViewHelper(
            'tag',
            array(),
            ''
        );

        self::assertInstanceOf(
            'Tx_Phpunit_ViewHelpers_AbstractTagViewHelper',
            $subject
        );
    }

    /**
     * @test
     */
    public function renderingTagWithoutContentCreatesSelfClosingTag()
    {
        $subject = new Tx_Phpunit_Tests_Unit_ViewHelpers_Fixtures_TestingTagViewHelper(
            'tag',
            array(),
            ''
        );

        self::assertSame(
            '<tag />',
            $subject->render()
        );
    }

    /**
     * @test
     */
    public function renderingTagWithContentCreatesTagWithContent()
    {
        $subject = new Tx_Phpunit_Tests_Unit_ViewHelpers_Fixtures_TestingTagViewHelper(
            'tag',
            array(),
            'Test'
        );

        self::assertSame(
            '<tag>Test</tag>',
            $subject->render()
        );
    }

    /**
     * @test
     */
    public function renderingTagWithAttributesCreatesTagWithAttributes()
    {
        $subject = new Tx_Phpunit_Tests_Unit_ViewHelpers_Fixtures_TestingTagViewHelper(
            'tag',
            array(
                'value' => 'test',
                'empty' => ''
            ),
            ''
        );

        self::assertSame(
            '<tag value="test" empty="" />',
            $subject->render()
        );
    }

    /**
     * @test
     */
    public function renderingTagWithAttributesAndContentCreatesTagWithAttributesAndContent()
    {
        $subject = new Tx_Phpunit_Tests_Unit_ViewHelpers_Fixtures_TestingTagViewHelper(
            'tag',
            array(
                'value' => 'test',
                'empty' => ''
            ),
            'Test'
        );

        self::assertSame(
            '<tag value="test" empty="">Test</tag>',
            $subject->render()
        );
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function renderingTagWithAnEmptyTagNameCausesAnInvalidArgumentException()
    {
        $subject = new Tx_Phpunit_Tests_Unit_ViewHelpers_Fixtures_TestingTagViewHelper(
            '',
            array(),
            ''
        );

        $subject->render();
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function renderingTagWithAnAttributeWithANonStringKeyCausesAnInvalidArgumentException()
    {
        $subject = new Tx_Phpunit_Tests_Unit_ViewHelpers_Fixtures_TestingTagViewHelper(
            'tag',
            array(
                // This is a valid key
                'thisIsAValidKey' => 'value',
                // This is an invalid key
                4711 => 'value'
            ),
            ''
        );

        $subject->render();
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function renderingTagWithAnAttributeWithAnEmptyStringAsKeyCausesAnInvalidArgumentException()
    {
        $subject = new Tx_Phpunit_Tests_Unit_ViewHelpers_Fixtures_TestingTagViewHelper(
            'tag',
            array(
                // This is a valid key
                'thisIsAValidKey' => 'value',
                // This is an invalid key
                '' => 'value'
            ),
            ''
        );

        $subject->render();
    }
}