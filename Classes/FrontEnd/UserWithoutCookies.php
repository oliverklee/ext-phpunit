<?php

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
