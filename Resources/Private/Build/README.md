## How to update the phpunit library phar file

* Adapt the `composer.json` file and do a `composer update` in this directory.
* Then execute the following command to update the phar:
```
./phar-composer.phar build . ../Libraries/phpunit-library.phar
```
* Commit `Resources/Private/Libraries/phpunit-library.phar`

