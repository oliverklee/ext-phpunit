<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2008-2013 Kasper Ligaard <kasperligaard@gmail.com>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * This class uses the new AJAX broker in Typo3 4.2.
 *
 * @see http://bugs.typo3.org/view.php?id=7096
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Kasper Ligaard <kasperligaard@gmail.com>
 * @author Michael Klapper <michael.klapper@aoemedia.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Bastian Waidelich <bastian@typo3.org>
 * @author Carsten Koenig <ck@carsten-koenig.de>
 */
class Tx_Phpunit_BackEnd_Ajax {
	/**
	 * @var Tx_Phpunit_Interface_UserSettingsService
	 */
	protected $userSettingsService = NULL;

	/**
	 * @var array<string>
	 */
	protected $validCheckboxKeys = array(
		'failure',
		'success',
		'error',
		'skipped',
		'incomplete',
		'testdox',
		'codeCoverage',
		'showMemoryAndTime',
		'runSeleniumTests',
	);

	/**
	 * The constructor.
	 *
	 * @param boolean $initializeUserSettingsService whether to automatically initialize the user settings service
	 */
	public function __construct($initializeUserSettingsService = TRUE) {
		if ($initializeUserSettingsService) {
			/** @var $userSettingsService Tx_Phpunit_Service_UserSettingsService */
			$userSettingsService = t3lib_div::makeInstance('Tx_Phpunit_Service_UserSettingsService');
			$this->injectUserSettingsService($userSettingsService);
		}
	}

	/**
	 * The destructor.
	 */
	public function __destruct() {
		unset($this->userSettingsService);
	}

	/**
	 * Injects the user settings service.
	 *
	 * @param Tx_Phpunit_Interface_UserSettingsService $service the service to inject
	 *
	 * @return void
	 */
	public function injectUserSettingsService(Tx_Phpunit_Interface_UserSettingsService $service) {
		$this->userSettingsService = $service;
	}

	/**
	 * Used to broker incoming requests to other calls.
	 * Called by typo3/ajax.php
	 *
	 * @param array $unused additional parameters (not used)
	 * @param TYPO3AJAX $ajax the AJAX object for this request
	 *
	 * @return void
	 */
	public function ajaxBroker(array $unused, TYPO3AJAX $ajax) {
		$state = (boolean) t3lib_div::_POST('state');
		$checkbox = t3lib_div::_POST('checkbox');

		if (in_array($checkbox, $this->validCheckboxKeys, TRUE)) {
			$ajax->setContentFormat('json');
			$this->userSettingsService->set($checkbox, $state);
			$ajax->addContent('success', TRUE);
		} else {
			$ajax->setContentFormat('plain');
			$ajax->setError('Illegal input parameters.');
		}
	}
}
?>