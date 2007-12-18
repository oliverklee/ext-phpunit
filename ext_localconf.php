<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['phpunit']);
clearstatcache();
$phpunitlib = '';
if (is_dir($extConf['phpunitlib'])) {
	$phpunitlib .= $extConf['phpunitlib'] . PATH_SEPARATOR;	
}

if (!$extConf['usepear'] || !t3lib_extMgm::isLoaded('pear') ||
	(!is_dir(t3lib_extMgm::extPath('pear').'/PEAR/PHPUnit'))
	) {
	// If all above fails, then fall back to use own provided version of PHPUnit:
	// TODO: Automatically detect current version, see http://bugs.typo3.org/view.php?id=6969
	$phpunitlib .= t3lib_extMgm::extPath('phpunit').'PHPUnit-3.2.6/';
}

define (TX_PHPUNITLIB_EXTPATH, $phpunitlib);
set_include_path(TX_PHPUNITLIB_EXTPATH . PATH_SEPARATOR . get_include_path());
?>