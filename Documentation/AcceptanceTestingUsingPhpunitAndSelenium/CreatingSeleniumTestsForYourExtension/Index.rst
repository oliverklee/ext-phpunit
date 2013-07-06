

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


Creating Selenium tests for your extension
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

- In order to create a Selenium test, you need to create a directory
  :code:`Tests/Selenium/` in your extension directory and add some PHP
  files ending with :code:`*Test.php` containing a class
  extending :code:`Tx_Phpunit_Selenium_TestCase`with functions that
  :code`@test` annotation.

- A test for testing if the text 'Hello World!' is present at a page
  with the ID=1 would look like this:
::

  /**
   * @test
   */
  public function helloWorldIsPresent() {
    $this->open('http://localhost/index.php?id=1');
    $this->assertTextPresent('HelloWorld!');
  }

- Consult the `PHPUnit documentation <http://phpunit.de/manual/current/en/>`_ for information on writing
  tests, available functions etc. See Chapter 17 for details on using
  PHPUnit for Selenium tests.

