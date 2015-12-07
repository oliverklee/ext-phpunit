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
 * This class is the base class for all view helpers which render some select boxes.
 *
 * @author Nicole Cordes <nicole.cordes@googlemail.com>
 */
abstract class Tx_Phpunit_ViewHelpers_AbstractSelectorViewHelper extends Tx_Phpunit_ViewHelpers_AbstractTagViewHelper
{
    /**
     * @var Tx_Phpunit_Interface_UserSettingsService
     */
    protected $userSettingService = null;

    /**
     * @var Tx_Phpunit_Service_TestFinder
     */
    protected $testFinder = null;

    /**
     * @var string
     */
    protected $action = '';

    /**
     * The destructor.
     */
    public function __destruct()
    {
        unset($this->userSettingService, $this->testFinder);
        parent::__destruct();
    }

    /**
     * Injects the user setting service.
     *
     * @param Tx_Phpunit_Interface_UserSettingsService $userSettingService
     *
     * @return void
     */
    public function injectUserSettingService(Tx_Phpunit_Interface_UserSettingsService $userSettingService)
    {
        $this->userSettingService = $userSettingService;
    }

    /**
     * Injects the test finder.
     *
     * @param Tx_Phpunit_Service_TestFinder $testFinder
     *
     * @return void
     */
    public function injectTestFinder($testFinder)
    {
        $this->testFinder = $testFinder;
    }

    /**
     * Sets the action for the form.
     *
     * @param string $action
     *
     * @return void
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * Gets the action of the form.
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Creates the CSS style attribute content for an icon for the testable with the key $testableKey.
     *
     * @param string $testableKey
     *        the key of a Tx_Phpunit_Testable object, may also be "typo3", must not be empty
     *
     * @return string the content for the "style" attribute, will not be empty
     *
     * @throws InvalidArgumentException
     *         if there is no extension with tests for the given key
     */
    protected function createIconStyle($testableKey)
    {
        $testable = $this->testFinder->getTestableForKey($testableKey);

        return 'background: url(' . $testable->getIconPath() . ') 3px 50% white no-repeat; padding: 1px 1px 1px 24px;';
    }

    /**
     * Gets all options rendered as an array.
     *
     * @return array[] all option parameter as a multi-dimensional array, might be empty
     */
    abstract protected function getOptions();

    /**
     * Renders the select box as HTML string.
     *
     * @return string
     */
    abstract protected function renderSelect();
}
