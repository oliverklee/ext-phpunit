<?php

use TYPO3\CMS\Core\Http\AjaxRequestHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class uses the new AJAX broker in TYPO3 4.2.
 *
 * @deprecated will be removed for PHPUnit 6.
 *
 * @author Kasper Ligaard <kasperligaard@gmail.com>
 * @author Michael Klapper <michael.klapper@aoemedia.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Bastian Waidelich <bastian@typo3.org>
 * @author Carsten Koenig <ck@carsten-koenig.de>
 */
class Tx_Phpunit_BackEnd_Ajax
{
    /**
     * @var \Tx_Phpunit_Interface_UserSettingsService
     */
    protected $userSettingsService = null;

    /**
     * @var string[]
     */
    protected static $validCheckboxKeys = [
        'failure',
        'success',
        'error',
        'skipped',
        'incomplete',
        'testdox',
    ];

    /**
     * The constructor.
     *
     * @param bool $initializeUserSettingsService whether to automatically initialize the user settings service
     */
    public function __construct($initializeUserSettingsService = true)
    {
        if ($initializeUserSettingsService) {
            /** @var \Tx_Phpunit_Service_UserSettingsService $userSettingsService */
            $userSettingsService = GeneralUtility::makeInstance(\Tx_Phpunit_Service_UserSettingsService::class);
            $this->injectUserSettingsService($userSettingsService);
        }
    }

    /**
     * Injects the user settings service.
     *
     * @param \Tx_Phpunit_Interface_UserSettingsService $service the service to inject
     *
     * @return void
     */
    public function injectUserSettingsService(\Tx_Phpunit_Interface_UserSettingsService $service)
    {
        $this->userSettingsService = $service;
    }

    /**
     * Used to broker incoming requests to other calls.
     * Called by typo3/ajax.php
     *
     * @param array $unused additional parameters (not used)
     * @param AjaxRequestHandler $ajax the AJAX object for this request
     *
     * @return void
     */
    public function ajaxBroker(array $unused, AjaxRequestHandler $ajax)
    {
        $state = (bool)GeneralUtility::_POST('state');
        $checkbox = GeneralUtility::_POST('checkbox');

        if (in_array($checkbox, self::$validCheckboxKeys, true)) {
            $ajax->setContentFormat('json');
            $this->userSettingsService->set($checkbox, $state);
            $ajax->addContent('success', true);
        } else {
            $ajax->setContentFormat('plain');
            $ajax->setError('Illegal input parameters.');
        }
    }
}
