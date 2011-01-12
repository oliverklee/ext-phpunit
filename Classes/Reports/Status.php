<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2009-2011 Oliver Klee <typo3-coding@oliverklee.de>
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
 * This class provides a status report for the "Reports" BE module.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_phpunit_Reports_Status implements tx_reports_StatusProvider {
	/**
	 * Returns the status of this extension.
	 *
	 * @return array<tx_reports_reports_status_Status>
	 *         status reports for this extension
	 */
	public function getStatus() {
		return array(
			$this->getReflectionStatus(),
			$this->getEAcceleratorStatus(),
		);
	}

	/**
	 * Creates a status concerning whether PHP reflection works correctly.
	 *
	 * @return tx_reports_reports_status_Status
	 *         a status indicating whether PHP reflection works correctly
	 */
	protected function getReflectionStatus() {
		$heading = $GLOBALS['LANG']->sL(
			'LLL:EXT:phpunit/Resources/Private/Language/Report.xml:status_phpComments'
		);

		$method = new ReflectionMethod('tx_phpunit_Reports_Status', 'getStatus');
		if (strlen($method->getDocComment()) > 0) {
			$status = t3lib_div::makeInstance(
				'tx_reports_reports_status_Status',
				$heading,
				$GLOBALS['LANG']->sL('LLL:EXT:phpunit/Resources/Private/Language/Report.xml:status_phpComments_present_short'),
				$GLOBALS['LANG']->sL('LLL:EXT:phpunit/Resources/Private/Language/Report.xml:status_phpComments_present_verbose'),
				tx_reports_reports_status_Status::OK
			);
		} else {
			$status = t3lib_div::makeInstance(
				'tx_reports_reports_status_Status',
				$heading,
				$GLOBALS['LANG']->sL('LLL:EXT:phpunit/Resources/Private/Language/Report.xml:status_phpComments_stripped_short'),
				$GLOBALS['LANG']->sL('LLL:EXT:phpunit/Resources/Private/Language/Report.xml:status_phpComments_stripped_verbose'),
				tx_reports_reports_status_Status::ERROR
			);
		}

		return $status;
	}

	/**
	 * Creates a status concerning eAccelerator not crashing phpunit.
	 *
	 * @return tx_reports_reports_status_Status
	 *         a status concerning eAccelerator not crashing phpunit
	 */
	protected function getEAcceleratorStatus() {
		$heading = $GLOBALS['LANG']->sL(
			'LLL:EXT:phpunit/Resources/Private/Language/Report.xml:status_eAccelerator'
		);

		if (!extension_loaded('eaccelerator')) {
			$status = t3lib_div::makeInstance(
				'tx_reports_reports_status_Status',
				$heading,
				$GLOBALS['LANG']->sL('LLL:EXT:phpunit/Resources/Private/Language/Report.xml:status_eAccelerator_notInstalled_short'),
				'',
				tx_reports_reports_status_Status::OK
			);
		} else {
			$version = phpversion('eaccelerator');

			if (version_compare($version, '0.9.5.2', '<')) {
				$verboseMessage = sprintf(
					$GLOBALS['LANG']->sL('LLL:EXT:phpunit/Resources/Private/Language/Report.xml:status_eAccelerator_installedOld_verbose'),
					$version
				);

				$status = t3lib_div::makeInstance(
					'tx_reports_reports_status_Status',
					$heading,
					$GLOBALS['LANG']->sL('LLL:EXT:phpunit/Resources/Private/Language/Report.xml:status_eAccelerator_installedOld_short'),
					$verboseMessage,
					tx_reports_reports_status_Status::ERROR
				);
			} else {
				$verboseMessage = sprintf(
					$GLOBALS['LANG']->sL('LLL:EXT:phpunit/Resources/Private/Language/Report.xml:status_eAccelerator_installedNew_verbose'),
					$version
				);

				$status = t3lib_div::makeInstance(
					'tx_reports_reports_status_Status',
					$heading,
					$GLOBALS['LANG']->sL('LLL:EXT:phpunit/Resources/Private/Language/Report.xml:status_eAccelerator_installedNew_short'),
					$verboseMessage,
					tx_reports_reports_status_Status::OK
				);
			}
		}

		return $status;
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/phpunit/Classes/Reports/Status.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/phpunit/Classes/Reports/Status.php']);
}
?>