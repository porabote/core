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

use finfo;
use SplFileInfo;

/**
 * Convenience class for reading, writing and appending to files.
 */
class File
{

    /**
     * Folder object of the file
     *
     * @var \Porabote\Filesystem\Folder
     * @link https://book.porabote.com/2.0/en/filesystem/folder.html
     */
    public $Folder;

    /**
     * File name
     *
     * @var string
     */
    public $name;

    /**
     * Keep the file handler resource if the file is opened
     *
     * @var resource|null
     */
    public $handle;

    /**
     * File info
     *
     * @var object
     */
    public $SplFileInfo = [];

    /**
     * Path params
     *
     * Absolute path
     *
     * @var string|null
     */

    public $entity;

    public $path;
    private $storagePath = '';
    public $pwd;
    public $unzipRelativePath = 'tmp/unzip';
    public $unzipAbsolutePath;

    /**
     * Constructor
     *
     * @param string $path Absolute path to file
     * @param bool $create Create new file if it does not exist (if true)
     * @param int $mode Mode file
     */
    public function __construct($path, $create = false, $mode = 755)
    {

        $splFileInfo = new SplFileInfo($path);

        $this->SplFileInfo = new SplFileInfo($path);

        $this->Folder = new Folder($splFileInfo->getPath(), $create, $mode);
        if (!is_dir($path)) {
            $this->name = ltrim($splFileInfo->getFilename(), '/\\');
        }
        $create && $this->create();
    }

    function create()
    {
        $path = $this->path = $this->SplFileInfo->getPath() . '/' . $this->name;

        if(!file_exists($path)) {
            $this->handle = fopen($path, "a");
            fclose($this->handle);

            return true;
        }

        return true;
    }

    function write($msg)
    {
        $this->handle = fopen($this->path, "a");
        fwrite($this->handle, $msg . PHP_EOL);
        fclose($this->handle);
    }

    /**
     * Closes the current file if it is opened
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Closes the current file if it is opened.
     *
     * @return bool True if closing was successful or file was already closed, otherwise false
     */
    public function close()
    {
        if (!is_resource($this->handle)) {
            return true;
        }

        return fclose($this->handle);
    }

    function setStoragePath($path)
    {
        $this->storagePath = $path;
    }

    function newEntity($data = [])
    {
        $this->entity = [
            'basename' => $this->SplFileInfo->getBasename(),
            'ext' => $this->SplFileInfo->getExtension(),
            'uri' => str_replace($this->storagePath, '', $this->SplFileInfo->getRealPath()),
            'path' => $this->SplFileInfo->getRealPath(),
            'user_id' => null,
            'mime' => mime_content_type($this->SplFileInfo->getRealPath()),
            'token' => $this->createGuid(),
            'width' => '',
            'height' => '',
            'flag' => 'on',
            'title' => '',
            'dscr' => '',
            'label' => '',
            'main' => 1,
            'model_alias' => '',
            'record_id' => '',
            'data_s_path' => ''
        ];

        if(explode('/', $this->entity['mime'])[0] = 'image') {
            list($this->entity['width'], $this->entity['height']) = getimagesize($this->entity['path']);
        }

        return $this->entity = array_merge($this->entity, $data);

    }

    // Create GUID (Globally Unique Identifier)
    function createGuid() {
        $guid = '';
        $namespace = rand(11111, 99999);
        $uid = uniqid('', true);
        $data = $namespace;
        $data .= $_SERVER['REQUEST_TIME'];
        $data .= $_SERVER['HTTP_USER_AGENT'];
        $data .= $_SERVER['REMOTE_ADDR'];
        $data .= $_SERVER['REMOTE_PORT'];
        $hash = strtoupper(hash('ripemd128', $uid . $guid . md5($data)));
        $guid = substr($hash,  0,  8) . '-' .
            substr($hash,  8,  4) . '-' .
            substr($hash, 12,  4) . '-' .
            substr($hash, 16,  4) . '-' .
            substr($hash, 20, 12);
        return $guid;
    }


    /**
     *
     * - /xl/workbook.xml лежит описание листов
     * - /xl/sharedStrings.xml Все строки внутри XLSX
     * - /xl/worksheets/ Это директория с файлами (листами) типа «sheet1.xml
     */
    public function unzipFile()
    {
        $this->unzipRelativePath .=  time() . rand();

        $unpackPath = $this->SplFileInfo->getPath() . '/' . $this->unzipRelativePath;
        $this->Folder->create($unpackPath);

        $zip = new \ZipArchive;
        $res = $zip->open($this->SplFileInfo->getFileInfo());
        if ($res === TRUE) {
            $zip->extractTo($unpackPath);
            $zip->close();
            return $unpackPath;
        } else {
            echo 'unpack error';
        }
    }

    function getUnzipAbsolutePath()
    {
        return $this->SplFileInfo->getPath() . '/' . $this->unzipRelativePath;
    }

}