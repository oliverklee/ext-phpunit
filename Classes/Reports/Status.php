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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Lang\LanguageService;
use TYPO3\CMS\Reports\Status;
use TYPO3\CMS\Reports\StatusProviderInterface;

/**
 * This class provides a status report for the "Reports" BE module.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Phpunit_Reports_Status implements StatusProviderInterface {
	/**
	 * @var string
	 */
	const MEMORY_REQUIRED = '128M';
	/**
	 * @var string
	 */
	const MEMORY_RECOMMENDED = '256M';

	/**
	 * @var Tx_Phpunit_Interface_ExtensionSettingsService
	 */
	protected $extensionSettingsService = NULL;

	/**
	 * The constructor.
	 */
	public function __construct() {
		$this->extensionSettingsService = GeneralUtility::makeInstance('Tx_Phpunit_Service_ExtensionSettingsService');
	}

	/**
	 * The destructor.
	 */
	public function __destruct() {
		unset($this->extensionSettingsService);
	}

	/**
	 * Returns the status of this extension.
	 *
	 * @return Status[]
	 *         status reports for this extension
	 */
	public function getStatus() {
		return array(
			$this->getReflectionStatus(),
			$this->getEacceleratorStatus(),
			$this->getXdebugStatus(),
			$this->getMemoryLimitStatus(),
			$this->getIncludePathStatus(),
			$this->getExcludedExtensionsStatus(),
		);
	}

	/**
	 * Translates a localized string.
	 *
	 * @param string $subkey
	 *        the part of the key to translate (without the
	 *        "LLL:EXT:phpunit/Resources/Private/Language/locallang_report.xlf:" prefix)
	 *
	 * @return string the localized string for $subkey, might be empty
	 */
	protected function translate($subkey) {
		return $this->getLanguageService()->sL(
			'LLL:EXT:phpunit/Resources/Private/Language/locallang_report.xlf:' . $subkey
		);
	}

	/**
	 * Creates a status concerning whether PHP reflection works correctly.
	 *
	 * @return Status
	 *         a status indicating whether PHP reflection works correctly
	 */
	protected function getReflectionStatus() {
		$heading = $this->translate('status_phpComments');

		$method = new ReflectionMethod('tx_phpunit_Reports_Status', 'getStatus');
		if (strlen($method->getDocComment()) > 0) {
			/** @var $status Status */
			$status = GeneralUtility::makeInstance(
				'TYPO3\\CMS\\Reports\\Status',
				$heading,
				$this->translate('status_phpComments_present_short'),
				$this->translate('status_phpComments_present_verbose'),
				Status::OK
			);
		} else {
			/** @var $status Status */
			$status = GeneralUtility::makeInstance(
				'TYPO3\\CMS\\Reports\\Status',
				$heading,
				$this->translate('status_phpComments_stripped_short'),
				$this->translate('status_phpComments_stripped_verbose'),
				Status::ERROR
			);
		}

		return $status;
	}

	/**
	 * Creates a status concerning eAccelerator not crashing phpunit.
	 *
	 * @return Status
	 *         a status concerning eAccelerator not crashing phpunit
	 */
	protected function getEacceleratorStatus() {
		$heading = $this->translate('status_eAccelerator');

		if (!extension_loaded('eaccelerator')) {
			/** @var $status Status */
			$status = GeneralUtility::makeInstance(
				'TYPO3\\CMS\\Reports\\Status',
				$heading,
				$this->translate('status_eAccelerator_notInstalled_short'),
				'',
				Status::OK
			);
		} else {
			$version = phpversion('eaccelerator');

			if (version_compare($version, '0.9.5.2', '<')) {
				$verboseMessage = sprintf(
					$this->translate('status_eAccelerator_installedOld_verbose'),
					$version
				);

				/** @var $status Status */
				$status = GeneralUtility::makeInstance(
					'TYPO3\\CMS\\Reports\\Status',
					$heading,
					$this->translate('status_eAccelerator_installedOld_short'),
					$verboseMessage,
					Status::ERROR
				);
			} else {
				$verboseMessage = sprintf(
					$this->translate('status_eAccelerator_installedNew_verbose'),
					$version
				);

				/** @var $status Status */
				$status = GeneralUtility::makeInstance(
					'TYPO3\\CMS\\Reports\\Status',
					$heading,
					$this->translate('status_eAccelerator_installedNew_short'),
					$verboseMessage,
					Status::OK
				);
			}
		}

		return $status;
	}

	/**
	 * Creates a status concerning whether Xdebug is loaded.
	 *
	 * @return Status
	 *         a status concerning whether Xdebug is loaded
	 */
	protected function getXdebugStatus() {
		if (extension_loaded('xdebug')) {
			$messageKey = 'status_loaded';
		} else {
			$messageKey = 'status_notLoaded';
		}

		return GeneralUtility::makeInstance(
			'TYPO3\\CMS\\Reports\\Status',
			$this->translate('status_xdebug'),
			$this->translate($messageKey),
			'',
			Status::NOTICE
		);
	}

	/**
	 * Creates a status concerning the PHP memory limit.
	 *
	 * @return Status
	 *         a status indicating whether the PHP memory limit is high enogh
	 */
	protected function getMemoryLimitStatus() {
		$memoryLimitFromConfiguration = ini_get('memory_limit');
		$memoryLimitInBytes = GeneralUtility::getBytesFromSizeMeasurement($memoryLimitFromConfiguration);
		$requiredMemoryLimitInBytes = GeneralUtility::getBytesFromSizeMeasurement(self::MEMORY_REQUIRED);
		$recommendedMemoryLimitInBytes = GeneralUtility::getBytesFromSizeMeasurement(self::MEMORY_RECOMMENDED);

		$heading = $this->translate('status_memoryLimit');
		$message = sprintf(
			$this->translate('status_memoryLimit_tooLittle'),
			self::MEMORY_REQUIRED, self::MEMORY_RECOMMENDED
		);

		if ($memoryLimitInBytes < $requiredMemoryLimitInBytes) {
			/** @var $status Status */
			$status = GeneralUtility::makeInstance(
				'TYPO3\\CMS\\Reports\\Status',
				$heading,
				$memoryLimitFromConfiguration,
				$message,
				Status::ERROR
			);
		} elseif ($memoryLimitInBytes < $recommendedMemoryLimitInBytes) {
			/** @var $status Status */
			$status = GeneralUtility::makeInstance(
				'TYPO3\\CMS\\Reports\\Status',
				$heading,
				$memoryLimitFromConfiguration,
				$message,
				Status::WARNING
			);
		} else {
			/** @var $status Status */
			$status = GeneralUtility::makeInstance(
				'TYPO3\\CMS\\Reports\\Status',
				$heading,
				$memoryLimitFromConfiguration,
				'',
				Status::OK
			);
		}

		return $status;
	}

	/**
	 * Creates a status about the PHP include path.
	 *
	 * @return Status
	 *         a status about the PHP include path
	 */
	protected function getIncludePathStatus() {
		$paths = explode(PATH_SEPARATOR, get_include_path());

		$escapedPaths = array();
		foreach ($paths as $path) {
			$escapedPaths[] = htmlspecialchars($path);
		}

		return GeneralUtility::makeInstance(
			'TYPO3\\CMS\\Reports\\Status',
			$this->translate('status_includePath'),
			'',
			'<code>' . nl2br(htmlspecialchars(implode(LF, $escapedPaths))) . '</code>',
			Status::NOTICE
		);
	}

	/**
	 * Creates a status about the extensions that are excluded from unit testing.
	 *
	 * @return Status
	 *         a status about the excluded extensions
	 */
	protected function getExcludedExtensionsStatus() {
		$extensionKeys = GeneralUtility::trimExplode(',', $this->extensionSettingsService->getAsString('excludeextensions'));

		return GeneralUtility::makeInstance(
			'TYPO3\\CMS\\Reports\\Status',
			$this->translate('status_excludedExtensions'),
			'',
			nl2br(htmlspecialchars(implode(LF, $extensionKeys))),
			Status::NOTICE
		);
	}

	/**
	 * Returns $GLOBALS['LANG'].
	 *
	 * @return LanguageService
	 */
	protected function getLanguageService() {
		return $GLOBALS['LANG'];
	}
}