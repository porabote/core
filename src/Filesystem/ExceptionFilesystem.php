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


class ExceptionFilesystem extends \Exception
{

    public function __construct($message, $code = 0, Exception $previous = null) {
        parent::__construct('File handle error: '.$message);
    }

    // Переопределим строковое представление объекта.
    public function __toString() {
        return __CLASS__ . "00: [{$this->code}]: {$this->message}\n";
    }

    public function error() {

        echo json_encode([
            'error' => [
                'msg' => $this->message,
                'code' => $this->code,
            ],
        ]);
        exit;

    }

}