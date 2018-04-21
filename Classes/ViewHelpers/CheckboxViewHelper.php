<?php

/**
 * This view helper renders an input field of type checkbox.
 *
 * @deprecated will be removed for PHPUnit 6.
 *
 * @author Felix Rauch <rauch@skaiamail.de>
 */
class Tx_Phpunit_ViewHelpers_CheckboxViewHelper extends \Tx_Phpunit_ViewHelpers_AbstractTagViewHelper
{
    /**
     * @var string
     */
    protected $tagName = 'input';

    /**
     * Any additional attributes can be set here. They are resolved as key="value" in the resulting tag.
     *
     * Note: The keys "type" and "value" are reserved and will be overridden by the ViewHelper properties.
     *
     * @var string[]|int[]
     */
    protected $additionalAttributes = [];

    /**
     * @var string
     */
    protected $type = 'checkbox';

    /**
     * According to HTML spec, an <input> element of type "checkbox" must have a value attribute (even if it is empty).
     *
     * @var string
     */
    protected $value = '';

    /**
     * Constructor.
     *
     * @param string $value
     * @param string[]|int[] $additionalAttributes
     */
    public function __construct($value = '', array $additionalAttributes = [])
    {
        $this->value = $value;
        $this->additionalAttributes = $additionalAttributes;
    }

    /**
     * Renders the input field with the set attributes and value
     *
     * @return string
     */
    public function render()
    {
        $attributes = array_merge(
            $this->additionalAttributes,
            [
                'type' => $this->type,
                'value' => $this->value,
            ]
        );

        return $this->renderTag(
            $this->tagName,
            $attributes
        );
    }

    /**
     * @return string[]|int[]
     */
    public function getAdditionalAttributes()
    {
        return $this->additionalAttributes;
    }

    /**
     * @param string[]|int[] $additionalAttributes
     */
    public function setAdditionalAttributes(array $additionalAttributes)
    {
        $this->additionalAttributes = $additionalAttributes;
    }

    /**
     * Adds the given array of additional attributes to the existing additional attributes.
     * If keys are duplicated, this function will override the existing key.
     *
     * @param string[]|int[] $additionalAttributes
     */
    public function addAdditionalAttributes(array $additionalAttributes)
    {
        $this->additionalAttributes = array_merge($this->additionalAttributes, $additionalAttributes);
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
}
