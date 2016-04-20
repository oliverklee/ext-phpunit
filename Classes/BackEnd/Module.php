<?php
/*
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

use TYPO3\CMS\Backend\Module\BaseScriptClass;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\Utility\IconUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Lang\LanguageService;
use TYPO3\CMS\Core\Messaging\FlashMessageService;;

/**
 * Back-end module "PHPUnit".
 *
 * @author Kasper Ligaard <kasperligaard@gmail.com>
 * @author Michael Klapper <michael.klapper@aoemedia.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Bastian Waidelich <bastian@typo3.org>
 * @author Carsten Koenig <ck@carsten-koenig.de>
 */
class Tx_Phpunit_BackEnd_Module extends BaseScriptClass
{
    /**
     * @var string
     */
    const EXTENSION_KEY = 'phpunit';

    /**
     * @var string
     */
    const MODULE_NAME = 'tools_txphpunitbeM1';

    /**
     * the relative path to this extension
     *
     * @var string
     */
    protected $extensionPath = '';

    /**
     * a name prettifier for creating readable test and test case names
     *
     * @var PHPUnit_Util_TestDox_NamePrettifier
     */
    protected $namePrettifier = null;

    /**
     * @var Tx_Phpunit_Interface_Request
     */
    protected $request = null;

    /**
     * @var Tx_Phpunit_Service_TestFinder
     */
    protected $testFinder = null;

    /**
     * @var Tx_Phpunit_Service_TestCaseService
     */
    protected $testCaseService = null;

    /**
     * @var Tx_Phpunit_BackEnd_TestListener
     */
    protected $testListener = null;

    /**
     * @var Tx_Phpunit_Service_OutputService
     */
    protected $outputService = null;

    /**
     * @var Tx_Phpunit_Interface_UserSettingsService
     */
    protected $userSettingsService = null;

    /**
     * @var PHP_CodeCoverage
     */
    protected $coverage = null;

    /**
     * @var Tx_Phpunit_BackEnd_TestStatistics
     */
    protected $testStatistics = null;

    /**
     * The constructor.
     */
    public function __construct()
    {
        $this->init();

        $this->extensionPath = ExtensionManagementUtility::extRelPath(self::EXTENSION_KEY);
    }

    /**
     * The destructor.
     */
    public function __destruct()
    {
        unset(
            $this->request,
            $this->testFinder,
            $this->coverage,
            $this->testListener,
            $this->outputService,
            $this->userSettingsService,
            $this->testStatistics,
            $this->testCaseService
        );
    }

    /**
     * Injects the request.
     *
     * @param Tx_Phpunit_Interface_Request $request the request to inject
     *
     * @return void
     */
    public function injectRequest(Tx_Phpunit_Interface_Request $request)
    {
        $this->request = $request;
    }

    /**
     * Injects the test listener.
     *
     * @param Tx_Phpunit_BackEnd_TestListener $testListener the test listener to inject
     *
     * @return void
     */
    public function injectTestListener(Tx_Phpunit_BackEnd_TestListener $testListener)
    {
        $this->testListener = $testListener;
    }

    /**
     * Injects the name prettifier.
     *
     * @param PHPUnit_Util_TestDox_NamePrettifier $namePrettifier the name prettifier to inject
     *
     * @return void
     */
    public function injectNamePrettifier(PHPUnit_Util_TestDox_NamePrettifier $namePrettifier)
    {
        $this->namePrettifier = $namePrettifier;
    }

    /**
     * Injects the output service.
     *
     * @param Tx_Phpunit_Service_OutputService $service the service to inject
     *
     * @return void
     */
    public function injectOutputService(Tx_Phpunit_Service_OutputService $service)
    {
        $this->outputService = $service;
    }

    /**
     * Injects the user settings service.
     *
     * @param Tx_Phpunit_Interface_UserSettingsService $service the service to inject
     *
     * @return void
     */
    public function injectUserSettingsService(Tx_Phpunit_Interface_UserSettingsService $service)
    {
        $this->userSettingsService = $service;
    }

    /**
     * Injects the test finder.
     *
     * @param Tx_Phpunit_Service_TestFinder $testFinder the test finder to inject
     *
     * @return void
     */
    public function injectTestFinder(Tx_Phpunit_Service_TestFinder $testFinder)
    {
        $this->testFinder = $testFinder;
    }

    /**
     * Injects the test case service.
     *
     * @param Tx_Phpunit_Service_TestCaseService $testCaseService the test case service to inject
     *
     * @return void
     */
    public function injectTestCaseService(Tx_Phpunit_Service_TestCaseService $testCaseService)
    {
        $this->testCaseService = $testCaseService;
    }

    /**
     * Returns the localized string for the key $key.
     *
     * @param string $key the key of the string to retrieve, must not be empty
     *
     * @return string the localized string for the key $key
     */
    protected function translate($key)
    {
        return $this->getLanguageService()->getLL($key);
    }

