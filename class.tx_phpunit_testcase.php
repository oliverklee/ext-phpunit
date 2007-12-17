<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2005 Robert Lemke (robert@typo3.org)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is 
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
* 
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
* 
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * This class exists for the only reason that TYPO3 testcase inherit
 * a class called "tx_phpunitbe_testcase" instead of "PHPUnit_Framework_TestCase".
 */

require_once ('PHPUnit/Framework/TestCase.php');

class tx_phpunit_testcase extends PHPUnit_Framework_TestCase {
}

/*
 * This class is provided for backwards compatibility with the extension t3unit.
 * t3unit is based on PHPUnit version 2 (known as PHPUnit2)
 */
class tx_t3unit_testcase extends PHPUnit_Framework_TestCase {
}


?>