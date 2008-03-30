<?php
/**
 * Module 'PHPUnit' for the 'phpunit' extension.
 *
 * @author	Kasper Ligaard <ligaard@daimi.au.dk>
 * @package TYPO3
 * @subpackage tx_phpunit
 */

require_once 'PHPUnit/Util/Log/JSON.php';
require_once 'PHPUnit/Util/Log/Metrics.php';
require_once 'PHPUnit/Util/Log/PMD.php';
require_once 'PHPUnit/Util/Log/CPD.php';
require_once 'PHPUnit/Util/Log/GraphViz.php';

class tx_phpunit_module1 extends t3lib_SCbase {

	private static function getLL ($index) {
		global $LANG;
		return $LANG->getLL($index);
	}
	
	private static function sL ($input) {
		global $LANG;
		return $LANG->sL($input);
	}
	
	/**
	 * Create configuration for the function selector box
	 *
	 * @return	void
	 * @access	public
	 */
	public function menuConfig()	{
		$this->MOD_MENU = Array (
			'function' => Array (
				'runtests' => self::getLL('function_runtests'),
				'about' => self::getLL('function_about'),
			),
			'extSel' => '',
		);
		parent::menuConfig();
	}

	/**
	 * Main function of the module. All content is echoed directly instead of collecting it and
	 * doing the output later.
	 *
	 * @return	void
	 * @access	public
	 */
	public function main()	{
		global $BE_USER,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		if ($BE_USER->user['admin']) {

				// Draw the header.
			$this->doc = t3lib_div::makeInstance('bigDoc');
			$this->doc->backPath = $BACK_PATH;
			
				// Stylesheet for back-end module.
			$this->doc->styleSheetFile2 = t3lib_extMgm::extRelPath('phpunit').'mod1/phpunit-be.css';

				// JavaScript
			$this->doc->loadJavascriptLib('contrib/prototype/prototype.js');
			$this->doc->loadJavascriptLib('js/common.js');
			$this->doc->loadJavascriptLib(t3lib_extMgm::extRelPath('phpunit').'mod1/tx_phpunit_module1.js');
			$this->doc->JScode = $this->doc->wrapScriptTags('
					script_ended = 0;
					function jumpToUrl(URL)	{	//
						document.location = URL;
					}
					function setClass(id,className) {
						document.getElementById(id).className = className;
					}
			');

			echo $this->doc->startPage(self::getLL('title'));
			echo $this->doc->header(PHPUnit_Runner_Version::getVersionString());
			echo $this->doc->section('',$this->doc->funcMenu('', t3lib_BEfunc::getFuncMenu($this->id,'SET[function]',$this->MOD_SETTINGS['function'],$this->MOD_MENU['function']).$this->openNewWindowLink()));

				// Render content:
			switch ($this->MOD_SETTINGS['function']) {
				case 'runtests' :
					$this->runTests_render();
					break;
				case 'about' :
					$this->about_render();
					break;
			}

				// ShortCut
			echo $this->doc->section('',$this->doc->makeShortcutIcon('id',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']));

		} else {

			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->backPath = $BACK_PATH;

			echo $this->doc->startPage(self::getLL('title'));
			echo $this->doc->header(self::getLL('title'));
			echo self::getLL('admin_rights_needed');
		}
		echo $this->doc->endPage();
	}

	/*********************************************************
	 *
	 * 	Screen render functions
	 *
	 *********************************************************/

	/**
	 * Renders the screens for function "Run tests"
	 *
	 * @return	void
	 * @access	protected
	 */
	protected function runTests_render() {
		$command = $this->MOD_SETTINGS['extSel'] ? t3lib_div::_GP('command') : '';

		switch ($command) {
			case 'runalltests':
			case 'runsingletest':
				$this->runTests_renderIntro();
				$this->runTests_renderRunningTest();
				break;
			default:
				$this->runTests_renderIntro();
			}
	}

	/**
	 * Renders the intro screen for the function "Run tests"
	 *
	 * @return	void
	 * @access	protected
	 */
	protected function runTests_renderIntro() {
		$output = '';

		$extensionsWithTestSuites = $this->getExtensionsWithTestSuites();
		if (is_array($extensionsWithTestSuites))	{
			ksort($extensionsWithTestSuites);

			$output = $this->runTests_renderIntro_renderExtensionSelector($extensionsWithTestSuites);
			if ($this->MOD_SETTINGS['extSel'] && $this->MOD_SETTINGS['extSel']!='uuall') {
				$output .= $this->runTests_renderIntro_renderTestSelector($extensionsWithTestSuites, $this->MOD_SETTINGS['extSel']);
			}
		} else {
			$output = self::getLL('could_not_find_exts_with_tests');
		}

		echo $output;
	}

	/**
	 * Renders the extension selectorbox
	 *
	 * @param	array		$extensionsWithTestSuites: Array of extension keys for which test suites exist
	 * @return	string		HTML code for the selectorbox and a surrounding form
	 * @access	protected
	 */
	protected function runTests_renderIntro_renderExtensionSelector($extensionsWithTestSuites) {

		$extensionsOptionsArr =array();
		$extensionsOptionsArr[] = '<option value="">'.self::getLL('select_extension').'</option>';

		$selected = strcmp('uuall',$this->MOD_SETTINGS['extSel']) ? '' : ' selected="selected"';
		$extensionsOptionsArr[] = '<option class="alltests" value="uuall"'.$selected.'>'.self::getLL('all_extensions').'</option>';
		
		foreach($extensionsWithTestSuites as $dirName => $dummy)		{
			$style = 'background-image: url('.t3lib_extMgm::extRelPath($dirName).'/ext_icon.gif); background-repeat: no-repeat; background-position: 3px 50%; padding: 1px; padding-left: 24px;';
			$selected = strcmp($dirName,$this->MOD_SETTINGS['extSel']) ? '' : ' selected="selected"';
			if ($selected) {
				$currentExtName = $dirName;
			}
			$extensionsOptionsArr[]='<option style="'.$style.'" value="'.htmlspecialchars($dirName).'"'.$selected.'>'.$icon.htmlspecialchars($dirName).'</option>';
		}
		
		$style = 'background-image: url('.t3lib_extMgm::extRelPath($currentExtName).'/ext_icon.gif); background-repeat: no-repeat; background-position: 3px 50%; padding: 1px; padding-left: 24px;';
		
		$output = self::eAccelerator0951OptimizerHelp();
		$output .= '
			<form action="'.htmlspecialchars($this->MCONF['_']).'" method="POST">
                <select style="'.$style.'" name="SET[extSel]" onchange="jumpToUrl(\''.htmlspecialchars($this->MCONF['_']).'&SET[extSel]=\'+this.options[this.selectedIndex].value,this);">'.implode('',$extensionsOptionsArr).'</select>
				<input type="submit" value="'.self::getLL('run_all_tests').'" />
				<input type="hidden" name="command" value="runalltests" />
			</form>
			<br />
		';

		return $output;
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
			if (substr ($class, -8, 8) == 'testcase' &&
				$classReflection->isSubclassOf('PHPUnit_Framework_TestCase') && 
				$class != 'tx_phpunit_testcase'	&& 
				$class != 'tx_t3unit_testcase') {
				$testSuite->addTestSuite ($class);
			}
		}

		$testsOptionsArr = array();
		
		foreach ($testSuite->tests() as $testCases) {
			foreach ($testCases->tests() as $test) {
				$selected = $test->toString() == t3lib_div::GPvar('testname') ? ' selected="selected"' : '';
				$testSuiteName = strstr($test->toString(),'(');
				$testSuiteName = trim($testSuiteName,'()');
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

		$style = 'background-image: url('.t3lib_extMgm::extRelPath($extensionKey).'/ext_icon.gif); background-repeat: no-repeat; background-position: 3px 50%; padding: 1px; padding-left: 24px;';
		
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

		return $output;
	}

	/**
	 * Renders the screen for the function "Run tests" which shows and
	 * runs the actual unit tests
	 *
	 * @return	void
	 * @access	protected
	 */
	protected function runTests_renderRunningTest() {
		$this->simulateFrontendEnviroment();

		$extensionsWithTestSuites = $this->getExtensionsWithTestSuites();
		$testSuite = new PHPUnit_Framework_TestSuite('tx_phpunit_basetestsuite');
		$extensionKeysToProcess = array();
		if($this->MOD_SETTINGS['extSel']=='uuall') {
			echo '<h3>'.self::getLL('testing_all_extensions').'</h3>';
			$extensionKeysToProcess = array_keys($extensionsWithTestSuites);
		} else {
			echo '<h3>'.self::getLL('testing_extension').': '.htmlspecialchars($this->MOD_SETTINGS['extSel']).'</h3>';
			$extInfo = $extensionsWithTestSuites[$this->MOD_SETTINGS['extSel']];
			$extensionsWithTestSuites = array();
			$extensionsWithTestSuites[$this->MOD_SETTINGS['extSel']] = $extInfo;
			$extensionKeysToProcess = array($this->MOD_SETTINGS['extSel']);
		}


		// Load the files containing test cases from extensions:
		foreach ($extensionKeysToProcess as $extensionKey) {
			$paths = $extensionsWithTestSuites[$extensionKey];
			self::loadRequiredTestClasses($paths);
		}
		
			// Add all classes to the test suite which end with "testcase"
		foreach (get_declared_classes() as $class) {
			if (substr ($class, -8, 8) == 'testcase' && $class != 'tx_phpunit_testcase' && $class != 'tx_t3unit_testcase') {
				$testSuite->addTestSuite ($class);
			}
		}

			// Create a listener and run the tests:
		$testListener = new tx_phpunit_testlistener();
		$jsonListener = new PHPUnit_Util_Log_JSON();
        $graphVizListener = new PHPUnit_Util_Log_GraphViz();

		$testResult = new PHPUnit_Framework_TestResult;

		// Set to collect code coverage information.
		if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['collectCodeCoverageInformation'] &&
             extension_loaded('xdebug')) {
            $testResult->collectCodeCoverageInformation(TRUE);
        }
        
		$testResult->addListener($testListener);
		
		/* TODO: Create json based interface.
		$testResult->addListener($jsonListener);
		*/
		
		/* TODO: Add nice call graphs in code coderage report.
		$testResult->addListener($graphVizListener);
		*/

		if (t3lib_div::GPvar('testname')) {
			$testListener->totalNumberOfTestCases = 1;
			foreach ($testSuite->tests() as $testCases) {
				foreach ($testCases->tests() as $test) {
					if ($test->toString() == t3lib_div::GPvar('testname')) {
						$result = $test->run($testResult);
					}
				}
			}
		} else {
			//???:
			$testListener->totalNumberOfTestCases = $testSuite->count();
			$this->runTests_renderInfoAndProgressbar($testListener->totalNumberOfTestCases);
			$result = $testSuite->run($testResult);
		}            

		// Display test statistics:
		$testStatistics = '';
		if ($testResult->wasSuccessful()) {
	    	$testStatistics = '
				<script>setClass("progress-bar","wasSuccessful");</script>
				<h2 class="wasSuccessful">'.self::getLL('testing_success').'</h2>';
		} else {
	    	$testStatistics = '
				<script>setClass("progress-bar","hadFailure");</script>
				<h2 class="hadFailure">Failures!</h2>';
		}
		$testStatistics .= $testResult->count().' '.self::getLL('tests_total').', '.$testResult->failureCount().' '.self::getLL('tests_failures').', '.$testResult->errorCount().' '.self::getLL('tests_errors').'<br />';
		echo $testStatistics; 
		
		echo '
			<form action="'.htmlspecialchars($this->MCONF['_']).'" method="POST" >
				<input type="submit" value="'.self::getLL('run_again').'" tabindex="100" />
				<input name="command" type="hidden" value="'.t3lib_div::_GP('command').'" />
				<input name="testname" type="hidden" value="'.t3lib_div::_GP('testname').'" />
			</form>
		';
		
		// Code coverage output.
		//echo PHPUnit_Util_Report::render($result, '/tmp/coverage/');
		if ($testResult->getCollectCodeCoverageInformation()) {
			$jsonCodeCoverage = json_encode($testResult->getCodeCoverageInformation());
			// echo $jsonCodeCoverage;
		    PHPUnit_Util_Report::render($testResult, t3lib_extMgm::extPath('phpunit').'codecoverage/');
		    echo '<a target="_blank" href="'.t3lib_extMgm::extRelPath('phpunit').'codecoverage/typo3conf_ext.html">Click here to access the Code Coverage report</a><br/>';
		    echo 'Memory peak usage: '.ceil(memory_get_peak_usage()/(1024*1024)).' MB<br/>';
		    
		    /* TODO: Add metrics UI presentation
		    $logMetricsWriter = new PHPUnit_Util_Log_Metrics();
			$logMetricsWriter->process($testResult);
			*/
		
		    /* TODO: Add Project Mess Detector (PMD) statistics
        	$logPmdWriter = new PHPUnit_Util_Log_PMD();
        	$logPmdWriter->process($testResult);
			*/

		    /* TODO: Add Code Duplication Detection (CPD) statistics.
        	$logCpdWriter = new PHPUnit_Util_Log_CPD();
        	$logCpdWriter->process($testResult);
			*/
		}
	}

	/**
	 * Renders DIVs which contain information and a progressbar to visualize
	 * the running tests. The actual information will be written via JS during
	 * the test runs.
	 *
	 * @return	void
	 * @access	protected
	 */
	protected function runTests_renderInfoAndProgressbar($tests = 1) {
		echo '
			<div class="progress-bar-wrap">
				<span id="progress-bar">&nbsp;</span>
				<span id="transparent-bar">&nbsp;</span>
			</div>
		';
		
		if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['experimentalProgressBar']) {
			echo '<div style="width : 100%; height: auto;">';
			$width = 100 / $tests;
			for ($i = 0; $i < $tests; $i++) {
				echo '<a href="#testCaseNum-'.$i.'" style="width : '.$width.'%;" id="tx_phpunit_testcase_nr_'.$i.'" class="tx_phpunit_testcase_progressbox" title="'.$i.'">&nbsp;</a>';
			}
			echo '</div>';
		}
	}
	
	/**
	 * Renders the "About" screen
	 *
	 * @return	void
	 * @access	protected
	 */
	protected function about_render() {

		echo '<img src="'.t3lib_extMgm::extRelPath('phpunit').'mod1/phpunit.gif" width="94" height="80" alt="PHPUnit" title="PHPUnit" style="float:right; margin-left:10px;" />';
		$excludeExtensions = t3lib_div::trimExplode(',', $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['excludeextensions']);
		echo self::eAccelerator0951OptimizerHelp();
		if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['usepear'] && !t3lib_extMgm::isLoaded('pear')) {
			echo '<h2>Extension pear is not loaded</h2>
			The option for phpunit to use pear is set in the extension manager, but the pear extension is not loaded.<p>
			As a fall back phpunit uses the PHPUnit that is provided with it.<p>
			If you wish to use a pear provided PHPUnit, then load/install pear from the extension manager and fetch PHPUnit with the pear manager.<p>
			';
		}
		echo '
		<h2>About PHPUnit Backend Module</h2>
		PHPUnit BE is a <a href="http://en.wikipedia.org/wiki/Unit_testing">unit testing</a> framework based on <a href="http://www.phpunit.de/" target="_new">PHPUnit</a> by Sebastian Bergmann. It offers smooth integration
		of the PHPUnit framework into TYPO3 and offers an API and many functions which make unit testing of TYPO3 extensions easy and comfortable.<br />
		<h2>Get test-infected!</h2>
		If you think writing tests are dull, then try it. <a href="http://junit.sourceforge.net/doc/testinfected/testing.htm">You might become test-infected</a>!
		<h2>Current include path</h2>
		Below are the paths of the includepath that phpunit currently uses to locate PHPUnit:
		<p>
		<pre>'.join("\n",explode(PATH_SEPARATOR,get_include_path())).'</pre>
		<h2>Currently excluded extension</h2>
		The following extensions are excluded from being searched for tests:<p>
		<pre>'.join("\n",$excludeExtensions).'</pre>
		<p>Note: The extension exclusion list can be changed in the extension manager.
		<h2>Is XDebug PHP extension loaded?</h2>
		<p>To get code coverage reporting, PHPUnit needs the PHP extension <a target="_blank" href="http://www.xdebug.org"><em>XDebug</em></a>.</p>
		<p>On this PHP installation, XDebug is '.(extension_loaded('xdebug') ? '' : '<em>not</em>').' loaded</p>
		<h2>Current memory limit</h2>
		<p>When using XDebug to collect code coverage data, you will need the memory limit to be set rather high. Something like 256MB will probably be needed.</p>
		<p>On this PHP installation the memory limit is currently set to: '.ini_get('memory_limit').' 
		<h2>This extension has bugs...</h2>
		<p><a target="_blank" href="http://bugs.typo3.org/search.php?project_id=79&sticky_issues=on&sortby=last_updated&dir=DESC&hide_status_id=90">Click to see the list of issues for this extension</a></p>
		<p>You can report an issue by following the above link. An issue can be e.g. a bug or an improvement/enhancement.</p>
		<h2>Browse code in Subversion repository</h2>
		<p><a target="_blank" href="http://typo3xdev.svn.sourceforge.net/viewvc/typo3xdev/tx_phpunit/">The code repository for the phpunit extension can be browsed here</a></p>
		<h2>Licence and copyright</h2>
		PHPUnit is released under the terms of the PHP License as free software.<br />
		PHPUnit Copyright &copy; 2001 - 2008 Sebastian Bergmann
		<p>
		PHPUnit BE is released under the GPL Licence and is part of the TYPO3 Framework.<br />
		PHPUnit BE Copyright &copy; 2005-2008 <a href="mailto:kasperligaard@gmail.com">Kasper Ligaard</a>
		<h2>Contributors</h2>
		The following people have contributed by testing, bugfixing, suggesting new features etc.
		<p>
		Robert Lemke, Mario Rimann, Oliver Klee, Søren Soltveit and Mikkel Ricky.<p>
		';
	}


	/*********************************************************
	 *
	 * 	Helper functions
	 *
	 *********************************************************/

	/**
	 * Renders a link which opens the current screen in a new window
	 *
	 * @return	string
	 * @access	protected
	 */
	protected function openNewWindowLink()	{
		global $BACK_PATH;

		// FIXME: Needs to take mod.php into account, when generating URL here. Otherwise 'Open link in new window' will not work (gives error: Value "" for "M" was not found as a module).
		$url = t3lib_div::getIndpEnv('TYPO3_REQUEST_SCRIPT');
		$onClick = "phpunitbeWin=window.open('".$url."','phpunitbe','width=790,status=0,menubar=1,resizable=1,location=0,scrollbars=1,toolbar=0');phpunitbeWin.focus();return false;";
		$content = '
			<a id="opennewwindow" href="#" onclick="'.htmlspecialchars($onClick).'">
				<img'.t3lib_iconWorks::skinImg ($BACK_PATH,'gfx/open_in_new_window.gif','width="19" height="14"').' title="'.$this->sL('LLL:EXT:lang/locallang_core.xml:labels.openInNewWindow',1).'" class="absmiddle" alt="" />
			</a>
			<script language="JavaScript"> if(window.name=="phpunitbe") { document.getElementById("opennewwindow").style.display = "none"; } </script>
		';
// 				<img'.t3lib_iconWorks::skinImg ($BACK_PATH,'gfx/open_in_new_window.gif','width="19" height="14"').' title="'.$this->getsL('LLL:EXT:lang/locallang_core.php:labels.openInNewWindow',1).'" class="absmiddle" alt="" />

		return $content;
	}

	/**
	 * Scans all available extensions for test suites and returns the path / file names in an array
	 *
	 * @return	array		Array of testcase files
	 */
	protected function getExtensionsWithTestSuites() {
		// Fetch extension manager configuration options
		$excludeExtensions = t3lib_div::trimExplode(',', $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['excludeextensions']);
		$outOfLineTestCases = $this->traversePathForTestCases ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['outoflinetestspath']);
		
		// Get list of loaded extensions
		$extList = explode(',', $GLOBALS['TYPO3_CONF_VARS']['EXT']['extList']);
		
		$extensionsOwnTestCases = array();
		foreach ($extList as $extKey) {
			$testCasesArr = $this->findTestCasesInDir(t3lib_extMgm::extPath($extKey).'/tests/');
			if (!empty($testCasesArr)) {
				$extensionsOwnTestCases[$extKey] = $testCasesArr;
			}
		}

		$totalTestsArr = array_merge_recursive($outOfLineTestCases,$extensionsOwnTestCases);
		
		// Exclude extensions according to extension manager config
		$returnTestsArr = array_diff_key($totalTestsArr,array_flip($excludeExtensions));
		return $returnTestsArr;
	}