    /**
     * Returns $GLOBALS['LANG'].
     *
     * @return LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }

    /**
     * Main function of the module. Outputs all content directly instead of collecting it and doing the output later.
     *
     * @return void
     */
    public function main()
    {
        $this->doc = GeneralUtility::makeInstance('TYPO3\\CMS\\Backend\\Template\\DocumentTemplate');
        $this->doc->backPath = $GLOBALS['BACK_PATH'];

        if ($GLOBALS['BE_USER']->user['admin']) {
            $this->doc->docType = 'xhtml_strict';
            $this->doc->bodyTagAdditions = 'id="doc3"';

            $this->addAdditionalHeaderData();

            $this->cleanOutputBuffers();
            $this->outputService->output(
                $this->doc->startPage($this->translate('title')) .
                $this->doc->header(PHPUnit_Runner_Version::getVersionString())
            );

            $this->renderRunTests();

            $this->outputService->output(
                $this->doc->section(
                    $this->translate('shortcuts.title'),
                    '<p>' . $this->translate('shortcuts.text') . '</p>
                    <p>' . $this->translate('shortcuts.browser_dependency') . '</p>
                    <ul>
                    <li>' . $this->translate('shortcuts.browser_safari_ie') . '</li>
                    <li>' . $this->translate('shortcuts.browser_firefox') . '</li>
                    </ul>'
                )
            );
        } else {
            $this->outputService->output(
                $this->doc->startPage($this->translate('title')) .
                $this->doc->header($this->translate('title')) .
                $this->translate('admin_rights_needed')
            );
        }

        $this->outputService->output($this->doc->endPage());
    }

    /**
     * Adds some JavaScript and CSS stuff to header data.
     *
     * @return void
     */
    protected function addAdditionalHeaderData()
    {
        /** @var TYPO3\CMS\Core\Page\PageRenderer $pageRenderer */
        $pageRenderer = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Page\\PageRenderer');
        $pageRenderer->loadJquery();
        $publicResourcesPath = $this->extensionPath . 'Resources/Public/';
        $pageRenderer->addJsFile($publicResourcesPath . 'JavaScript/BackEnd.js', 'text/javascript', false);
        $pageRenderer->addCssFile($publicResourcesPath . 'CSS/BackEnd.css', 'stylesheet', 'all', '', false);
    }

    /**
     * Ends and cleans all output buffers.
     *
     * @return void
     */
    protected function cleanOutputBuffers()
    {
        do {
            $hasMoreBuffers = ob_end_clean();
        } while ($hasMoreBuffers);
    }


    /*********************************************************
     *
     * Screen render functions
     *
     *********************************************************/

    /**
     * Renders the screens for function "Run tests".
     *
     * @return void
     */
    protected function renderRunTests()
    {
        $command = $this->request->getAsString(Tx_Phpunit_Interface_Request::PARAMETER_KEY_COMMAND);
        switch ($command) {
            case 'runalltests':
                // The fallthrough is intentional.
            case 'runTestCaseFile':
                // The fallthrough is intentional.
            case 'runsingletest':
                $this->renderRunTestsIntro();
                $this->renderRunningTest();
                break;
            default:
                $this->renderRunTestsIntro();
        }
    }

    /**
     * Gets the key of the currently selected testable and saves it to the user settings.
     *
     * @return string the currently selected testable key, will not be empty
     */
    protected function getAndSaveSelectedTestableKey()
    {
        $testableKeyFromSettings = $this->userSettingsService->getAsString(Tx_Phpunit_Interface_Request::PARAMETER_KEY_TESTABLE);

        if ($this->request->hasString(Tx_Phpunit_Interface_Request::PARAMETER_KEY_TESTABLE)) {
            $selectedTestableKey = $this->request->getAsString(Tx_Phpunit_Interface_Request::PARAMETER_KEY_TESTABLE);
        } else {
            $selectedTestableKey = $testableKeyFromSettings;
        }

        if (($selectedTestableKey !== Tx_Phpunit_Testable::ALL_EXTENSIONS)
            && !$this->testFinder->existsTestableForKey($selectedTestableKey)
        ) {
            // We know that phpunit must be loaded.
            $selectedTestableKey = 'phpunit';
        }

        if ($selectedTestableKey !== $testableKeyFromSettings) {
            $this->userSettingsService->set(Tx_Phpunit_Interface_Request::PARAMETER_KEY_TESTABLE, $selectedTestableKey);
        }

        return $selectedTestableKey;
    }

    /**
     * Renders the intro screen for the function "Run tests".
     *
     * @return void
     */
    protected function renderRunTestsIntro()
    {
        /** @var FlashMessageService $flashMessageService */
        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $defaultFlashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
        if (!$this->testFinder->existsTestableForAnything()) {
            /** @var FlashMessage $message */
            $message = GeneralUtility::makeInstance(
                FlashMessage::class,
                $this->translate('could_not_find_exts_with_tests'),
                '',
                FlashMessage::WARNING
            );
            $defaultFlashMessageQueue->enqueue($message);
            $this->outputService->output($defaultFlashMessageQueue->renderFlashMessages());
            return;
        }

        /** @var FlashMessage $message */
        $message = GeneralUtility::makeInstance(
            FlashMessage::class,
            $this->translate('test_are_run_in_current_backend_context.message'),
            $this->translate('test_are_run_in_current_backend_context.title'),
            FlashMessage::WARNING
        );
        $defaultFlashMessageQueue->enqueue($message);
        $this->outputService->output($defaultFlashMessageQueue->renderFlashMessages());

        $this->outputService->output('<p><br /></p>');

        $this->createExtensionSelector();
        $selectedExtensionKey = $this->getAndSaveSelectedTestableKey();

        $output = '';
        if ($selectedExtensionKey !== Tx_Phpunit_Testable::ALL_EXTENSIONS) {
            $output .= $this->createTestCaseSelector($selectedExtensionKey) . $this->createTestSelector($selectedExtensionKey);
        }
        $output .= $this->createCheckboxes();

        $this->outputService->output($output);
    }

