<?php
$extensionPath = t3lib_extMgm::extPath('phpunit');
return array(
	'tx_phpunit_cli_testrunner' => $extensionPath . 'Classes/Cli/TestRunner.php',
	'tx_phpunit_database_testcase' => $extensionPath . 'class.tx_phpunit_database_testcase.php',
	'tx_phpunit_testcase' => $extensionPath . 'class.tx_phpunit_testcase.php',
	'tx_phpunit_backend_testlistener' => $extensionPath . 'Classes/BackEnd/TestListener.php',
	'tx_phpunit_module1' => $extensionPath . 'mod1/class.tx_phpunit_module1.php',
	'tx_phpunit_module1_ajax' => $extensionPath . 'mod1/class.tx_phpunit_module1_ajax.php',
	'tx_phpunit_reports_status' => $extensionPath . 'Classes/Reports/Status.php',
	'tx_phpunit_test_testcase' => $extensionPath . 'Tests/tx_phpunit_test_testcase.php',
	'tx_phpunit_test_testsuite' => $extensionPath . 'Tests/tx_phpunit_testsuite.php',
	'tx_phpunit_service_testfinder' => $extensionPath . 'Classes/Service/TestFinder.php',
	'vfsstream' => $extensionPath . 'PEAR/vfsStream/vfsStream.php',
);
?>