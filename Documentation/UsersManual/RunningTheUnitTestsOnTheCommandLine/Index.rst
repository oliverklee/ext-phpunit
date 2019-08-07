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


Running the tests on the command line on MAMP/XAMPP etc.
""""""""""""""""""""""""""""""""""""""""""""""""""""""""

If you are using  **MAMP** , you need to edit your localconf.php. (The
problem is that MAMP uses another MySQL socket than the default
socket.) To fix this, edit your localconf.php and add the socket
behind the typo3\_db host, e.g.:

::

   $typo_db_host = 'localhost:/Applications/MAMP/tmp/mysql/mysql.sock';

Applications like Xampp/MAMP on (Mac OS) provide their own PHP
binary which is needed to execute the CLi dispatcher. Example:

Won't work:

::

   $ ./typo3/sysext/core/bin/typo3 phpunit:run typo3conf/ext/coffee/Tests/

Works:

::

   $ /Applications/XAMPP/xamppfiles/bin/php-7.2.5 ./typo3/sysext/core/bin/typo3 phpunit:run typo3conf/ext/coffee/Tests/