	/**
	 * Traverses a given path recursively for *testcase.php files
	 *
	 * @param	string		$path: The path to descent from
	 * @return	array		Array of paths / filenames
	 */
	private function traversePathForTestCases($path) {
		$extensionsArr = array();
		if (@is_dir($path))	{
			$dirs = t3lib_div::get_dirs($path);
			if (is_array($dirs))	{
				sort($dirs);
				foreach($dirs as $dirName) {
					if (t3lib_extMgm::isLoaded ($dirName)) {
						$testsPath = $path.$dirName.'/tests/';
						$extensionsArr[$dirName] = $this->findTestCasesInDir($testsPath);
					}
				}
			}
		}
		return $extensionsArr;
	}

	private function findTestCasesInDir($dir) {
		$extensionsArr = array();
		if (is_dir($dir)) {
			$testCaseFileNames = array ();
			$fileNamesArr = t3lib_div::getFilesInDir($dir, $extensionList='php');
			if (is_array ($fileNamesArr)) {
				foreach ($fileNamesArr as $fileName) {
					if (substr ($fileName, -12, 12) == 'testcase.php') {
						$testCaseFileNames[] = $fileName;
					}
				}
			$extensionsArr = array($dir => $testCaseFileNames);
			}
		}
		return $extensionsArr;
	}
	