    /**
     * Creates the extension drop-down.
     *
     * @return void
     */
    protected function createExtensionSelector()
    {
        $this->getAndSaveSelectedTestableKey();

        /** @var Tx_Phpunit_ViewHelpers_ExtensionSelectorViewHelper $extensionSelectorViewHelper */
        $extensionSelectorViewHelper = GeneralUtility::makeInstance('Tx_Phpunit_ViewHelpers_ExtensionSelectorViewHelper');
        $extensionSelectorViewHelper->injectOutputService($this->outputService);
        $extensionSelectorViewHelper->injectUserSettingService($this->userSettingsService);
        $extensionSelectorViewHelper->injectTestFinder($this->testFinder);
        $extensionSelectorViewHelper->setAction(BackendUtility::getModuleUrl('tools_txphpunitbeM1'));

        $extensionSelectorViewHelper->render();
    }

    /**
     * Renders a drop-down for running single tests cases for the given extension.
     *
     * @param string $extensionKey
     *        keys of the extension for which to render the drop-down
     *
     * @return string
     *         HTML code with the drop-down and a surrounding form, will be empty
     *         if no loaded single extension is selected
     */
    protected function createTestCaseSelector($extensionKey)
    {
        if (!$this->testFinder->existsTestableForKey($extensionKey)) {
            return '';
        }

        $testsPathOfExtension = $this->testFinder->getTestableForKey($extensionKey)->getTestsPath();
        $testSuites = $this->testCaseService->findTestCaseFilesInDirectory($testsPathOfExtension);

        foreach ($testSuites as $fileName) {
            require_once($testsPathOfExtension . $fileName);
        }

        $testSuite = new PHPUnit_Framework_TestSuite('tx_phpunit_basetestsuite');
        foreach (get_declared_classes() as $className) {
            if ($this->testCaseService->isValidTestCaseClassName($className)) {
                $testSuite->addTestSuite($className);
            }
        }

        // testCaseFile
        $testCaseFileOptionsArray = array();
        /** @var PHPUnit_Framework_TestCase $testCase */
        foreach ($testSuite->tests() as $testCase) {
            $selected = ($testCase->toString() === $this->request->getAsString(Tx_Phpunit_Interface_Request::PARAMETER_KEY_TESTCASE))
                ? ' selected="selected"' : '';
            $testCaseFileOptionsArray[] = '<option value="' . $testCase->toString() . '"' . $selected . '>' .
                htmlspecialchars($testCase->getName()) . '</option>';
        }

        $currentStyle = $this->createIconStyle($extensionKey);

        return '<form action="' . htmlspecialchars(BackendUtility::getModuleUrl('tools_txphpunitbeM1')) . '" method="post">' .
        '<p>' .
        '<select style="' . $currentStyle . '" name="' . Tx_Phpunit_Interface_Request::PARAMETER_NAMESPACE . '[' .
        Tx_Phpunit_Interface_Request::PARAMETER_KEY_TESTCASE . ']">' .
        '<option value="">' . htmlspecialchars($this->translate('select_tests')) . '</option>' .
        implode(LF, $testCaseFileOptionsArray) . '</select>' .
        '<button type="submit" name="' . Tx_Phpunit_Interface_Request::PARAMETER_NAMESPACE . '[' .
        Tx_Phpunit_Interface_Request::PARAMETER_KEY_EXECUTE . ']" value="run" accesskey="c">' .
        $this->translate('runTestCaseFile') . '</button>' .
        '<input type="hidden" name="' . Tx_Phpunit_Interface_Request::PARAMETER_NAMESPACE . '[' .
        Tx_Phpunit_Interface_Request::PARAMETER_KEY_COMMAND . ']" value="runTestCaseFile" />' .
        '</p>' .
        '</form>';
    }

