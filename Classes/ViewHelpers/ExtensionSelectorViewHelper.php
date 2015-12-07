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
 * This view helper renders the extension selector box.
 *
 * @author Nicole Cordes <nicole.cordes@googlemail.com>
 */
class Tx_Phpunit_ViewHelpers_ExtensionSelectorViewHelper extends Tx_Phpunit_ViewHelpers_AbstractSelectorViewHelper
{
    /**
     * Renders the content of the view helper and pushes it to the output service.
     *
     * @return void
     */
    public function render()
    {
        $content = $this->renderForm($this->renderSelect());

        $this->outputService->output($content);
    }

    /**
     * Renders the form with submit button around some content.
     *
     * @param string $formContent
     *
     * @return string the final form
     */
    protected function renderForm($formContent)
    {
        $formContentWithAdditionalElements = $formContent .
            $this->renderHiddenFields() . $this->renderSubmitButton($this->translate('run_all_tests'));

        $formContentWithinParagraph = $this->renderTag('p', array(), $formContentWithAdditionalElements);

        return $this->renderTag(
            'form',
            array(
                'action' => $this->action,
                'method' => 'post',
            ),
            $formContentWithinParagraph
        );
    }

    /**
     * Renders some (hidden) fields for the form.
     *
     * @return string the rendered fields, will not be empty
     */
    protected function renderHiddenFields()
    {
        return $this->renderTag(
            'input',
            array(
                'name' => Tx_Phpunit_Interface_Request::PARAMETER_NAMESPACE .
                    '[' . Tx_Phpunit_Interface_Request::PARAMETER_KEY_COMMAND . ']',
                'type' => 'hidden',
                'value' => 'runalltests',
            )
        );
    }

    /**
     * Renders the submit button for the form.
     *
     * @param string $label the label for the button, must not be empty
     *
     * @return string the rendered button tag, will not be empty
     */
    protected function renderSubmitButton($label)
    {
        return $this->renderTag(
            'button',
            array(
                'accesskey' => 'a',
                'name' => Tx_Phpunit_Interface_Request::PARAMETER_NAMESPACE .
                    '[' . Tx_Phpunit_Interface_Request::PARAMETER_KEY_EXECUTE . ']',
                'type' => 'submit',
                'value' => 'run',
            ),
            $label
        );
    }

    /**
     * Renders the select box as HTML.
     *
     * @return string the rendered select tag
     */
    protected function renderSelect()
    {
        $options = $this->getOptions();

        $selectedExtensionStyle = '';

        $renderedOptionTags = array();
        foreach ($options as $option) {
            if (isset($option['selected']) && $option['selected'] === 'selected') {
                $selectedExtensionStyle = $option['style'];
            }
            if ($option['value'] === Tx_Phpunit_Testable::ALL_EXTENSIONS) {
                $optionValue = $this->translate('all_extensions');
            } else {
                $optionValue = $option['value'];
            }
            $renderedOptionTags[] = $this->renderTag('option', $option, $optionValue);
        }

        return $this->renderTag(
            'select',
            array(
                'name' => Tx_Phpunit_Interface_Request::PARAMETER_NAMESPACE .
                    '[' . Tx_Phpunit_Interface_Request::PARAMETER_KEY_TESTABLE . ']',
                'onchange' => 'document.location = \'' . $this->action . '&' .
                    Tx_Phpunit_Interface_Request::PARAMETER_NAMESPACE .
                    '[' . Tx_Phpunit_Interface_Request::PARAMETER_KEY_TESTABLE . ']=' .
                    '\'+this.options[this.selectedIndex].value;',
                'style' => $selectedExtensionStyle,
            ),
            implode(LF, $renderedOptionTags)
        );
    }

    /**
     * Gets all options rendered as an array
     *
     * @return array[] all options, will not be empty
     */
    protected function getOptions()
    {
        $options = array();

        $allExtensionOption = array(
            'class' => 'alltests',
            'value' => Tx_Phpunit_Testable::ALL_EXTENSIONS,
        );
        if ($this->isOptionSelected(Tx_Phpunit_Testable::ALL_EXTENSIONS)) {
            $allExtensionOption['selected'] = 'selected';
        }
        $options[] = $allExtensionOption;

        /** @var Tx_Phpunit_Testable $testable */
        foreach ($this->testFinder->getTestablesForEverything() as $testable) {
            $extensionOption = array(
                'style' => $this->createIconStyle($testable->getKey()),
                'value' => $testable->getKey(),
            );
            if ($this->isOptionSelected($testable->getKey())) {
                $extensionOption['selected'] = 'selected';
            }

            $options[] = $extensionOption;
        }

        return $options;
    }

    /**
     * Checks whether $option is the selected option.
     *
     * @param string $option the option to check, must not be empty
     *
     * @return bool whether $option is the selected option
     */
    protected function isOptionSelected($option)
    {
        return ($this->userSettingService->getAsString(Tx_Phpunit_Interface_Request::PARAMETER_KEY_TESTABLE) === $option);
    }
}