	/**
	 * Roughly simulates the frontend although being in the backend.
	 *
	 * @return	void
	 * @todo	This is a quick hack, needs proper implementation
	 */
	protected function simulateFrontendEnviroment() {

		global $TSFE, $TYPO3_CONF_VARS;

			// FIXME: Currently bad workaround which only initializes a few things, not really what you'd call a frontend enviroment

		require_once(PATH_tslib.'class.tslib_fe.php');
		require_once(PATH_t3lib.'class.t3lib_page.php');
		require_once(PATH_t3lib.'class.t3lib_userauth.php');
		require_once(PATH_tslib.'class.tslib_feuserauth.php');
		require_once(PATH_t3lib.'class.t3lib_tstemplate.php');
		require_once(PATH_t3lib.'class.t3lib_cs.php');

		$temp_TSFEclassName = t3lib_div::makeInstanceClassName('tslib_fe');
		$TSFE = new $temp_TSFEclassName(
				$TYPO3_CONF_VARS,
				t3lib_div::_GP('id'),
				t3lib_div::_GP('type'),
				t3lib_div::_GP('no_cache'),
				t3lib_div::_GP('cHash'),
				t3lib_div::_GP('jumpurl'),
				t3lib_div::_GP('MP'),
				t3lib_div::_GP('RDCT')
			);
		$TSFE->connectToDB();
		$TSFE->config = array();		// Must be filled with actual config!

	}
	