    /**
     * Renders a drop-down for running single tests for the given extension.
     *
     * @param string $extensionKey
     *        keys of the extension for which to render the drop-down
     *
     * @return string
     *         HTML code with the drop-down and a surrounding form
     */
    protected function createTestSelector($extensionKey)
    {
        if (!$this->testFinder->existsTestableForKey($extensionKey)) {
            return '';
        }

        $testSuite = new PHPUnit_Framework_TestSuite('tx_phpunit_basetestsuite');

        $testsPathOfExtension = $this->testFinder->getTestableForKey($extensionKey)->getTestsPath();
        $testSuites = $this->testCaseService->findTestCaseFilesInDirectory($testsPathOfExtension);

        foreach ($testSuites as $fileName) {
            require_once($testsPathOfExtension . $fileName);
        }

        foreach (get_declared_classes() as $className) {
            if ($this->testCaseService->isValidTestCaseClassName($className)) {
                $testSuite->addTestSuite($className);
            }
        }

        // single test case
        $testsOptionsArr = array();
        $testCaseFile = $this->request->getAsString(Tx_Phpunit_Interface_Request::PARAMETER_KEY_TESTCASE);
        $useHumanReadableOptionLabels = $this->userSettingsService->getAsBoolean('testdox');

        /** @var PHPUnit_Framework_TestSuite $testCase */
        foreach ($testSuite->tests() as $testCase) {
            if (($testCaseFile !== null) && ($testCase->getName() !== $testCaseFile)) {
                continue;
            }
            /** @var PHPUnit_Framework_TestCase $test */
            foreach ($testCase->tests() as $test) {
                if ($test instanceof PHPUnit_Framework_TestSuite) {
                    list($testSuiteName, $testName) = explode('::', $test->getName());
                    $testIdentifier = $testName . '(' . $testSuiteName . ')';
                } else {
                    $testName = $test->getName();
                    $testIdentifier = $test->toString();
                    $testSuiteName = strstr($testIdentifier, '(');
                    $testSuiteName = trim($testSuiteName, '()');
                }
                $selected = ($testIdentifier === $this->request->getAsString(Tx_Phpunit_Interface_Request::PARAMETER_KEY_TEST))
                    ? ' selected="selected"' : '';
                $testCaption = $useHumanReadableOptionLabels ? $this->namePrettifier->prettifyTestMethod($testName) : $testName;
                $testsOptionsArr[$testSuiteName][] .= '<option value="' . $testIdentifier . '"' . $selected . '>' .
                    htmlspecialchars($testCaption) . '</option>';
            }
        }

        if (count($testsOptionsArr) === 0) {
            return '';
        }

        // builds options for select (including option groups for test suites)
        $testOptionsHtml = '';
        foreach ($testsOptionsArr as $suiteName => $testArr) {
            $testOptionsHtml .= '<optgroup label="' . $suiteName . '">';
            foreach ($testArr as $testHtml) {
                $testOptionsHtml .= $testHtml;
            }
            $testOptionsHtml .= '</optgroup>';
        }

        $currentStyle = $this->createIconStyle($extensionKey);

        return '<form action="' . htmlspecialchars(BackendUtility::getModuleUrl('tools_txphpunitbeM1')) . '" method="post">
            <p>
            <select style="' . $currentStyle . '" name="' . Tx_Phpunit_Interface_Request::PARAMETER_NAMESPACE . '[' .
            Tx_Phpunit_Interface_Request::PARAMETER_KEY_TEST . ']">
            <option value="">' . $this->translate('select_tests') . '</option>' . $testOptionsHtml . '</select>
            <button type="submit" name="' . Tx_Phpunit_Interface_Request::PARAMETER_NAMESPACE . '[' .
            Tx_Phpunit_Interface_Request::PARAMETER_KEY_EXECUTE .
            ']" value="run" accesskey="s">' . $this->translate('run_single_test') . '</button>
            <input type="hidden" name="' . Tx_Phpunit_Interface_Request::PARAMETER_NAMESPACE . '[' .
            Tx_Phpunit_Interface_Request::PARAMETER_KEY_COMMAND .
            ']" value="runsingletest" />
            <input type="hidden" name="' . Tx_Phpunit_Interface_Request::PARAMETER_NAMESPACE . '[' .
            Tx_Phpunit_Interface_Request::PARAMETER_KEY_TESTCASE .
            ']" value="' . $testCaseFile . '" />
            </p>
            </form>';
    }

