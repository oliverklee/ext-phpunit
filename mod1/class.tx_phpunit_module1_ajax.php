<?php

/*
 * This class uses the new ajax broker in Typo3 4.2. Thus a minimum requirement 
 * of Typo3 4.2 (and hence PHP 5.2.x) is required.
 * 
 * For more on the AJAX classes, and how the interact, see http://bugs.typo3.org/view.php?id=7096
 * 
 * @author Kasper Ligaard <kasperligaard@gmail.com>
 */

class tx_phpunit_module1_ajax extends tx_phpunit_module1 {
	
	
	/**********************************
	 *
	 * AJAX Calls
	 *
	 **********************************/

	/**
	 * Used to broker incoming requests to other calls.
	 * Called by typo3/ajax.php
	 *
	 * @param	array		$params: additional parameters (not used here)
	 * @param	TYPO3AJAX	&$ajaxObj: reference of the TYPO3AJAX object of this request
	 * @return	void
	 */
	public function ajaxBroker($params, &$ajaxObj) {
		global $LANG;
		
		$jsonalike = join(' ', $params);
		if (false) {
			$ajaxObj->setError('Det er noget lort.');
		} else {
			$ajaxObj->addContent('ligaardHelloWorld', $jsonalike);
		}
	}
}
?>