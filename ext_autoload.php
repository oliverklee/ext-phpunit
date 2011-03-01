<?php
$extensionPath = t3lib_extMgm::extPath('phpunit');
return array(
	'tx_phpunit_backend_testlistener' => $extensionPath . 'Classes/BackEnd/TestListener.php',
	'tx_phpunit_cli_testrunner' => $extensionPath . 'Classes/Cli/TestRunner.php',
	'tx_phpunit_database_testcase' => $extensionPath . 'Classes/Database/TestCase.php',
	'tx_phpunit_reports_status' => $extensionPath . 'Classes/Reports/Status.php',
	'tx_phpunit_service_notestsdirectoryexception' => $extensionPath . 'Classes/Service/NoTestsDirectoryException.php',
	'tx_phpunit_service_testfinder' => $extensionPath . 'Classes/Service/TestFinder.php',
	'tx_phpunit_testablecode' => $extensionPath . 'Classes/TestableCode.php',
	'tx_phpunit_testcase' => $extensionPath . 'Classes/TestCase.php',
	'tx_phpunit_module1' => $extensionPath . 'mod1/class.tx_phpunit_module1.php',
	'tx_phpunit_module1_ajax' => $extensionPath . 'mod1/class.tx_phpunit_module1_ajax.php',
	'tx_phpunit_test_testsuite' => $extensionPath . 'Tests/tx_phpunit_testsuite.php',
	'vfsstream' => $extensionPath . 'PEAR/vfsStream/vfsStream.php',
	'vfsstreamabstractContent' => $extensionPath . 'PEAR/vfsStream/vfsStreamAbstractContent.php',
	'vfsstreamcontainer' => $extensionPath . 'PEAR/vfsStream/vfsStreamContainer.php',
	'vfsstreamcontaineriterator' => $extensionPath . 'PEAR/vfsStream/vfsStreamContainerIterator.php',
	'vfsstreamcontent' => $extensionPath . 'PEAR/vfsStream/vfsStreamContent.php',
	'vfsstreamdirectory' => $extensionPath . 'PEAR/vfsStream/vfsStreamDirectory.php',
	'vfsstreamexception' => $extensionPath . 'PEAR/vfsStream/vfsStreamException.php',
	'vfsstreamfile' => $extensionPath . 'PEAR/vfsStream/vfsStreamFile.php',
	'vfsstreamwrapper' => $extensionPath . 'PEAR/vfsStream/vfsStreamWrapper.php',
);
?>