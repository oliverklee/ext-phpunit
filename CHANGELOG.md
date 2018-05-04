# Change log

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](https://semver.org/).

## x.y.z

### Added

### Changed

### Deprecated

### Removed

### Fixed
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
