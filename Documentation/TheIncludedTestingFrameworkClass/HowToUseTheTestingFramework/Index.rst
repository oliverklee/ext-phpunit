

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


How to use the testing framework
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^


Getting it all started
""""""""""""""""""""""

First of all, you need to create an instance of the testing framework
that is specific to you extension (by providing the key of your
extension including the :code:`tx\_` prefix) and have it clean up after the
tests:

::

   /**
    * @var Tx_Phpunit_Framework
    */
   private $testingFramework;

   public function setUp() {
     $this->testingFramework = new Tx_Phpunit_Framework('tx_news2');

     $this->fixture = new ...;
   }

   public function tearDown() {
     $this->testingFramework->cleanUp();

     unset($this->fixture, $this->testingFramework);
   }


Using dummy database records
""""""""""""""""""""""""""""

If you would like to create dummy records in the database for your
unit tests, you'll need to add a column to your tables so that the
framework can mark records as dummy records. For your extension's own
tables, the column needs to look like this:

::

   is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
   ...
   KEY phpunit_dummy (is_dummy_record)

For tables of other extensions, the column name needs to include your
extension's key and and underscore as prefix before the
“is\_dummy\_record” (which also is the way the extension manager
requires):

::

   tx_news2_is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
   ...
   KEY phpunit_dummy (tx_news2_is_dummy_record)

If you need to modify tables of other extensions, you'll also need to
provide the names of these tables as the second constructor parameter
for the framework:

::

   $this->testingFramework = new Tx_Phpunit_Framework('tx_news2', array('tx_foo', 'tx_bar'));

For system tables, the  *phpunit* extension already provides the
correct “is\_dummy” columns, so you so not need to add them yourself.

You then can create records:

::

   $uid = $this->fixture->createRecord(
     'tx_phpunit_test',
     array('title' => $title)
   );
   $pid = $this->fixture->createFrontEndPage(0, array('title' => 'foo'));


Using a fake front end
""""""""""""""""""""""

You can easily create a fake front end and use front-end users:

::

   $this->testingFramework->createFakeFrontEnd();
   $feUserId = $this->testingFramework->createFrontEndUser();
   $this->testingFramework->loginFrontEndUser($feUserId);
   ...
   $this->testingFramework->createFakeFrontEnd();
   $feUserId = $this->testingFramework->createAndLogInFrontEndUser();


Using the clean-up hook
"""""""""""""""""""""""

If you would like some functions to always get called when the testing
framework's clean-up function is called (e.g. you would like to clean
your persistence layer, or you would like to clear some object cache),
you can implement the Tx\_Phpunit\_Interface\_Framework class. The
hook gets registered like this in your ext\_localconf.php:

::

   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['FrameworkCleanUp'][]
     = 'EXT:oelib/class.tx_oelib_TestingFrameworkCleanup.php:&tx_oelib_TestingFrameworkCleanup';

You can register multiple classes for this hook.


Where to find out more
""""""""""""""""""""""

For a complete list of all functions in the testing framework and all
parameters, please have a look at the framework's API documentation.
You can either view it as HTML in EXT:phpunit/doc/, or you could
include the framework source code in your IDE and then read the
function documentation and use your IDE's code help.