	private static function eAccelerator0951OptimizerHelp () {
		$retval = '';
		if (extension_loaded('eaccelerator') && version_compare(phpversion('eaccelerator'),'0.9.5.2','<')) {
			$retval .= '<h2>IMPORTANT NOTICE ABOUT eAccelerator!</h2>';
			$retval .= 'eAccelerator '.phpversion('eaccelerator').' is loaded. This version of eAccelerator is known to crash phpunit when the optimizer is turned on.<p>';
			$retval .= 'You should either upgrade to eAccelerator version 0.9.5.2 (or later) or turn off the eAccelerator optimizer (in php.ini set: <code>eaccelerator.optimizer = "0"</code>) when running phpunit.<p>';
			$vars = ini_get_all('eaccelerator');
			$retval .= 'Current local value of <code>eaccelerator.optimizer</code>: '.$vars['eaccelerator.optimizer']['local_value'].'<br>';
			$retval .= 'Current global value of <code>eaccelerator.optimizer</code>: '.$vars['eaccelerator.optimizer']['global_value'].'<br>';
			$retval .= 'Confer <a href="http://eaccelerator.net/ticket/242">eAccelerator ticket 242 for more info</a>';
		}
		return $retval;
	}

	private static function loadRequiredTestClasses ($paths) {
		if (isset($paths)) {
			foreach ($paths as $path => $fileNames) {
				foreach ($fileNames as $fileName) {
					require_once (realpath($path.'/'.$fileName));
				}
			}
		}
	}
}
?>