<?php
/***************************************************************
* Copyright notice
*
* (c) 2009 Oliver Klee <typo3-coding@oliverklee.de>
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
 * Class tx_phpunit_report_status for the "phpunit" extension.
 *
 * This class provides a status report for the "report" BE module.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_phpunit_report_status implements tx_reports_StatusProvider  {
	/**
	 * Returns the status of this extension.
	 *
	 * @return array an array of tx_reports_reports_status_Status objects
	 */
	public function getStatus() {
		$reports = array();

		$method = new ReflectionMethod('tx_phpunit_report_status', 'getStatus');

		if (strlen($method->getDocComment()) > 0) {
			$reports[] = t3lib_div::makeInstance(
				'tx_reports_reports_status_Status',
				$GLOBALS['LANG']->sL('LLL:EXT:phpunit/report/locallang.xml:status_phpComments'),
				$GLOBALS['LANG']->sL('LLL:EXT:phpunit/report/locallang.xml:status_phpComments_present_short'),
				$GLOBALS['LANG']->sL('LLL:EXT:phpunit/report/locallang.xml:status_phpComments_present_verbose'),
				tx_reports_reports_status_Status::OK
			);
		} else {
			$reports[] = t3lib_div::makeInstance(
				'tx_reports_reports_status_Status',
				$GLOBALS['LANG']->sL('LLL:EXT:phpunit/report/locallang.xml:status_phpComments'),
				$GLOBALS['LANG']->sL('LLL:EXT:phpunit/report/locallang.xml:status_phpComments_stripped_short'),
				$GLOBALS['LANG']->sL('LLL:EXT:phpunit/report/locallang.xml:status_phpComments_stripped_verbose'),
				tx_reports_reports_status_Status::ERROR
			);
		}

		return $reports;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/phpunit/report/class.tx_phpunit_report_status.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/phpunit/report/class.tx_phpunit_report_status.php']);
}
?>