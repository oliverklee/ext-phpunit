<?php
/**
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
 * This class is the base class for all view helpers.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
abstract class Tx_Phpunit_ViewHelpers_AbstractViewHelper {
	/**
	 * @var Tx_Phpunit_Service_OutputService
	 */
	protected $outputService = NULL;

	/**
	 * The destructor.
	 */
	public function __destruct() {
		unset($this->outputService);
	}

	/**
	 * Injects the output service.
	 *
	 * @param Tx_Phpunit_Service_OutputService $service the service to inject
	 *
	 * @return void
	 */
	public function injectOutputService(Tx_Phpunit_Service_OutputService $service) {
		$this->outputService = $service;
	}

	/**
	 * Renders and outputs this view helper.
	 *
	 * @return void
	 */
	abstract public function render();
}