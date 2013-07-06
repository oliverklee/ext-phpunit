

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


The order of calls to the test listener when executing tests
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Between each startTest call and the endTest call, there's a call to
one of the following methods for non-successful tests:

- addError

- addFailure

- addIncompleteTest

- addSkippedTest


Running a single test (without data provider)
"""""""""""""""""""""""""""""""""""""""""""""

The test case is Tx\_Phpunit\_BackEnd\_Fixtures\_DataProviderTest, and
the test name is “test1”.

#. startTest(Tx\_Phpunit\_BackEnd\_Fixtures\_DataProviderTest, name =
   “test1”)

#. endTest(Tx\_Phpunit\_BackEnd\_Fixtures\_DataProviderTest, name =
   “test1”)


Running a single test (with a data provider for three data sets)
""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""

The test case is Tx\_Phpunit\_BackEnd\_Fixtures\_DataProviderTest, and
the test name is “dataProviderTest”.

Note: That the data provider keys are not displayed might be a
`bug/regression <http://forge.typo3.org/issues/12091>`_ either in the
PHPUnit package or the phpunit extension.

#. startTestSuite(PHPUnit\_Framework\_TestSuite\_DataProvider, name=”Tx\_
   Phpunit\_BackEnd\_Fixtures\_DataProviderTest::dataProviderTest”)

#. startTest(Tx\_Phpunit\_BackEnd\_Fixtures\_DataProviderTest, name =
   “dataProviderTest”)

#. endTest(Tx\_Phpunit\_BackEnd\_Fixtures\_DataProviderTest, name =
   “dataProviderTest”)

#. (steps 2 and 3 are repeated once for each additional data set)

#. endTestSuite(PHPUnit\_Framework\_TestSuite\_DataProvider, name=”Tx\_Ph
   punit\_BackEnd\_Fixtures\_DataProviderTest::dataProviderTest”)


Running a test case (including a data provider and some single tests)
"""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""

The test case is Tx\_Phpunit\_BackEnd\_Fixtures\_DataProviderTest.

#. startTest(Tx\_Phpunit\_BackEnd\_Fixtures\_DataProviderTest, name =
   “test1”)

#. endTest(Tx\_Phpunit\_BackEnd\_Fixtures\_DataProviderTest, name =
   “test1”)

#. (steps 1 and 2 are repeated once for each additional single test
   before the data provider test, including the correct test function
   name)

#. startTestSuite(PHPUnit\_Framework\_TestSuite\_DataProvider, name=”Tx\_
   Phpunit\_BackEnd\_Fixtures\_DataProviderTest::dataProviderTest”)

#. startTest(Tx\_Phpunit\_BackEnd\_Fixtures\_DataProviderTest, name =
   “dataProviderTest”)

#. endTest(Tx\_Phpunit\_BackEnd\_Fixtures\_DataProviderTest, name =
   “dataProviderTest”)

#. (steps 5 and 6 are repeated once for each additional data set)

#. endTestSuite(PHPUnit\_Framework\_TestSuite\_DataProvider, name=”Tx\_Ph
   punit\_BackEnd\_Fixtures\_DataProviderTest::dataProviderTest”)

#. startTest(Tx\_Phpunit\_BackEnd\_Fixtures\_DataProviderTest, name =
   “test3”)

#. endTest(Tx\_Phpunit\_BackEnd\_Fixtures\_DataProviderTest, name =
   “test3”)

#. (steps 9 and 10 are repeated once for each additional single test
   before the data provider test, including the correct test function
   name)


Running all test cases of an extension
""""""""""""""""""""""""""""""""""""""

#. startTestSuite(PHPUnit\_Framework\_TestSuite,
   name=”tx\_phpunit\_basetestsuite”)

#. startTestSuite(PHPUnit\_Framework\_TestSuite,
   name=”Tx\_Phpunit\_BackEnd\_Fixtures\_AnotherDataProviderTest”)

#. (then the same steps as when running a test case by itself)

#. endTestSuite(PHPUnit\_Framework\_TestSuite,
   name=”Tx\_Phpunit\_BackEnd\_Fixtures\_AnotherDataProviderTest”)

#. (steps 2 through 4 are repeated once for each additional test case)

#. endTestSuite(PHPUnit\_Framework\_TestSuite,
   name=”tx\_phpunit\_basetestsuite”)