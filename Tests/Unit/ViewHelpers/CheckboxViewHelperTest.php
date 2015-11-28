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
 * Test case for Tx_Phpunit_ViewHelpers_CheckboxViewHelper.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Felix Rauch <rauch@skaiamail.de>
 */
class Tx_Phpunit_Tests_Unit_ViewHelpers_CheckboxViewHelperTest extends Tx_Phpunit_TestCase
{
    /**
     * @test
     */
    public function classIsSubclassOfAbstractTagViewHelper()
    {
        self::assertInstanceOf(
            'Tx_Phpunit_ViewHelpers_AbstractTagViewHelper',
            new Tx_Phpunit_ViewHelpers_CheckboxViewHelper()
        );
    }

    /**
     * @test
     */
    public function viewHelperRendersAnInputTagOfTypeCheckbox()
    {
        $subject = new Tx_Phpunit_ViewHelpers_CheckboxViewHelper();

        self::assertRegExp(
            '/^<input[^$]+type="checkbox"[^$]*\/>$/',
            $subject->render()
        );
    }

    /**
     * @test
     */
    public function viewHelperRendersAnInputTagWithValueGivenInConstructor()
    {
        $subject = new Tx_Phpunit_ViewHelpers_CheckboxViewHelper('test');

        self::assertRegExp(
            '/^<input[^$]+value="test"[^$]*\/>$/',
            $subject->render()
        );
    }

    /**
     * @test
     */
    public function viewHelperRendersAnInputTagWithAdditionalAttributesGivenInConstructor()
    {
        $subject = new Tx_Phpunit_ViewHelpers_CheckboxViewHelper(
            '',
            array(
                'foo' => 'bar'
            )
        );

        self::assertRegExp(
            '/^<input[^$]+foo="bar"[^$]*\/>$/',
            $subject->render()
        );
    }

    /**
     * @test
     */
    public function viewHelperRendersAnInputTagWithAdditionalAttributesAddedAfterInstantiation()
    {
        $subject = new Tx_Phpunit_ViewHelpers_CheckboxViewHelper(
            '',
            array(
                'some' => 'attribute'
            )
        );

        $subject->addAdditionalAttributes(
            array(
                'foo' => 'bar'
            )
        );

        self::assertRegExp(
            '/^<input[^$]+foo="bar"[^$]*\/>$/',
            $subject->render()
        );
    }

    /**
     * @test
     */
    public function reservedAttributeTypeIsPreserved()
    {
        $subject = new Tx_Phpunit_ViewHelpers_CheckboxViewHelper();

        // This should have no effect on the rendered tag:
        $subject->setAdditionalAttributes(
            array(
                'type' => 'will be overridden'
            )
        );

        self::assertRegExp(
            '/^<input[^$]+type="checkbox"[^$]*\/>$/',
            $subject->render()
        );
    }

    /**
     * @test
     */
    public function reservedAttributeValueIsPreserved()
    {
        $subject = new Tx_Phpunit_ViewHelpers_CheckboxViewHelper('test');

        // This should have no effect on the rendered tag:
        $subject->setAdditionalAttributes(
            array(
                'value' => 'will be overridden'
            )
        );

        self::assertRegExp(
            '/^<input[^$]+value="test"[^$]*\/>$/',
            $subject->render()
        );
    }

    /**
     * @test
     */
    public function addingReservedAttributeToAdditionalAttributesDoesNotGenerateDuplicateAttributes()
    {
        $subject = new Tx_Phpunit_ViewHelpers_CheckboxViewHelper('test');

        $subject->setAdditionalAttributes(
            array(
                'value' => 'another'
            )
        );

        // Only one match should be found
        self::assertSame(
            1,
            preg_match_all('/(value="test"|value="another")/', $subject->render(), $matches)
        );
    }

    /**
     * @test
     */
    public function addingAdditionalAttributesWillPreserveExistingAdditionalAttributesWithDifferentKeys()
    {
        $subject = new Tx_Phpunit_ViewHelpers_CheckboxViewHelper(
            '',
            array(
                'mustBe' => 'preserved'
            )
        );

        $subject->addAdditionalAttributes(
            array(
                'foo' => 'bar'
            )
        );

        self::assertContains(
            'mustBe="preserved"',
            $subject->render()
        );
    }

    /**
     * @test
     */
    public function changingTheValueAfterInstantiationIsPossible()
    {
        $subject = new Tx_Phpunit_ViewHelpers_CheckboxViewHelper('oldValue');

        $subject->setValue('newValue');

        self::assertContains(
            'value="newValue"',
            $subject->render()
        );
    }
}