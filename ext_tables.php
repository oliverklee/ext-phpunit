<?php
defined('TYPO3_MODE') or die('Access denied.');

if (TYPO3_MODE === 'BE'
    && \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) <= 8000000) {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
        'tools',
        'txphpunitbeM1',
        '',
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('phpunit') . 'Classes/BackEnd/'
    );
}
