<?php

/**
 * This class represents some code that can be tested.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Phpunit_Testable
{
    /**
     * @var string
     */
    const ALL_EXTENSIONS = 'allExtensions';

    /**
     * @var string
     */
    protected $key = '';

    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var string
     */
    protected $codePath = '';

    /**
     * @var string
     */
    protected $testsPath = '';

    /**
     * files that should be excluded from code coverage
     *
     * @var string[]
     */
    protected $blacklist = [];

    /**
     * files that should be included in code coverage
     *
     * @var string[]
     */
    protected $whitelist = [];

    /**
     * @var string
     */
    protected $iconPath = '';

    /**
     * Returns the key.
     *
     * The key is intended to be used e.g., for drop-downs.
     *
     * For extensions, this will be the extension key. For out-of-line tests, this will be full path to the tested code.
     *
     * @return string the key, will not be empty
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Sets the key.
     *
     * The key is intended to be used e.g., for drop-downs.
     *
     * For extensions, this must be the extension key. For out-of-line tests, this must be full path to the tested code.
     *
     * @param string $key the key, must not be empty
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function setKey($key)
    {
        if ($key === '') {
            throw new \InvalidArgumentException('$key must not be empty.', 1334439650);
        }

        $this->key = $key;
    }

    /**
     * Returns the display title.
     *
     * @return string the title, might be empty
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the display title.
     *
     * @param string $title the title, may be empty
     *
     * @return void
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Returns the code path.
     *
     * This is the absolute path of the code that is tested.
     *
     * @return string the code path, will not be empty
     */
    public function getCodePath()
    {
        return $this->codePath;
    }

    /**
     * Sets the code path.
     *
     * This is the absolute path of the code that is tested.
     *
     * @param string $codePath the code path, must not be empty
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function setCodePath($codePath)
    {
        if ($codePath === '') {
            throw new \InvalidArgumentException('$codePath must not be empty.', 1334439668);
        }

        $this->codePath = $codePath;
    }

    /**
     * Returns the tests path.
     *
     * This is the absolute path of the unit tests. Usually, this path is
     * located within the code path.
     *
     * @return string the tests path, will not be empty
     */
    public function getTestsPath()
    {
        return $this->testsPath;
    }

    /**
     * Sets the tests path.
     *
     * This is the absolute path of the unit tests. Usually, this path is
     * located within the code path.
     *
     * @param string $testsPath the tests path, must not be empty
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function setTestsPath($testsPath)
    {
        if ($testsPath === '') {
            throw new \InvalidArgumentException('$testsPath must not be empty.', 1334439674);
        }

        $this->testsPath = $testsPath;
    }

    /**
     * Returns the blacklist, i.e., the absolute paths to the files that should
     * be excluded from the code coverage report.
     *
     * @return string[]
     *         the absolute paths to the blacklisted files, might be empty
     */
    public function getBlacklist()
    {
        return $this->blacklist;
    }

    /**
     * Sets the blacklist, i.e., the absolute paths to the files that should
     * be excluded from the code coverage report.
     *
     * @param string[] $files
     *        the absolute paths to the blacklisted files, may be empty
     *
     * @return void
     */
    public function setBlacklist(array $files)
    {
        $this->blacklist = $files;
    }

    /**
     * Returns the whitelist, i.e., the absolute paths to the files that should
     * be included in the code coverage report.
     *
     * @return string[]
     *         the absolute paths to the whitelisted files, might be empty
     */
    public function getWhitelist()
    {
        return $this->whitelist;
    }

    /**
     * Sets the whitelist, i.e., the absolute paths to the files that should
     * be included in the code coverage report.
     *
     * @param string[] $files
     *        the absolute paths to the whitelisted files, may be empty
     *
     * @return void
     */
    public function setWhitelist(array $files)
    {
        $this->whitelist = $files;
    }

    /**
     * Returns the relative path to the icon associated with this testable code.
     *
     * @return string the relative icon path, will not be empty
     */
    public function getIconPath()
    {
        return $this->iconPath;
    }

    /**
     * Sets the relative path to the icon associated with this testable code.
     *
     * @param string $iconPath the icon path, must not be empty
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function setIconPath($iconPath)
    {
        if ($iconPath === '') {
            throw new \InvalidArgumentException('$iconPath must not be empty.', 1334439681);
        }

        $this->iconPath = $iconPath;
    }
}
