

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

I is possible use any PHPUnit version 3.x with phpunit. If you already
have PHPUnit installed through PEAR, then you can use that version
instead; or you could run ‘bleeding edge’ GIT version directly from
the repository.

Using another PHPUnit version is easy if you remember these three
points:

#. Use the absolute path.

#. Remember the trailing slash in the absolute path.

#. The path should point to the directory before ‘PHPUnit’ directory.
   Example: If you have the Framework.php-file installed in
   c:\xampp\php\PEAR\PHPUnit, then you should point to the directory
   c:\xampp\php\PEAR\

You can verify the include path from within the phpunit extension via
the ‘About phpunit BE’ entry in the upper right corner of the back-end
module.

When you are all set, then you just write the path in the extension
configuration in the extension manager for phpunit.