    /**
     * Renders the checkboxes for hiding or showing various test results.
     *
     * @return string
     *         HTML code with checkboxes and a surrounding form
     */
    protected function createCheckboxes()
    {
        $output = '<form action="' . htmlspecialchars(BackendUtility::getModuleUrl('tools_txphpunitbeM1')) . '" method="post">';
        $output .= '<div class="phpunit-controls">';
        $failureState = $this->userSettingsService->getAsBoolean('failure') ? 'checked="checked"' : '';
        $errorState = $this->userSettingsService->getAsBoolean('error') ? 'checked="checked"' : '';
        $skippedState = $this->userSettingsService->getAsBoolean('skipped') ? 'checked="checked"' : '';
        $successState = $this->userSettingsService->getAsBoolean('success') ? 'checked="checked"' : '';
        $incompleteState = $this->userSettingsService->getAsBoolean('incomplete') ? 'checked="checked"' : '';
        $showTime = $this->userSettingsService->getAsBoolean('showTime') ? 'checked="checked"' : '';
        $testdoxState = $this->userSettingsService->getAsBoolean('testdox') ? 'checked="checked"' : '';
        $output .= '<input type="checkbox" id="SET_success" ' . $successState . ' /><label for="SET_success">' . $this->translate('success') . '</label>';
        $output .= ' <input type="checkbox" id="SET_failure" ' . $failureState . ' /><label for="SET_failure">' . $this->translate('failure') . '</label>';
        $output .= ' <input type="checkbox" id="SET_skipped" ' . $skippedState . ' /><label for="SET_skipped">' . $this->translate('skipped') . '</label>';
        $output .= ' <input type="checkbox" id="SET_error" ' . $errorState . ' /><label for="SET_error">' . $this->translate('error') . '</label>';
        $output .= ' <input type="checkbox" id="SET_testdox" ' . $testdoxState .
            ' /><label for="SET_testdox">' . $this->translate('show_as_human_readable') . '</label>';
        $output .= ' <input type="checkbox" id="SET_incomplete" ' . $incompleteState .
            ' /><label for="SET_incomplete">' . $this->translate('incomplete') . '</label>';
        $output .= ' <input type="checkbox" id="SET_showTime" ' . $showTime .
            '/><label for="SET_showTime">' . $this->translate('show_time') . '</label>';

        $codecoverageDisable = '';
        $codecoverageForLabelWhenDisabled = '';
        if (!extension_loaded('xdebug')) {
            $codecoverageDisable = ' disabled="disabled"';
            $codecoverageForLabelWhenDisabled = ' title="' . $this->translate('code_coverage_requires_xdebug') . '"';
        }
        $codeCoverageState = $this->userSettingsService->getAsBoolean('codeCoverage') ? 'checked="checked"' : '';
        $output .= ' <input type="checkbox" id="SET_codeCoverage" ' . $codecoverageDisable . ' ' . $codeCoverageState .
            ' /><label for="SET_codeCoverage"' . $codecoverageForLabelWhenDisabled .
            '>' . $this->translate('collect_code_coverage_data') . '</label>';
        $runSeleniumTests = $this->userSettingsService->getAsBoolean('runSeleniumTests') ? 'checked="checked"' : '';
        $output .= ' <input type="checkbox" id="SET_runSeleniumTests" ' . $runSeleniumTests . '/><label for="SET_runSeleniumTests">' . $this->translate('run_selenium_tests') . '</label>';
        $output .= '</div>';
        $output .= '</form>';

        return $output;
    }

    /**
     * Renders the screen for the function "Run tests" which shows and runs the actual unit tests.
     *
     * @return void
     */
    protected function renderRunningTest()
    {
        $this->setPhpUnitErrorHandler();

        $selectedTestableKey = $this->getAndSaveSelectedTestableKey();
        $this->renderTestingHeader($selectedTestableKey);

        $this->renderReRunButton();

        $testablesToProcess = $this->collectTestablesToProcess($selectedTestableKey);

        $this->loadAllFilesContainingTestCasesForTestables($testablesToProcess);

        $testSuite = $this->createTestSuiteWithAllTestCases();

        $testResult = new PHPUnit_Framework_TestResult();

        $this->configureTestListener();
        $testResult->addListener($this->testListener);

        $this->testStatistics = GeneralUtility::makeInstance('Tx_Phpunit_BackEnd_TestStatistics');
        $this->testStatistics->start();

        if ($this->shouldCollectCodeCoverageInformation()) {
            $this->coverage = GeneralUtility::makeInstance('PHP_CodeCoverage');
            $this->coverage->start('phpunit');
        }

        if ($this->request->hasString(Tx_Phpunit_Interface_Request::PARAMETER_KEY_TEST)) {
            $this->runSingleTest($testSuite, $testResult);
        } elseif ($this->request->hasString(Tx_Phpunit_Interface_Request::PARAMETER_KEY_TESTCASE)) {
            $this->runTestCase($testSuite, $testResult);
        } else {
            $this->runAllTests($testSuite, $testResult);
        }

        $this->testStatistics->stop();
        $this->renderTestStatistics($testResult);

        $this->renderReRunButton();

        if ($this->shouldCollectCodeCoverageInformation()) {
            $this->renderCodeCoverage();
        }
    }

    /**
     * Sets the PHPUnit error handler to catch all errors.
     * This is important as PHPUnit only sets its error handler if nothing has been set before,
     * but typically an error handler is set during by TYPO3 during the initialization.
     *
     * @return void
     */
    protected function setPhpUnitErrorHandler()
    {
        set_error_handler(array('PHPUnit_Util_ErrorHandler', 'handleError'), E_ALL | E_STRICT);
    }

    /**
     * Renders and outputs the "Testing ..." header for the given testable key.
     *
     * @param string $testableKey the key of the selected testable
     *
     * @return void
     */
    protected function renderTestingHeader($testableKey)
    {
        if ($testableKey === Tx_Phpunit_Testable::ALL_EXTENSIONS) {
            $this->outputService->output('<h1>' . $this->translate('testing_all_extensions') . '</h1>');
        } else {
            $this->outputService->output(
                '<h1>' . $this->translate('testing_extension') . ': ' . htmlspecialchars($testableKey) . '</h1>'
            );
        }
    }

    /**
     * Collects the testables to process as directed by the given testable key.
     *
     * @param string $testableKey the key of the selected testable
     *
     * @return Tx_Phpunit_Testable[] the testables to process
     */
    protected function collectTestablesToProcess($testableKey)
    {
        if ($testableKey === Tx_Phpunit_Testable::ALL_EXTENSIONS) {
            $testablesToProcess = $this->testFinder->getTestablesForEverything();
        } else {
            $testablesToProcess = array($this->testFinder->getTestableForKey($testableKey));
        }

        return $testablesToProcess;
    }

