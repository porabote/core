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
namespace Porabote\Components\ImageMagick;

use \Porabote\Filesystem\Folder;

/**
 * Convenience class for reading, writing and appending to files.
 */
class ImageMagickHandler
{
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

    static public $tmpPath = TMP . 'ImageMagick';
    public $file;
    public $path;


    function setTmpPath($tmpPath)
    {
        $this->tmpPath = $tmpPath;
    }

    static function cloneToTmp($sourcePath, $targetPath = null)
    {
        if(!$targetPath) $targetPath = self::$tmpPath;

        Folder::create($targetPath);

        $targetPath = $targetPath . '/' . bin2hex(random_bytes(15)) .'.png';

        $action = 'convert ' . $sourcePath . ' ' . $targetPath;
        exec($action, $output, $result);
        return $targetPath;
    }

    static function composite($background, $overlay, $x = 0, $y = 0)
    {
        $action = 'convert -composite -gravity center ' . $background . ' ' . $overlay . ' ' . $background . '';

        exec($action, $output, $result);
        return $result;
    }


    /*
	 * Обрезка изображение по координатам (загрузка аватарок, факсимиле и прочего)
     */
    public function cropByCoordinates($data) {

        $patch = $data['save']['uri'];
        # определяем размеры исходного изображения
        list( $width, $height ) = getimagesize($patch);

        $width_new = $data['x2'] - $data['x1'];
        $height_new = $data['y2'] - $data['y1'];

        $img_thread = 'convert -crop '.$width_new.'x'.$height_new.'+'.$data['x1'].'+'.$data['y1'].'  '.$patch.' '.$patch.'';
//debug($img_thread);
        exec($img_thread, $output, $result);
        return $patch;
    }

    /*
	 * Очистака изображения от белого фона
     */
    public function setOpacity( $data, $color = 'white', $levelBlack = 95) {


        if(is_string($data)) {
            $path = $data;
        } else {
            $path = $data['save']['uri'];
            $path = str_replace('//', '/', $path);
        }

        $img_thread = 'convert '.$path.' -level 5%,'.$levelBlack.'% -transparent '.$color.' '.$path.'';
        exec($img_thread, $output, $result);
    }

    /*
	 * Установка фона
     */
    public function setBackground($path, $options = [])
    {

        list($width, $height) = getimagesize($path);

        $optionsDefault = [
            'color' => 'ffffff',
            'opacity' => '1'
        ];

        //создаем градиентную заливку
        $gradient_path = pathinfo($path)['dirname'].'/gradient.png';
        exec('convert \
            -size '.$width.'x'.$height.' \
            gradient:"rgba(255,255,255,0.85)"-"rgba(255,255,255,0.85)" \
            '.$gradient_path.'');

        //накладываем градиент на изображение
        exec('convert '.$gradient_path.' '.$path.'   -composite  '.$path.'');

        // Удаляем файл градиента
        unlink($gradient_path);

    }


}