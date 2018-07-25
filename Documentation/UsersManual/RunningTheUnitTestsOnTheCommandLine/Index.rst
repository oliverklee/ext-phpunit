

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


Running the unit tests on the command line in TYPO3 8.7
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

#. Make sure that there is at least one front-end page in your TYPO3
   installation.

#. Execute
   <path-to-your-typo3-installation>/typo3/sysext/core/bin/typo3
   phpunit:run --options="options for phpunit like --verbose -c phpunitIntegration.xml"


Running the unit tests on the command line in TYPO3 7.6
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

#. Create a non-admin back-end user named “\_cli\_phpunit”. This is the
   user which then will be used to run the tests.

#. Make sure that there is at least one front-end page in your TYPO3
   installation.

#. Make sure that your testcase files end with “\*Test.php”, not with
   “\*\_testcase.php”.

#. Execute <path-to-your-typo3-installation>/typo3/cli\_dispatch.phpsh
   phpunit <path-to-your-tests>

As the CLI BE user must not have administrator privileges, you might
need to write your unit tests to accommodate to that. This might
include the following things:

- If you need a logged-in BE user with certain data or with
  administrator privileges: create a mock BE user in $GLOBALS['BE\_USER']


Running the tests on the command line on MAMP/XAMPP etc.
""""""""""""""""""""""""""""""""""""""""""""""""""""""""

If you are using  **MAMP** , you need to edit your localconf.php. (The
problem is that MAMP uses another mysql socket than the default
socket.) To fix this, edit your localconf.php and add the socket
behind the typo3\_db host, e.g.:

::

   $typo_db_host = 'localhost:/Applications/MAMP/tmp/mysql/mysql.sock';

Applications like Xampp/Mamp on (Mac os X) provide their own PHP
binary which is needed to execute the CLi dispatcher. Example:

Won't work:

::

   $ ./typo3/cli_dispatch.phpsh phpunit typo3conf/ext/coffee/Tests/

Works:

::

   $ /Applications/XAMPP/xamppfiles/bin/php-5.3.1 ./typo3/cli_dispatch.phpsh phpunit typo3conf/ext/coffee/Tests/

