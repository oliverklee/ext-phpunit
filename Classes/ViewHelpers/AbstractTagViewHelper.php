<?php

/**
 * Tag based view helper.
 * Should be used as the base class for all view helpers which output simple tags, as it provides some useful
 * convenience methods.
 *
 * @deprecated will be removed for PHPUnit 6.
 *
 * @author Felix Rauch <rauch@skaiamail.de>
 */
abstract class Tx_Phpunit_ViewHelpers_AbstractTagViewHelper extends \Tx_Phpunit_ViewHelpers_AbstractViewHelper
{
    /**
     * Renders any HTML tag with its own parameter either around some content.
     *
     * If the content is empty, the tag gets rendered as a self-closing tag.
     *
     * @param string $tagName
     * @param string[] $attributes
     *        use HTML attribute as key, must not be empty
     *        use attribute value as array value, might be empty
     * @param string $content
     *
     * @return string the rendered HTML tag
     *
     * @throws \InvalidArgumentException if the given tagName is empty
     */
    protected function renderTag($tagName, array $attributes = [], $content = '')
    {
        if (empty($tagName)) {
            throw new \InvalidArgumentException('$tagName must not be NULL or empty.', 1343763729);
        }

        $output = '<' . htmlspecialchars($tagName);

        foreach ($attributes as $key => $value) {
            if (!is_string($key) || $key === '') {
                throw new \InvalidArgumentException('Attribute key must not be empty.', 1448657422);
            }
            $output .= ' ' . htmlspecialchars($key) . '="' . htmlspecialchars($value) . '"';
        }

        if ($content !== '') {
            $output .= '>' . $content . '</' . htmlspecialchars($tagName) . '>';
        } else {
            $output .= ' />';
        }

        return $output;
    }
}
