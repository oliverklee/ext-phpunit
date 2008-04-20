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
	 * Main function of the module. All content is echoed directly instead of collecting it and
	 * doing the output later.
	 *
	 * @return	void
	 * @access	public
	 */
	public function main() {
		global $BE_USER,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		if ($BE_USER->user['admin']) {

				// Draw the header.
			$this->doc = t3lib_div::makeInstance('bigDoc');
			$this->doc->backPath = $BACK_PATH;
			$this->doc->docType = 'xhtml_strict';

				// Stylesheet for back-end module.
			$this->doc->styleSheetFile2 = t3lib_extMgm::extRelPath('phpunit').'mod1/phpunit-be.css';

				// JavaScript
			// @todo: Use Typo3 4.2 $this->doc->loadJavascriptLib() function in the future.
			$t3_41_compatibility = '<script type="text/javascript" src="contrib/prototype/prototype.js"></script>';
			$t3_41_compatibility .= '<script type="text/javascript" src="js/common.js"></script>';
			$t3_41_compatibility .= '<script type="text/javascript" src="'.t3lib_extMgm::extRelPath('phpunit').'mod1/tx_phpunit_module1.js"></script>';

			$this->doc->JScode = $t3_41_compatibility .
				'<link rel="stylesheet" type="text/css" href="../typo3conf/ext/phpunit/mod1/phpunit-be.css" />'.$this->doc->wrapScriptTags('
				script_ended = 0;
				function jumpToUrl(URL)	{	//
					document.location = URL;
				}

				function setClass(id, className) {
					element = document.getElementById(id);
					if (element) {
							element.className = className;
					}
					parent.window.document.getElementById(id).className = className;
				}
				');

			echo $this->doc->startPage(self::getLL('title'));
			echo $this->doc->header(PHPUnit_Runner_Version::getVersionString());
			echo $this->doc->section('', $this->doc->funcMenu('', t3lib_BEfunc::getFuncMenu($this->id, 'SET[function]', $this->MOD_SETTINGS['function'], $this->MOD_MENU['function']).$this->openNewWindowLink()));

				// Render content:
			switch ($this->MOD_SETTINGS['function']) {
				case 'runtests' :
					$this->runTests_render();
					break;
				case 'about' :
					$this->about_render();
					break;
				case 'news' :
					$this->news_render();
					break;
			}

				// ShortCut
			echo $this->doc->section('', $this->doc->makeShortcutIcon('id', implode(',', array_keys($this->MOD_MENU)), $this->MCONF['name']));

		} else {

			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->backPath = $BACK_PATH;

			echo $this->doc->startPage(self::getLL('title'));
			echo $this->doc->header(self::getLL('title'));
			echo self::getLL('admin_rights_needed');
		}
		echo $this->doc->endPage();
	}
		/**
	 * Renders a selector box for running single tests for the given extension
	 *
	 * @param	array		$extensionsWithTestSuites: Array of extension keys for which test suites exist
	 * @param	string		$extensionKey: Extension key of the extensino to run single test for
	 * @return	string		HTML code with the selectorbox and a surrounding form
	 * @access	protected
	 */
	protected function runTests_renderIntro_renderTestSelector($extensionsWithTestSuites, $extensionKey) {
		$testSuite = new PHPUnit_Framework_TestSuite('tx_phpunit_basetestsuite');

		// Load the files containing test cases from extensions:
		$paths = $extensionsWithTestSuites[$extensionKey];

		if (isset($paths)) {
			foreach ($paths as $path => $fileNames) {
				foreach ($fileNames as $fileName) {
					require_once ($path.$fileName);
				}
			}
		}

		// Add all classes to the test suite which end with "testcase", except the two special classes used as super-classes.
		foreach (get_declared_classes() as $class) {
			$classReflection = new ReflectionClass($class);
			if (substr($class, -8, 8) == 'testcase' &&
				$classReflection->isSubclassOf('PHPUnit_Framework_TestCase') &&
				$class !== 'tx_phpunit_testcase'	&&
				$class !== 'tx_t3unit_testcase' &&
				$class !== 'tx_phpunit_database_testcase') {
				$testSuite->addTestSuite($class);
			}
		}

		$testsOptionsArr = array();

		foreach ($testSuite->tests() as $testCases) {
			foreach ($testCases->tests() as $test) {
				$selected = $test->toString() == t3lib_div::GPvar('testname') ? ' selected="selected"' : '';
				$testSuiteName = strstr($test->toString(), '(');
				$testSuiteName = trim($testSuiteName, '()');
				$testsOptionsArr[$testSuiteName][] = '<option value="'.$test->toString().'"'.$selected.'>'.htmlspecialchars($test->getName()).'</option>';
			}
		}

		$currentStyle = 'background-image: url('.t3lib_extMgm::extRelPath($extensionKey).'/ext_icon.gif); background-repeat: no-repeat; background-position: 3px 50%; padding: 1px; padding-left: 24px;';

		// build options for select (incl. option groups for test suites)
		$testOptionsHtml = '';
		foreach ($testsOptionsArr as $suiteName => $testArr) {
			$testOptionsHtml .= '<optgroup label="'.$suiteName.'">';
			foreach ($testArr as $testHtml) {
				$testOptionsHtml .= $testHtml;
			}
			$testOptionsHtml .= '</optgroup>';
		}

		$style = 'background-image: url('.t3lib_extMgm::extRelPath($extensionKey).'ext_icon.gif); background-repeat: no-repeat; background-position: 3px 50%; padding: 1px; padding-left: 24px;';

		$output = '
			<form action="'.htmlspecialchars($this->MCONF['_']).'" method="post">
				<select style="'.$currentStyle.'" name="testname">
				<option value="">'.self::getLL('select_tests').'</option>'.
				$testOptionsHtml.
				'</select>
				<input type="submit" value="'.self::getLL('run_single_test').'" />
				<input type="hidden" name="command" value="runsingletest" />
			</form>
		';

		$output .= '<script type="text/javascript">function rundaphpunit () { document.getElementById("testframe").src = "http://gt3/t3/typo3/mod.php?M=tools_txphpunitbeM1&command=runalltests&SET[extSel]=phpunit"; }</script>';
		$output .= '<a href="#" onclick="rundaphpunit();">Run PHPUnit tests</a>';
		$output .= '<iframe id="testframe" width="100%" height="400px" marginwidth="0" frameborder="1" ></iframe>';

		return $output;
	}

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