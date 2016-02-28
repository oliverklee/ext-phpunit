

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


Using another version of PHPUnit than the version provided with the extension
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

It is possible use any PHPUnit version (>4.6 but <5.0) with the extension, if you install TYPO3 and the extension using composer.
Just specify the desired PHPUnit version in your root composer.json and it will work out of the box if TYPO3 version is 7LTS
or the TYPO3_COMPOSER_AUTOLOAD constant is set for TYPO3 6.2.x

