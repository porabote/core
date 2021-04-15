<?php
/**
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Porabote Software development, Inc.
 * @link          https://porabote.com
 * @since         04.2020
 * @version       2.1
 */
namespace Porabote\Filesystem;

use Exception;
use DirectoryIterator;
use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Folder's for work with CRUD folders.
 */
class Folder
{

    static private $__messages;

    /**
     * Absolute path of Folder.
     *
     * @var string
     */
    public $path;

    /**
     * Mode of folder.
     *
     * @var int
     */
    public $mode = 0755;

    /**
     * Logs from last method.
     *
     * @var array
     */
    protected $_logs = [];

    /**
     * Errors from last method.
     *
     * @var array
     */
    protected $_errors = [];

    /**
     * List of directory paths.
     *
     * @var array
     */
    protected $_directories;

    /**
     * List of complete file paths.
     *
     * @var array
     */
    protected $_files;

    /**
     * Constructor.
     *
     * @param string|null $path Path to folder
     * @param bool $create Create folder if not found
     * @param int|false $mode Mode (CHMOD) to apply to created folder, false to ignore
     */
    public function __construct($path = null, $createNew = false, $mode = false)
    {
        try {

            $this->path = $path;

            if (empty($path)) {
                throw new ExceptionFilesystem('Empty path.');
            }
            if ($mode) {
                $this->mode = $mode;
            }

            if (!file_exists($path) && $createNew === true) {
                $this->__create($path, $this->mode);
            }

        } catch (ExceptionFilesystem $e) {
            return $e->error();
        }
    }

    /**
     * Create a directory recursively.
     *
     * @param string $pathname The directory structure to create
     * @param int|bool $mode octal value 0755
     * @return bool Returns TRUE/FALSE
     */
    public function __create($pathname, $mode = false)
    {
        if (is_dir($pathname) || empty($pathname)) {
            return true;
        }

        if (!$mode) {
            $mode = $this->mode;
        }

        if (is_file($pathname)) {
            $this->_errors[] = sprintf('%s is a file', $pathname);

            return false;
        }
        $pathname = rtrim($pathname, DIRECTORY_SEPARATOR);
        $nextPathname = substr($pathname, 0, strrpos($pathname, DIRECTORY_SEPARATOR));

        if ($this->create($nextPathname, $mode)) {
            if (!file_exists($pathname)) {
                $old = umask(0);
                if (mkdir($pathname, $mode, true)) {
                    umask($old);
                    $this->_messages[] = sprintf('%s created', $pathname);

                    return true;
                }
                umask($old);
                $this->_errors[] = sprintf('%s NOT created', $pathname);

                return false;
            }
        }

        return false;
    }

    static public function create($pathname, $mode = 0755)
    {
        if (is_dir($pathname) || empty($pathname)) {
            return true;
        }

        if (is_file($pathname)) {
            $this->_errors[] = sprintf('%s is a file', $pathname);

            return false;
        }
        $pathname = rtrim($pathname, DIRECTORY_SEPARATOR);
        $nextPathname = substr($pathname, 0, strrpos($pathname, DIRECTORY_SEPARATOR));

        if (self::create($nextPathname, $mode)) {
            if (!file_exists($pathname)) {
                $old = umask(0);
                if (mkdir($pathname, $mode, true)) {
                    umask($old);
                    self::$__messages[] = sprintf('%s created', $pathname);

                    return true;
                }
                umask($old);
                $this->_errors[] = sprintf('%s NOT created', $pathname);

                return false;
            }
        }

        return false;
    }

    /**
     * get error from latest method
     *
     * @param bool $reset Reset error stack after reading
     * @return array
     */
    public function getErrors()
    {
        return $this->_errors;
    }

}
