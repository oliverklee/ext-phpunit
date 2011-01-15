<?php
$extensionPath = t3lib_extMgm::extPath('phpunit');
return array(
	'tx_phpunit_backend_testlistener' => $extensionPath . 'Classes/BackEnd/TestListener.php',
	'tx_phpunit_cli_testrunner' => $extensionPath . 'Classes/Cli/TestRunner.php',
	'tx_phpunit_database_testcase' => $extensionPath . 'Classes/Database/TestCase.php',
	'tx_phpunit_reports_status' => $extensionPath . 'Classes/Reports/Status.php',
	'tx_phpunit_service_testfinder' => $extensionPath . 'Classes/Service/TestFinder.php',
	'tx_phpunit_testcase' => $extensionPath . 'Classes/TestCase.php',
	'tx_phpunit_module1' => $extensionPath . 'mod1/class.tx_phpunit_module1.php',
	'tx_phpunit_module1_ajax' => $extensionPath . 'mod1/class.tx_phpunit_module1_ajax.php',
	'vfsstream' => $extensionPath . 'PEAR/vfsStream/vfsStream.php',
	'tx_phpunit_test_testsuite' => $extensionPath . 'Tests/tx_phpunit_testsuite.php',
);
?>