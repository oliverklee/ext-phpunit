<?php
namespace OliverKlee\Phpunit\Tests\Unit\ViewHelpers;

use OliverKlee\Phpunit\Tests\Unit\ViewHelpers\Fixtures\TestingTagViewHelper;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

/**
 * Testcase.
 *
 * @author Felix Rauch <rauch@skaiamail.de>
 */
class AbstractTagViewHelperTest extends \Tx_Phpunit_TestCase
{
    protected function setUp()
    {
        if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 8000000) {
            self::markTestSkipped('The BE module is not available in TYPO3 CMS >= 8.');
        }
    }

    /**
     * @test
     */
    public function classIsSubclassOfAbstractViewHelper()
    {
        $subject = new TestingTagViewHelper(
            'tag',
            [],
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
            [],
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
            [],
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
            [],
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
            [
                'value' => 'test',
                'empty' => '',
            ],
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
            [
                'value' => 'test',
                'empty' => '',
            ],
            'Test'
        );

        self::assertSame(
            '<tag value="test" empty="">Test</tag>',
            $subject->render()
        );
    }

    /**
     * @test
     */
    public function renderingTagWithAnEmptyTagNameCausesAnInvalidArgumentException()
    {
        $subject = new TestingTagViewHelper(
            '',
            [],
            ''
        );

        $this->expectException(\InvalidArgumentException::class);

        $subject->render();
    }

    /**
     * @test
     */
    public function renderingTagWithAnAttributeWithANonStringKeyCausesAnInvalidArgumentException()
    {
        $subject = new TestingTagViewHelper(
            'tag',
            [
                // This is a valid key
                'thisIsAValidKey' => 'value',
                // This is an invalid key
                4711 => 'value',
            ],
            ''
        );

        $this->expectException(\InvalidArgumentException::class);

        $subject->render();
    }

    /**
     * @test
     */
    public function renderingTagWithAnAttributeWithAnEmptyStringAsKeyCausesAnInvalidArgumentException()
    {
        $subject = new TestingTagViewHelper(
            'tag',
            [
                // This is a valid key
                'thisIsAValidKey' => 'value',
                // This is an invalid key
                '' => 'value',
            ],
            ''
        );

        $this->expectException(\InvalidArgumentException::class);

        $subject->render();
    }
}
