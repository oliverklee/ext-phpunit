<?php
/***************************************************************
 *  Copyright notice
 *
 * (c) 2012 Nicole Cordes <nicole.cordes@googlemail.com>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * This view helper renders the extension selector box.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Nicole Cordes <nicole.cordes@googlemail.com>
 */
class Tx_Phpunit_ViewHelpers_ExtensionSelectorViewHelper extends Tx_Phpunit_ViewHelpers_AbstractSelectorViewHelper {
	/**
	 * Renders the content of the view helper and pushes it to the output service.
	 *
	 * @return void
	 */
	public function render() {
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
	protected function renderForm($formContent) {
		$formContentWithAdditionalElements = $formContent .
			$this->renderHiddenFields() . $this->renderSubmitButton($GLOBALS['LANG']->getLL('run_all_tests'));

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
	protected function renderHiddenFields() {
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
	protected function renderSubmitButton($label) {
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
	protected function renderSelect() {
		$options = $this->getOptions();

		$selectedExtensionStyle = '';

		$renderedOptionTags = array();
		foreach ($options as $option) {
			if (isset($option['selected']) && $option['selected'] === 'selected') {
				$selectedExtensionStyle = $option['style'];
			}
			if ($option['value'] === Tx_Phpunit_Testable::ALL_EXTENSIONS) {
				$optionValue = $GLOBALS['LANG']->getLL('all_extensions');
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
				'onchange' => 'jumpToUrl(\'' . $this->action . '&' .
					Tx_Phpunit_Interface_Request::PARAMETER_NAMESPACE .
					'[' . Tx_Phpunit_Interface_Request::PARAMETER_KEY_TESTABLE . ']=' .
					'\'+this.options[this.selectedIndex].value,this);',
				'style' => $selectedExtensionStyle,
			),
			implode(LF, $renderedOptionTags)
		);
	}

	/**
	 * Gets all options rendered as an array
	 *
	 * @return array<array> all options, will not be empty
	 */
	protected function getOptions() {
		$options = array();

		$allExtensionOption = array(
			'class' => 'alltests',
			'value' => 'uuall',
		);
		if ($this->isOptionSelected(Tx_Phpunit_Testable::ALL_EXTENSIONS)) {
			$allExtensionOption['selected'] = 'selected';
		}
		$options[] = $allExtensionOption;

		/** @var $testable Tx_Phpunit_Testable */
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
	 * @return boolean whether $option is the selected option
	 */
	protected function isOptionSelected($option) {
		return ($this->userSettingService->getAsString(Tx_Phpunit_Interface_Request::PARAMETER_KEY_TESTABLE) === $option);
	}
}
?>