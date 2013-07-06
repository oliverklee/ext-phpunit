

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


Creating unit tests for your extension
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

- In order to create a unit test, you need to create a directory :code:`Tests/Unit/`
  in your extension directory and add some PHP files ending with :code:`*Test.php`
  containing a class with functions that have the :code:`@test` annotation.

- If you need to store data for fixtures, create a sub directory
  :code:`Tests/Fixtures/` or :code:`Tests/Unit/Fixtures/`.

- Consult the `PHPUnit documentation <http://www.phpunit.de/wiki/Documentation>`_
  for information on writing tests, available functions etc.


Things that work, but that are deprecated
=========================================

- :code:`tests/` (lowercase) as the name for the tests directory (instead of :code:`Tests/`
  (with a capital first letter)

- using :code:`_testcase.php` as suffix for test case files (instead of the
  :code:`*Test.php` suffix)

- using :code:`test_*` as prefix for test functions (instead of the :code:`@test` annotation)
