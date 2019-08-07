<?php

use TYPO3\CMS\Lang\LanguageService;

/**
 * This class is the base class for all view helpers.
 *
 * @deprecated will be removed for PHPUnit 6.
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
