

.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. ==================================================
.. DEFINE SOME TEXTROLES
.. --------------------------------------------------
.. role::   underline
.. role::   typoscript(code)
.. role::   ts(typoscript)
   :class:  typoscript
.. role::   php(code)


Installation
^^^^^^^^^^^^

These are basic requirements for using this extension:

#. On rpm based systems, like Fedora and RHEL, you need the packages php-
   process and php-posix in addition to the normal list of PHP
   extensions.

#. Use the Extension Manager to download and install phpunit.

#. View the extension's options in the Extension Manager and save them at
   least once.

Please note that this extension will only work if it is installed directly
in typo3conf/ext/phpunit/. If it is symlinked, the PHAR inclusion will fail.

If you would like to run the unit tests of the phpunit extension
itself, you'll also need to install the following dummy extension
which are located in EXT:phpunit/TestExtensions/:

- aaa

- bbb

- ccc

- ddd

- user\_phpunittest

- user\_phpunittest2

For running database tests, you'll also need to provide the TYPO3
MySQL user with the following global permissions:

- SELECT

- INSERT

- CREATE

- DROP

- ALTER

If you would like to use a different PHPUnit version, make sure you install TYPO3 and the phpunit extension
via composer and specify the desired PHPUnit version in your root composer.json file.