    /**
     * Loads all files containing test cases for the given testables.
     *
     * @param Tx_Phpunit_Testable[] $testables the testables for which to load all test case files
     *
     * @return void
     */
    protected function loadAllFilesContainingTestCasesForTestables(array $testables)
    {
        /** @var Tx_Phpunit_Testable $testable */
        foreach ($testables as $testable) {
            $this->loadAllFilesContainingTestCasesForSingleTestable($testable);
        }
    }

    /**
     * Loads all files containing test cases for the given testable.
     *
     * @param Tx_Phpunit_Testable $testable the testable for which to load all test case files
     *
     * @return void
     */
    protected function loadAllFilesContainingTestCasesForSingleTestable(Tx_Phpunit_Testable $testable)
    {
        $testsPath = $testable->getTestsPath();
        $testCaseFileNames = $this->testCaseService->findTestCaseFilesInDirectory($testsPath);
        foreach ($testCaseFileNames as $testCaseFileName) {
            require_once(realpath($testsPath . $testCaseFileName));
        }
    }

    /**
     * Creates a test suite that contains all test cases in the systems (but filters out this extension's base test cases).
     *
     * @return PHPUnit_Framework_TestSuite the test suite with all test cases added
     */
    protected function createTestSuiteWithAllTestCases()
    {
        $testSuite = new PHPUnit_Framework_TestSuite('tx_phpunit_basetestsuite');

        foreach (get_declared_classes() as $className) {
            if ($this->testCaseService->isValidTestCaseClassName($className)) {
                $testSuite->addTestSuite($className);
            }
        }

        return $testSuite;
    }

    /**
     * Configures the test listener as defined in the user settings.
     *
     * @return void
     */
    protected function configureTestListener()
    {
        if ($this->userSettingsService->getAsBoolean('testdox')) {
            $this->testListener->useHumanReadableTextFormat();
        }

        if ($this->userSettingsService->getAsBoolean('showTime')) {
            $this->testListener->enableShowTime();
        }
    }

    /**
     * Runs a single test as given in the GET/POST variable.
     *
     * @param PHPUnit_Framework_TestSuite $testSuiteWithAllTestCases suite with all test cases
     * @param PHPUnit_Framework_TestResult $testResult the test result (will be modified)
     *
     * @return void
     */
    protected function runSingleTest(
        PHPUnit_Framework_TestSuite $testSuiteWithAllTestCases,
        PHPUnit_Framework_TestResult $testResult
    ) {
        $this->renderProgressbar();
        /** @var PHPUnit_Framework_TestSuite $testCases */
        foreach ($testSuiteWithAllTestCases->tests() as $testCases) {
            foreach ($testCases->tests() as $test) {
                if ($test instanceof PHPUnit_Framework_TestSuite) {
                    /** @var PHPUnit_Framework_TestSuite $test */
                    list($testSuiteName, $testName) = explode('::', $test->getName());
                    $this->testListener->setTestSuiteName($testSuiteName);
                    $testIdentifier = $testName . '(' . $testSuiteName . ')';
                } else {
                    /** @var \PHPUnit_Framework_SelfDescribing $test */
                    $testIdentifier = $test->toString();
                    $testIdentifierParts = explode('::', $testIdentifier);
                    $testSuiteName = $testIdentifierParts[0];
                    $this->testListener->setTestSuiteName($testSuiteName);
                }
                if ($testIdentifier === $this->request->getAsString(Tx_Phpunit_Interface_Request::PARAMETER_KEY_TEST)) {
                    if ($test instanceof PHPUnit_Framework_TestSuite) {
                        $this->testListener->setTotalNumberOfTests($test->count());
                    } else {
                        $this->testListener->setTotalNumberOfTests(1);
                    }
                    $this->outputService->output('<h2 class="testSuiteName">Testsuite: ' . $testCases->getName() . '</h2>');
                    $test->run($testResult);
                }
            }
        }
        if (!is_object($testResult)) {
            $this->outputService->output(
                '<h2 class="hadError">Error</h2><p>The test <strong> ' .
                htmlspecialchars($this->request->getAsString(Tx_Phpunit_Interface_Request::PARAMETER_KEY_TESTCASE)) .
                '</strong> could not be found.</p>'
            );
        }
    }

