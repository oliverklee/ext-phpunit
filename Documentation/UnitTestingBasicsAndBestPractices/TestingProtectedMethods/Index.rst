

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


Testing protected methods
^^^^^^^^^^^^^^^^^^^^^^^^^

Generally, it is recommended to test only those protected methods
directly that are part of the class' API, i.e., that are explicitly
intended to be used by subclasses. All other protected methods should
be tested indirectly through the existing public methods.


Using an accessible proxy created via eval()
""""""""""""""""""""""""""""""""""""""""""""

This is the recommended way to test protected methods.

There is an example of this in the TestFinder test:
::

  /**
    * Creates a subclass Tx\_Phpunit\_Service\_TestFinder with some protected
    * functions made public.
    *
    * @return Tx_Phpunit_Servic\_TestFinder an accessible proxy
    */
  protected function** createAccessibleProxy() {
    $className ='Tx\_Phpunit\_Service\_TestFinderAccessibleProxy';
    if (!class_exists($className, FALSE)) {
      eval (
        'class '. $className .' extends Tx_Phpunit_Service_TestFinder {' .
        '  public function isTestCaseFileName($path) {' .
        '    return parent::isTestCaseFileName($path);' .
        '  }' .
        '}'
      );
    }

    return new $className();
  }

  …

  $this->fixture= $this->createAccessibleProxy();


Using a subclass in a PHP file
""""""""""""""""""""""""""""""

This is just the same as with the accessible proxy, but the subclass
is located in a file (within :code:`Tests/Unit/Fixtures/`) instead of being created
via :code:`eval`.


Using Reflection
""""""""""""""""

This is not recommended.

However, if you insists, there is an `article by Sebastian Bergmann on
testing private and protected methods <http://sebastian-
bergmann.de/archives/881-Testing-Your-Privates.html>`_.


Using an accessible mock from the extbase base test class
"""""""""""""""""""""""""""""""""""""""""""""""""""""""""

This is not recommended.

If you insist, you can create an accessible mock with just a “dummy”
method mocked and then directly call the protected method:
::

  $fixture = $this->getAccessibleMock('Tx\_Foo\_Controller\_FooController', array('dummy'));
  $fixture->_call('setTargetDirectory', $targetPath);
