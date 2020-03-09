# Change log

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](https://semver.org/).

## 6.5.15

### Fixed
- Security: Prevent execution of `eval-stdin.php` via the web server (#165)

## 6.5.14

### Added
- Add support for TYPO3 9.5 (#126)
- Add support for PHP 7.3 (#124)

### Changed
- Upgrade PHPUnit to 6.5.14 (#135)
- Upgrade helhum/typo3-console (#129)
- Use PHP 7.0 language features (#131)
- Move Tests/ from autoload-dev to autoload (#133)
- Completely switch to PSR-4 autoloading (#125)
- Simplify the test run command configuration in .travis.yml (#123)
- Require PHP >= 7.0 (#121)
- Require TYPO3 8.7 (#119)

### Removed
- Drop the legacy CLI test runner (#120)
- Drop the back-end module (#118)
- Remove the UI for code coverage (#116)
- Drop the IDE test runner (#114, #117)
- Drop the testing framework (#113, #115)
- Drop the time statistics (#112)
- Drop DatabaseTestCase (#111)
- Drop the Selenium integration (#110)

### Fixed
- Use more PHPUnit 5.7 features (#132)
- Require symfony/console (#127)
- Drop a removed directory from the PHP linting (#130)
- Stop requesting an upload folder (#121)

## 5.7.27

### Added
- Run Travis CI with highest and lowest dependencies (#107)
- Add option to pass CLI options to PHPUnit (#102)
- Add TestCase::getProtectedProperty (#91)
- Auto-release to the TER (#67)

### Changed
- Change the license from GPL V3+ to GPL V2+ (#108)
- Streamline ext_localconf.php and ext_tables.php (#103, #105)
- Namespace the non-deprecated classes and interfaces (#96)
- Update PHPUnit to 5.7 (#93, #94)
- Stop using a PHAR for including libraries (#87)
- Use spaces for indenting SQL and .htaccess files (#78)
- Streamline ext_emconf.php (#74, #75)
- Move the inclusion of the PHPUnit library to a single location (#65)

### Deprecated
- Change all deprecations to be removed in PHPUnit 7 (#80)
- Deprecate Tx_Phpunit_Framework (#76)
- Deprecate Tx_Phpunit_Selenium_TestCase (#76)
- Deprecate Tx_Phpunit_Database_TestCase (#76)
- Deprecate Tx_Phpunit_Service_Database (#76)

### Removed
- Drop the destructors (#99)
- Drop the ancient unused TestSuite class (#97)
- Drop the direct phpunit/phpunit dependency (#90)
- Remove the unused PHPUnit configuration file (#76)
- Remove the obsolete ext_autoload.php (#56)

### Fixed
- Fix code inspection warnings (#106)
- Fix the casing of the RunTestsCommand namespace (#101)
- Stop using the Core base exception class (#100)
- Fix the autoloading in RunTestsCommand (#95)
- Use the DB connection pool in TYPO3 8LTS (#92)
- Fix PHPUnit inclusion in the BE module (#89)
- Add the missing vfsStream library (#88)
- Drop the deprecated "replace" from composer.json (#86)
- Explicitly require MySQL on Travis CI (#85)
- Explicitly provide the extension name in the composer.json (#84)
- Fix the casing of the vfsstream package (#82)
- Also have the extension icon in Resources/ (#81)
- Only clean up tables that have a dummy column (#79)
- Fix the PHAR inclusion in TYPO3 8.7.17 (#59, #60)
- Only include the PHAR from the test runners (#57, #53)
- Use the DB name from the connection pool in TYPO3 >= 8.7 (#58, #55)
- Hide the test tables from BE user table permission lists (#52)
- Simplify the fake frontend teardown (#51)

## 5.3.5

### Added
- Support PHP 7.1 and 7.2 (#44)
- Add a new CLI test runner and use it in 8.7 (#42)
- Install TYPO3 8.7 on Travis (#41)

### Changed
- Use expectException (#43)
- Upgrade to PHPUnit 5.3.x (#38)
- Use PSR-4 autoloading for the test classes (#35)

### Deprecated
- The old CLI and IDE test runners will be removed in PHPUnit 6.

### Removed
- Remove the FunctionalTestCaseTrait (#39)
- Drop support for PHP 5.5 (#37)
- Drop the incorrect TYPO3 Core license headers (#34)

### Fixed
- Fix deprecation warnings in TYPO3 8.7 (#49)
- Update the TCA for TYPO3 8.7 (#48)
- Use getAbsoluteWebPath instead of extRelPath (#47)
- Make the unit tests not depend on the current time of day (#36)
- Provide cli_dispatch.phpsh for 8.7 on Travis (#33)

## 4.8.37

### Added
- run the unit tests on TravisCI
- Composer script for PHP linting
- add TravisCI builds

### Changed
- move the extension to GitHub

### Deprecated
- Deprecate a bunch of classes (#31)

### Removed
- remove the generated API documentation

### Fixed
- Always call dynamic methods dynamically (#16)
- Fix travis builds with TYPO3 CMS 7.6 (#12)
- Avoid serialization error in the Selenium tests (#11)
- fix the "replace" section in the composer.json of the test extensions

## 4.8.36

The [change log up to version 4.8.36](Documentation/changelog-archive.txt)
has been archived.
