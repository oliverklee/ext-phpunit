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

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class provides functions for reading data from a POST/GET request.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Phpunit_BackEnd_Request extends Tx_Phpunit_AbstractDataContainer
    implements Tx_Phpunit_Interface_Request, SingletonInterface
{
    /**
     * @var array
     */
    private $cachedRequestData = array();

    /**
     * @var bool
     */
    private $requestDataHasBeenRetrieved = false;

    /**
     * Returns the value stored for the key $key.
     *
     * @param string $key the key of the value to retrieve, must not be empty
     *
     * @return mixed the value for the given key, will be NULL if there is no value for the given key
     */
    protected function get($key)
    {
        $this->checkForNonEmptyKey($key);
        if (!$this->requestDataHasBeenRetrieved) {
            $this->retrieveRequestData();
        }
        if (!isset($this->cachedRequestData[$key])) {
            return null;
        }

        return $this->cachedRequestData[$key];
    }

    /**
     * Retrieves the EM configuration for the PHPUnit extension.
     *
     * @return void
     */
    protected function retrieveRequestData()
    {
        $this->cachedRequestData = GeneralUtility::_GP(Tx_Phpunit_Interface_Request::PARAMETER_NAMESPACE);

        $this->requestDataHasBeenRetrieved = true;
    }
}