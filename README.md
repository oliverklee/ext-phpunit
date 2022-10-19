# PHPUnit TYPO3 extension

[![TYPO3 V11](https://img.shields.io/badge/TYPO3-11-orange.svg)](https://get.typo3.org/version/11)
[![TYPO3 V10](https://img.shields.io/badge/TYPO3-10-orange.svg)](https://get.typo3.org/version/10)
[![TYPO3 V9](https://img.shields.io/badge/TYPO3-9-orange.svg)](https://get.typo3.org/version/9)
[![GitHub CI Status](https://github.com/oliverklee/ext-phpunit/workflows/CI/badge.svg?branch=main)](https://github.com/oliverklee/ext-phpunit/actions)
[![Latest Stable Version](https://poser.pugx.org/oliverklee/phpunit/v/stable.svg)](https://packagist.org/packages/oliverklee/phpunit)
[![Total Downloads](https://poser.pugx.org/oliverklee/phpunit/downloads.svg)](https://packagist.org/packages/oliverklee/phpunit)
[![Latest Unstable Version](https://poser.pugx.org/oliverklee/phpunit/v/unstable.svg)](https://packagist.org/packages/oliverklee/phpunit)
[![License](https://img.shields.io/github/license/oliverklee/ext-phpunit)](https://packagist.org/packages/oliverklee/phpunit)

Unit testing for TYPO3. Includes PHPUnit and a CLI test runner.

This extension should be used for old projects that already use the PHPUnit
extension. For new projects, it is recommended to use either the
[Nimut testing framework](https://github.com/Nimut/testing-framework)
(if your extension supports multiple TYPO3 LTS versions) or the
[TYPO3 testing framework](https://github.com/TYPO3/testing-framework)
(if your extension needs to support only one TYPO3 LTS version at a time,
or if you cannot use symlinks) instead.

Most of the documentation is in ReST format
[in the Documentation/ folder](Documentation/) and is rendered
[as part of the TYPO3 documentation](https://docs.typo3.org/typo3cms/extensions/phpunit/).
