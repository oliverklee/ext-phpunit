.. include:: Images.txt

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


Executing Selenium tests
^^^^^^^^^^^^^^^^^^^^^^^^

:For running the Selenium tests, you will need a running Selenium Server . You can download the JAR file from`
`http://seleniumhq.org/download/ <http://seleniumhq.org/download/>`_. Start it from the
console by running :code:`java -jar selenium-server-standalone-2.7.0.jar`
(the exact file name depends on the version you are using).

When running the PHPUnit test from the TYPO3 backend, make sure
you have checked the “Run Selenium Tests” checkbox (see picture
below).

|img-4|

