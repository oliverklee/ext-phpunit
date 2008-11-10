<?php defined('TYPO3_MODE') || die ('Access denied.');

$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY] = unserialize($_EXTCONF);

//$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['phpunit']);
clearstatcache();
$phpunitlib = '';
if (is_dir($TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['phpunitlib'])) {
	$phpunitlib .= $TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['phpunitlib'] . PATH_SEPARATOR;
}

if (!$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['usepear'] || !t3lib_extMgm::isLoaded('pear') ||
	(!is_dir(t3lib_extMgm::extPath('pear').'/PEAR/PHPUnit'))
	) {
	// If all above fails, then fall back to use own provided version of PHPUnit:
	// TODO: Automatically detect current version, see http://bugs.typo3.org/view.php?id=6969
	$phpunitlib .= t3lib_extMgm::extPath('phpunit').'PHPUnit-3.3.4/';
}

// Typo3 4.2 AJAX feature. See e.g. manual attached to issue #7096, http://bugs.typo3.org/view.php?id=7096
$TYPO3_CONF_VARS['BE']['AJAX']['tx_phpunit_module1_ajax'] = 'typo3conf/ext/phpunit/mod1/class.tx_phpunit_module1_ajax.php:tx_phpunit_module1_ajax->ajaxBroker';


define(TX_PHPUNITLIB_EXTPATH, $phpunitlib);
set_include_path(TX_PHPUNITLIB_EXTPATH . PATH_SEPARATOR . get_include_path());
?>