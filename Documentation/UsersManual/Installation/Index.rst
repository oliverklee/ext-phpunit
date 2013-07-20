

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

#. Make sure you are running TYPO3 >= 4.5.0 and PHP >= 5.3.0 (PHP >=
   5.3.9 is recommended).

#. On rpm based systems, like Fedora and RHEL, you need the packages php-
   process and php-posix in addition to the normal list of PHP
   extensions.

#. Use the Extension Manager to download and install phpunit.

#. View the extension's options in the Extension Manager and save them at
   least once.

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

If you would like to use a local PHPUnit package installed via Composer,
please make sure you have installed all necessary packages. Please add
at least "phpunit/phpunit", "phpunit/phpunit-selenium" and
"mikey179/vfsStream".
This is necessary to avoid problems with the PHPUnit and Composer
autoloader.