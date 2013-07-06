

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


Writing tests for extensions with extbase
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Note: These instructions assume that you're using extbase >= 1.3.

For testing extbase-based extensions, your testcases need to
extend :code:`Tx_Extbase_Tests_Unit_BaseTestCase` instead of
:code:`Tx_Phpunit_TestCase`. This makes sure that the extbase autoloader
gets activated correctly.

If you are testing repository classes or any class that instantiates a
repository class, you will need to provide any repository instance
with a mock of :code:`Tx_Extbase_Object_ObjectManagerInterface` (or you you
skip the original constructor when creating a mock of a repository).

