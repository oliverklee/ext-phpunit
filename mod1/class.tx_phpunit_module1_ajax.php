<?php

/*
 * This class uses the new ajax broker in Typo3 4.2. Thus a minimum requirement
 * of Typo3 4.2 (and hence PHP 5.2.x) is required.
 *
 * For more on the AJAX classes, and how the interact, see http://bugs.typo3.org/view.php?id=7096
 *
 * @author Kasper Ligaard <kasperligaard@gmail.com>
 */

class tx_phpunit_module1_ajax {
	/**
	 * Used to broker incoming requests to other calls.
	 * Called by typo3/ajax.php
	 *
	 * @param	array		$params: additional parameters (not used)
	 * @param	TYPO3AJAX	&$ajaxObj: reference of the TYPO3AJAX object of this request
	 * @return	void
	 */
	public function ajaxBroker($params, &$ajaxObj) {
		// Check for legal input ('white-listing').
		$state = t3lib_div::_POST('state') === 'true' ? 'on' : 'off';
		$checkbox = t3lib_div::_POST('checkbox');
		switch ($checkbox) {
			case 'failure':
			case 'success':
			case 'error':
			case 'codeCoverage':
				break;
			default:
				$checkbox = false;
		}

		if ($checkbox) {
			$ajaxObj->setContentFormat('json');
			$GLOBALS['BE_USER']->uc['moduleData']['tools_txphpunitM1'][$checkbox] = $state;
			$GLOBALS['BE_USER']->writeUC();
			$userConfiguration = $GLOBALS['BE_USER']->getModuleData('tools_txphpunitM1');
			$ajaxObj->addContent('success', true);
		} else {
			$ajaxObj->setContentFormat('plain');
			$ajaxObj->setError('Illegal input parameters.');
		}
	}
}
?>