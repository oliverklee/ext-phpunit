

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


Running the unit tests in a continuous-integration environment
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

When running the unit tests in a CI environment, the tests are run in
TYPO3's CLI mode.

#. Set up your TYPO3 as for running the tests on the command line (see
   above for details).

#. Create a the following job in your continuous-integration server
   :<path-to-your-typo3-installation>/typo3/cli\_dispatch.phpsh phpunit
   <path-to-your-extension>