    /**
     * Runs a testcase as given in the GET/POST variable "testCaseFile".
     *
     * @param PHPUnit_Framework_TestSuite $testSuiteWithAllTestCases suite with all test cases
     * @param PHPUnit_Framework_TestResult $testResult the test result (will be modified)
     *
     * @return void
     */
    protected function runTestCase(
        PHPUnit_Framework_TestSuite $testSuiteWithAllTestCases,
        PHPUnit_Framework_TestResult $testResult
    ) {
        $testCaseFileName = $this->request->getAsString(Tx_Phpunit_Interface_Request::PARAMETER_KEY_TESTCASE);
        $this->testListener->setTestSuiteName($testCaseFileName);

        $suiteNameHasBeenDisplayed = false;
        $totalNumberOfTestCases = 0;
        foreach ($testSuiteWithAllTestCases->tests() as $testSuite) {
            /** @var \PHPUnit_Framework_TestSuite $testSuite */
            foreach ($testSuite->tests() as $test) {
                if ($test instanceof PHPUnit_Framework_TestSuite) {
                    $testIdentifierParts = explode('::', $test->getName());
                    $testIdentifier = $testIdentifierParts[0];
                } else {
                    $testIdentifier = get_class($test);
                }
                if ($testIdentifier === $testCaseFileName) {
                    if ($test instanceof PHPUnit_Framework_TestSuite) {
                        $totalNumberOfTestCases += $test->count();
                    } else {
                        $totalNumberOfTestCases++;
                    }
                }
            }
        }
        $this->testListener->setTotalNumberOfTests($totalNumberOfTestCases);
        $this->renderProgressbar();

        foreach ($testSuiteWithAllTestCases->tests() as $testSuite) {
            /** @var \PHPUnit_Framework_TestSuite $testSuite */
            foreach ($testSuite->tests() as $test) {
                if ($test instanceof PHPUnit_Framework_TestSuite) {
                    $testIdentifierParts = explode('::', $test->getName());
                    $testIdentifier = $testIdentifierParts[0];
                } else {
                    $testIdentifier = get_class($test);
                }
                if ($testIdentifier === $testCaseFileName) {
                    if (!$suiteNameHasBeenDisplayed) {
                        $this->outputService->output('<h2 class="testSuiteName">Testsuite: ' . $testCaseFileName . '</h2>');
                        $suiteNameHasBeenDisplayed = true;
                    }
                    $test->run($testResult);
                }
            }
        }
        if (!is_object($testResult)) {
            $this->outputService->output(
                '<h2 class="hadError">Error</h2><p>The test <strong> ' .
                htmlspecialchars($this->request->getAsString(Tx_Phpunit_Interface_Request::PARAMETER_KEY_TEST)) .
                '</strong> could not be found.</p>'
            );
            return;
        }
    }

    /**
     * Runs all tests.
     *
     * @param PHPUnit_Framework_TestSuite $testSuiteWithAllTestCases suite with all test cases
     * @param PHPUnit_Framework_TestResult $testResult the test result (will be modified)
     *
     * @return void
     */
    protected function runAllTests(
        PHPUnit_Framework_TestSuite $testSuiteWithAllTestCases,
        PHPUnit_Framework_TestResult $testResult
    ) {
        $this->testListener->setTotalNumberOfTests($testSuiteWithAllTestCases->count());
        $this->renderProgressbar();
        $testSuiteWithAllTestCases->run($testResult);
    }

    /**
     * Renders and output the tests statistics.
     *
     * @param PHPUnit_Framework_TestResult $testResult the test result
     *
     * @return void
     */
    protected function renderTestStatistics(PHPUnit_Framework_TestResult $testResult)
    {
        if ($testResult->wasSuccessful()) {
            $testStatistics = '<h2 class="wasSuccessful">' . $this->translate('testing_success') . '</h2>';
        } else {
            if ($testResult->errorCount() > 0) {
                $testStatistics = '<script type="text/javascript">/*<![CDATA[*/setProgressBarClass("hadError");/*]]>*/</script>
                    <h2 class="hadError">' . $this->translate('testing_failure') . '</h2>';
            } else {
                $testStatistics = '<script type="text/javascript">/*<![CDATA[*/setProgressBarClass("hadFailure");/*]]>*/</script>
                    <h2 class="hadFailure">' . $this->translate('testing_failure') . '</h2>';
            }
        }
        $testStatistics .= '<p>' . $testResult->count() . ' ' . $this->translate('tests_total') . ', ' . $this->testListener->assertionCount() . ' ' .
            $this->translate('assertions_total') . ', ' . $testResult->failureCount() . ' ' . $this->translate('tests_failures') .
            ', ' . $testResult->skippedCount() . ' ' . $this->translate('tests_skipped') . ', ' .
            $testResult->notImplementedCount() . ' ' . $this->translate('tests_incomplete') . ', ' . $testResult->errorCount() .
            ' ' . $this->translate('tests_errors') . ', <span title="' . $this->testStatistics->getTime() . '&nbsp;' .
            $this->translate('tests_seconds') . '">' . round($this->testStatistics->getTime(), 3) . '&nbsp;' .
            $this->translate('tests_seconds') . '</span></p>';
        $this->outputService->output($testStatistics);

    }

