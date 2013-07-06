

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


Running the unit tests in PhpStorm
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

#. Follow and complete all steps in the previous chapter

#. Configure PhpStorm to use the shell script found in Resources/Scripts/
   as PHP binary

#. Edit the default run configuration of PHPUnit and add the environment
   variable named ENV\_TYPO3\_SITE\_PATH

#. Set the value of the variable to the absolute path of your TYPO3
   installation  **without** trailing slash.

#. Set the path to the phpunit extension as include path in PhpStorm

#. Now you can right-click any test, test file or test folder and select
   “run” to run the tests.

For more details and some screenshots you can read this blog post:

http://www.bitmotion.de/de/agentur/blogs/bitmotion/typo3-unit-test-phpstorm/

