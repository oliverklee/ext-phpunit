<?php
namespace OliverKlee\Phpunit\Tests\Unit\ViewHelpers;

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

use OliverKlee\Phpunit\Tests\Unit\ViewHelpers\Fixtures\TestingTagViewHelper;

/**
 * Testcase.
 *
 * @author Felix Rauch <rauch@skaiamail.de>
 */
class AbstractTagViewHelperTest extends \Tx_Phpunit_TestCase
{

    /**
     * @test
     */
    public function classIsSubclassOfAbstractViewHelper()
    {
        $subject = new TestingTagViewHelper(
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
        $subject = new TestingTagViewHelper(
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
        $subject = new TestingTagViewHelper(
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
        $subject = new TestingTagViewHelper(
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
        $subject = new TestingTagViewHelper(
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
        $subject = new TestingTagViewHelper(
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
     * @expectedException \InvalidArgumentException
     */
    public function renderingTagWithAnEmptyTagNameCausesAnInvalidArgumentException()
    {
        $subject = new TestingTagViewHelper(
            '',
            array(),
            ''
        );

        $subject->render();
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function renderingTagWithAnAttributeWithANonStringKeyCausesAnInvalidArgumentException()
    {
        $subject = new TestingTagViewHelper(
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
     * @expectedException \InvalidArgumentException
     */
    public function renderingTagWithAnAttributeWithAnEmptyStringAsKeyCausesAnInvalidArgumentException()
    {
        $subject = new TestingTagViewHelper(
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
