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
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

/**
 * This XCLASS makes sure no FE login cookies are sent during the unit tests.
 */
class Tx_Phpunit_FrontEnd_UserWithoutCookies extends FrontendUserAuthentication
{
    /**
     * @var bool
     */
    public $forceSetCookie = false;

    /**
     * @var bool
     */
    public $dontSetCookie = true;

    /**
     * Sets no session cookie at all.
     *
     * @return void
     */
    protected function setSessionCookie()
    {
    }

    /**
     * Unsets no cookie at all.
     *
     * @param string $cookieName
     *
     * @return void
     */
    public function removeCookie($cookieName)
    {
    }
}
