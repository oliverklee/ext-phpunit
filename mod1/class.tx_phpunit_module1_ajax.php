<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2008-2011 Kasper Ligaard <kasperligaard@gmail.com>
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
 */
class tx_phpunit_module1_ajax {
	/**
	 * Used to broker incoming requests to other calls.
	 * Called by typo3/ajax.php
	 *
	 * @param array $params
	 *        additional parameters (not used)
	 * @param TYPO3AJAX $ajaxObj
	 *        the AJAX object of this request
	 *
	 * @return void
	 */
	public function ajaxBroker($params, TYPO3AJAX $ajaxObj) {
		// Checks for legal input ('white-listing').
		$state = t3lib_div::_POST('state') === 'true' ? 'on' : 'off';
		$checkbox = t3lib_div::_POST('checkbox');
		switch ($checkbox) {
			case 'failure':
			case 'success':
			case 'error':
			case 'skipped':
			case 'notimplemented':
			case 'testdox':
			case 'codeCoverage':
			case 'showMemoryAndTime':
				break;
			default:
				$checkbox = FALSE;
		}

		if ($checkbox) {
			$ajaxObj->setContentFormat('json');
			$GLOBALS['BE_USER']->uc['moduleData']['tools_txphpunitM1'][$checkbox] = $state;
			$GLOBALS['BE_USER']->writeUC();
			$ajaxObj->addContent('success', TRUE);
		} else {
			$ajaxObj->setContentFormat('plain');
			$ajaxObj->setError('Illegal input parameters.');
		}
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/phpunit/mod1/class.tx_phpunit_module1_ajax.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/phpunit/mod1/class.tx_phpunit_module1_ajax.php']);
}
?>