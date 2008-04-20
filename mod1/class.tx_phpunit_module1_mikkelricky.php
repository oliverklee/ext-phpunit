<?php
class tx_phpunit_module1_mikkelricky extends tx_phpunit_module1 {
	public function main() {
		global $BACK_PATH, $BE_USER, $LANG;

		$this->doc = t3lib_div::makeInstance('bigDoc');
		$this->doc->backPath = $BACK_PATH;
		$this->doc->docType = 'xhtml_strict';

		$this->doc->styleSheetFile2 = t3lib_extMgm::extRelPath('phpunit').'mod1/styles.css';
		$this->doc->JScode = $this->doc->wrapScriptTags('
					script_ended = 0;
					function jumpToUrl(URL)	{	//
						document.location = URL;
					}
					function setClass(id, className) {
						element = document.getElementById(id);
						if (element) {
								element.className = className;
						}
					}
			');

		echo $this->doc->startPage($LANG->getLL('title'));
		echo $this->doc->header($LANG->getLL('title'));

		if ($BE_USER->user['admin']) {
			$this->printModuleContent();
		} else {
			echo $this->doc->section('', self::getLL('admin_rights_needed'));
		}
		echo $this->doc->endPage();
	}

	private static function runTestCases($testCases) {
		self::printProgressBar();

		$testSuite = new PHPUnit_Framework_TestSuite('tx_phpunit_basetestsuite');

		echo '<ol>';
		foreach ($testCases as $testCase) {
			echo '<li>'.$testCase['filename'].'</li>';
			$testSuite->addTestFile($testCase['filename']);
		}
		echo '</ol>';

		$testListener = new tx_phpunit_testlistener;
		$testListener->totalNumberOfTestCases = $testSuite->count();
		$testResult = new PHPUnit_Framework_TestResult;
		$testResult->addListener($testListener);
		$testSuite->run($testResult);
	}

	private static function runTestSuite($suite) {
		self::printProgressBar();

		if ($suite) {
			$definedClasses = get_declared_classes();

			require_once($suite['filename']);

			$newClasses = array_diff(get_declared_classes(), $definedClasses);

			foreach ($newClasses as $class) {
				$reflectionClass = new ReflectionClass($class);
				if ($reflectionClass->isSubclassOf('PHPUnit_Framework_TestSuite')) {
					$testSuite = $reflectionClass->newInstance();

					$testListener = new tx_phpunit_testlistener;
					$testListener->totalNumberOfTestCases = $testSuite->count();
					$testResult = new PHPUnit_Framework_TestResult;
					$testResult->addListener($testListener);
					$testSuite->run($testResult);
				}
			}
		}
	}


	private static function printProgressBar() {
		echo '
			<div class="progress-bar-wrap">
				<span id="progress-bar">&nbsp;</span>
				<span id="transparent-bar">&nbsp;</span>
			</div>
		';
	}

	private function printModuleContent() {
		$addEmptyOptions = true;

		$extKeys = t3lib_div::trimExplode(',', $GLOBALS['TYPO3_CONF_VARS']['EXT']['extList']);

		$allTestsInfo = array();
		foreach ($extKeys as $extKey) {
			if (t3lib_extMgm::isLoaded($extKey)) {
				$path = t3lib_extMgm::extPath($extKey);
				$testInfo = self::getTestInfo(realpath($path.'/tests/'));
				if ($testInfo) {
					$allTestInfo[$extKey] = $testInfo;
				}
			}
		}

		$action = t3lib_div::_POST('action');
		if ($action) {
			$action = array_pop(array_keys($action));
		}

		$content = '';

		$content .= '<form action="'.htmlspecialchars(t3lib_div::linkThisScript()).'" method="post">';

		$content .= '<table class="testcontrols">';

		// Render extension selector
		$key = 'extkey';
		$id = uniqid($key);

		$content .= '<tr'.(($action == $key) ? ' class="running"' : '').'>';
		$content .= '<td>';
		$content .= '<label for="'.$id.'">'.'Extension'.'</label>';
		$content .= '</td>';
		$content .= '<td>';
		$content .= '<select id="'.$id.'" name="'.$key.'">';
		if ($addEmptyOptions) {
			$content .= '<option/>';
		}
		foreach ($allTestInfo as $extKey => $testInfo) {
			$value = htmlspecialchars($extKey);
			$selected = t3lib_div::_POST($key) == $value ? ' selected="selected"' : '';
			$content .= '<option'.$selected.'>'.$value.'</option>';
		}
		$content .= '</select>';
		$content .= '</td>';
		$content .= '<td>';
		$content .= '<input type="submit" name="action['.$key.']" value="run"/>';
		$content .= '</td>';
		$content .= '</tr>';

		// Render package selector
		$key = 'package';
		$id = uniqid($key);

		$content .= '<tr'.(($action == $key) ? ' class="running"' : '').'>';
		$content .= '<td>';
		$content .= '<label for="'.$id.'">'.'Package'.'</label>';
		$content .= '</td>';
		$content .= '<td>';
		$content .= '<select id="'.$id.'" name="package">';
		if ($addEmptyOptions) {
			$content .= '<option/>';
		}
		foreach ($allTestInfo as $extKey => $testInfo) {
			if (array_key_exists('packages', $testInfo)) {
				$content .= '<optgroup label="'.$extKey.'">';
				foreach ($testInfo['packages'] as $name => $testCases) {
					$value = htmlspecialchars($extKey.'|'.$name);
					$label = htmlspecialchars($name);
					$selected = t3lib_div::_POST($key) == $value ? ' selected="selected"' : '';
					$content .= '<option value="'.$value.'"'.$selected.'>'.$name.'</option>';
				}
				$content .= '</optgroup>';
			}
		}
		$content .= '</select>';
		$content .= '</td>';
		$content .= '<td>';
		$content .= '<input type="submit" name="action['.$key.']" value="run"/>';
		$content .= '</td>';
		$content .= '</tr>';

		// Render test selector
		$key = 'testcase';
		$id = uniqid($key);

		$content .= '<tr'.(($action == $key) ? ' class="running"' : '').'>';
		$content .= '<td>';
		$content .= '<label for="'.$id.'">'.'Test Case'.'</label>';
		$content .= '</td>';
		$content .= '<td>';
		$content .= '<select id="'.$id.'" name="testcase">';
		if ($addEmptyOptions) {
			$content .= '<option/>';
		}
		foreach ($allTestInfo as $extKey => $testInfo) {
			if (array_key_exists('testcases', $testInfo)) {
				$content .= '<optgroup label="'.$extKey.'">';
				foreach ($testInfo['testcases'] as $testCase) {
					$name = $testCase['name'];
					$value = htmlspecialchars($extKey.'|'.$name);
					$label = htmlspecialchars($name);
					$selected = t3lib_div::_POST($key) == $value ? ' selected="selected"' : '';
					$content .= '<option value="'.$value.'"'.$selected.'>'.$name.'</option>';
				}
				$content .= '</optgroup>';
			}
		}
		$content .= '</select>';
		$content .= '</td>';
		$content .= '<td>';
		$content .= '<input type="submit" name="action['.$key.']" value="run"/>';
		$content .= '</td>';
		$content .= '</tr>';


		// Render suite selector
		$key = 'suite';
		$id = uniqid($key);

		$content .= '<tr'.(($action == $key) ? ' class="running"' : '').'>';
		$content .= '<td>';
		$content .= '<label for="'.$id.'">'.'Suite'.'</label>';
		$content .= '</td>';
		$content .= '<td>';
		$content .= '<select id="'.$id.'" name="suite">';
		if ($addEmptyOptions) {
			$content .= '<option/>';
		}
		foreach ($allTestInfo as $extKey => $testInfo) {
			if (array_key_exists('suites', $testInfo)) {
				$content .= '<optgroup label="'.$extKey.'">';
				foreach ($testInfo['suites'] as $testSuite) {
					$name = $testSuite['name'];
					$value = htmlspecialchars($extKey.'|'.$name);
					$label = htmlspecialchars($name);
					$selected = t3lib_div::_POST($key) == $value ? ' selected="selected"' : '';
					$content .= '<option value="'.$value.'"'.$selected.'>'.$name.'</option>';
				}
				$content .= '</optgroup>';
			}
		}
		$content .= '</select>';
		$content .= '</td>';
		$content .= '<td>';
		$content .= '<input type="submit" name="action['.$key.']" value="run"/>';
		$content .= '</td>';
		$content .= '</tr>';

		$content .= '</table>';
		$content .= '</form>';

		echo $content;

		switch ($action) {
			case 'extkey':
				$extKey = t3lib_div::_POST($action);
				if ($extKey) {
					$testCases = $allTestInfo[$extKey]['testcases'];
					self::runTestCases($testCases);
				}
				break;
			case 'package':
				$package = t3lib_div::_POST('package');
				if ($package) {
					list($extKey, $name) = explode('|', $package);
					$testCases = $allTestInfo[$extKey]['packages'][$name];
					self::runTestCases($testCases);
				}
				break;
			case 'testcase':
				$testCase = t3lib_div::_POST('testcase');
				if ($testCase) {
					list($extKey, $name) = explode('|', $testCase);
					$testCases = null;
					foreach ($allTestInfo[$extKey]['testcases'] as $testCase) {
						if ($testCase['name'] == $name) {
							$testCases = array($testCase);
							break;
						}
					}
					self::runTestCases($testCases);
				}
				break;
			case 'suite':
				$suite = t3lib_div::_POST('suite');
				if ($suite) {
					list($extKey, $name) = explode('|', $suite);
					$testSuite = null;
					foreach ($allTestInfo[$extKey]['suites'] as $t) {
						if ($t['name'] == $name) {
							$testSuite = $t;
							break;
						}
					}
					self::runTestSuite($testSuite);
				}
				break;
		}
	}

	private function getTestInfo($path) {
		$info = null;
		if (is_dir($path)) {
			$path = realpath($path).'/';
			$testCaseFilenames = array();
			$filenames = t3lib_div::getAllFilesAndFoldersInPath(array(), $path, 'php');
			$filenames = array_values($filenames);

			// Fix all Windows paths in order to make the following trics easier!
			if (TYPO3_OS == 'WIN') {
				$path = t3lib_div::fixWindowsFilePath($path);
				$filenames = array_map(array('t3lib_div', 'fixWindowsFilePath'), $filenames);
			}

			$info = array();
			foreach ($filenames as $filename) {
				$pattern = '|'.preg_quote($path, '|').'(?:(?:class\.)?(.+)/)?([^/]+)_testcase\.php$|';
				if (preg_match($pattern, $filename, $matches)) {
					$testCase = array('name' => $matches[2],
                                                       'filename' => $filename,
					);
					if ($package = $matches[1]) {
						if (!array_key_exists('packages', $info)) {
							$info['packages'] = array();
						}
						if (!array_key_exists($package, $info['packages'])) {
							$info['packages'][$package] = array();
						}
						$info['packages'][$package][] = $testCase;
					}
					$info['testcases'][] = $testCase;
				}

				$pattern = '|'.preg_quote($path, '|').'(?:(.+)/)?([^/]+)_testsuite\.php$|';
				if (preg_match($pattern, $filename, $matches)) {
					$testSuite = array(
                        'name' => $matches[2],
                        'filename' => $filename,
					);
					if (!array_key_exists('suites', $info)) {
						$info['suites'] = array();
					}
					$info['suites'][] = $testSuite;
				}
			}
		}

		return $info;
	}
}
?>
