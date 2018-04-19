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
use TYPO3\CMS\Lang\LanguageService;

/**
 * This class is the base class for all view helpers.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
abstract class Tx_Phpunit_ViewHelpers_AbstractViewHelper
{
    /**
     * @var \Tx_Phpunit_Service_OutputService
     */
    protected $outputService = null;

    /**
     * The destructor.
     */
    public function __destruct()
    {
        unset($this->outputService);
    }

    /**
     * Injects the output service.
     *
     * @param \Tx_Phpunit_Service_OutputService $service the service to inject
     *
     * @return void
     */
    public function injectOutputService(\Tx_Phpunit_Service_OutputService $service)
    {
        $this->outputService = $service;
    }

    /**
     * Renders and outputs this view helper.
     *
     * @return void
     */
    abstract public function render();

    /**
     * Returns the localized string for the key $key.
     *
     * @param string $key the key of the string to retrieve, must not be empty
     *
     * @return string the localized string for the key $key
     */
    protected function translate($key)
    {
        return $this->getLanguageService()->getLL($key);
    }

    /**
     * Returns $GLOBALS['LANG'].
     *
     * @return LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }
}
