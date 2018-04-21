<?php
namespace OliverKlee\Phpunit\Tests\Unit\ViewHelpers\Fixtures;

/**
 * Fixture class.
 *
 * This testing view helper renders a tag with the given arguments (see the parent class)
 *
 * @author Felix Rauch <rauch@skaiamail.de>
 */
class TestingTagViewHelper extends \Tx_Phpunit_ViewHelpers_AbstractTagViewHelper
{
    /**
     * @var string
     */
    protected $tagName = '';

    /**
     * @var string[]
     */
    protected $attributes = [];

    /**
     * @var string
     */
    protected $content = '';

    /**
     * Constructor.
     *
     * This class must be given all required information when instantiating.
     *
     * @param string $tagName
     * @param string[] $attributes
     * @param string $content
     */
    public function __construct($tagName = 'tag', array $attributes = [], $content = '')
    {
        $this->tagName = $tagName;
        $this->attributes = $attributes;
        $this->content = $content;
    }

    /**
     * This method renders the tag.
     *
     * @return string
     */
    public function render()
    {
        return $this->renderTag($this->tagName, $this->attributes, $this->content);
    }
}