    /**
     * Renders and output the re-run button.
     *
     * @return void
     */
    protected function renderReRunButton()
    {
        $this->outputService->output(
            '<form action="' . htmlspecialchars(BackendUtility::getModuleUrl('tools_txphpunitbeM1')) . '" method="post">
            <p>
            <button type="submit" name="' . Tx_Phpunit_Interface_Request::PARAMETER_NAMESPACE . ' [' .
            Tx_Phpunit_Interface_Request::PARAMETER_KEY_EXECUTE . ']" value="run" accesskey="r">' .
            $this->translate('run_again') . '</button>
            <input name="' . Tx_Phpunit_Interface_Request::PARAMETER_NAMESPACE . '[' .
            Tx_Phpunit_Interface_Request::PARAMETER_KEY_COMMAND . ']" type="hidden" value="' .
            htmlspecialchars($this->request->getAsString(Tx_Phpunit_Interface_Request::PARAMETER_KEY_COMMAND)) . '" />
            <input name="' . Tx_Phpunit_Interface_Request::PARAMETER_NAMESPACE . '[' .
            Tx_Phpunit_Interface_Request::PARAMETER_KEY_TEST . ']" type="hidden" value="' .
            htmlspecialchars($this->request->getAsString(Tx_Phpunit_Interface_Request::PARAMETER_KEY_TEST)) . '" />
            <input name="' . Tx_Phpunit_Interface_Request::PARAMETER_NAMESPACE . '[' .
            Tx_Phpunit_Interface_Request::PARAMETER_KEY_TESTCASE . ']" type="hidden" value="' .
            htmlspecialchars($this->request->getAsString(Tx_Phpunit_Interface_Request::PARAMETER_KEY_TESTCASE)) . '" />
            </p>
            </form>' .
            '<div id="testsHaveFinished"></div>');
    }

    /**
     * Renders and outputs the code coverage report.
     *
     * @return void
     */
    protected function renderCodeCoverage()
    {
        $this->coverage->stop();

        $codeCoverageDirectory = PATH_site . 'typo3temp/codecoverage/';
        if (!is_readable($codeCoverageDirectory) && !is_dir($codeCoverageDirectory)) {
            GeneralUtility::mkdir($codeCoverageDirectory);
        }

        $coverageReport = new PHP_CodeCoverage_Report_HTML();
        $coverageReport->process($this->coverage, $codeCoverageDirectory);
        $this->outputService->output(
            '<p><a target="_blank" href="../typo3temp/codecoverage/index.html">' .
            'Click here to access the Code Coverage report</a></p>' .
            '<p>Memory peak usage: ' . GeneralUtility::formatSize(memory_get_peak_usage()) . 'B</p>'
        );
    }

    /**
     * Renders DIVs which contain information and a progressbar to visualize
     * the running tests.
     *
     * The actual information will be written via JS during
     * the test runs.
     *
     * @return void
     */
    protected function renderProgressbar()
    {
        /** @var Tx_Phpunit_ViewHelpers_ProgressBarViewHelper $progressBarViewHelper */
        $progressBarViewHelper = GeneralUtility::makeInstance('Tx_Phpunit_ViewHelpers_ProgressBarViewHelper');
        $progressBarViewHelper->injectOutputService($this->outputService);
        $progressBarViewHelper->render();
    }

    /*********************************************************
     *
     * Helper functions
     *
     *********************************************************/

    /**
     * Recursively finds all test case files in the directory $directory.
     *
     * @param string $directory
     *        the absolute path of the directory in which to look for test cases,
     *        must not be empty
     *
     * @return array[]
     *         files names of the test cases in the directory $dir and all
     *         its subdirectories relative to $dir, will be empty if no
     *         test cases have been found
     */
    protected function findTestCasesInDir($directory)
    {
        if (!is_dir($directory)) {
            return array();
        }

        $testCaseFileNames = $this->testCaseService->findTestCaseFilesInDirectory($directory);

        $extensionsArr = array();
        if (!empty($testCaseFileNames)) {
            $extensionsArr[$directory] = $testCaseFileNames;
        }

        return $extensionsArr;
    }

    /**
     * Includes all PHP files given in $paths.
     *
     * @param array[] $paths
     *        array keys: absolute path
     *        array values: array of file names in that path
     *
     * @return void
     */
    protected function loadRequiredTestClasses(array $paths)
    {
        foreach ($paths as $path => $fileNames) {
            foreach ($fileNames as $fileName) {
                require_once(realpath($path . '/' . $fileName));
            }
        }
    }

    /**
     * Creates the CSS style attribute content for an icon for the extension
     * $extensionKey.
     *
     * @param string $extensionKey
     *        the key of a loaded extension, may also be "typo3"
     *
     * @return string the content for the "style" attribute, will not be empty
     *
     * @throws Tx_Phpunit_Exception_NoTestsDirectory
     *         if there is not extension with tests with the given key
     */
    protected function createIconStyle($extensionKey)
    {
        if ($extensionKey === '') {
            throw new Tx_Phpunit_Exception_NoTestsDirectory('$extensionKey must not be empty.', 1303503647);
        }
        if (!$this->testFinder->existsTestableForKey($extensionKey)) {
            throw new Tx_Phpunit_Exception_NoTestsDirectory(
                'The extension ' . $extensionKey . ' is not loaded.',
                1303503664
            );
        }

        $testable = $this->testFinder->getTestableForKey($extensionKey);

        return 'background: url(' . $testable->getIconPath() . ') 3px 50% white no-repeat; padding: 1px 1px 1px 24px;';
    }

    /**
     * Checks whether code coverage information should be collected.
     *
     * @return bool whether code coverage information should be collected
     */
    protected function shouldCollectCodeCoverageInformation()
    {
        return $this->userSettingsService->getAsBoolean('codeCoverage') && extension_loaded('xdebug');
    }
}
