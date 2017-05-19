<?php
defined('TYPO3_MODE') or die('Access denied.');

if (TYPO3_MODE === 'BE') {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
        'tools',
        'txphpunitbeM1',
        '',
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Classes/BackEnd/'
    );
}
