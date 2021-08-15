<?php
namespace Porabote\Uploader;

use Porabote\Filesystem\Folder;
use Porabote\Filesystem\File;

class Uploader {

    private static $files = [];
    static private $storagePath;

    public static function upload($requestFiles)
    {
        Uploader::setStoragePath(storage_path());

        foreach ($requestFiles as $file) {

            $File = Uploader::moveTo(
                [
                    'tmp_name' => $file->getPathName(),
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'type' => $file->getMimeType(),
                    'error' => $file->getError(),
                ],
                '/upload'
            );

            //array_push(self::$files, $filePath);
        }

        return $File;
    }
    
    static function setStoragePath($storagePath)
    {
        self::$storagePath = $storagePath;
    }

    static public function moveTo($fileRequestData, $relativePath)
    {
        $fullPath = self::$storagePath . $relativePath;

        Folder::create($fullPath);

        $fileName = self::transliteFileName($fileRequestData['name']);

        if (move_uploaded_file($fileRequestData['tmp_name'], $fullPath . '/' . $fileName)) {
            $File = new \Porabote\Filesystem\File($fullPath . '/' . $fileName);
            $File->setStoragePath(self::$storagePath);
            return $File->newEntity();
        } else {
            return null;
        }
    }

    public static function transliteFileName($fileName)
    {
        $fileName = preg_replace('/[^a-zA-Zа-яА-Я0-9\_\-\.]/ui', '',$fileName );

        return mb_ereg_replace_callback(
            "(.+)(\.[a-zA-Z]+$)",
            function ($match) {
                return self::wordTranscript($match[1]). '_' . time() .$match[2];
            },
            $fileName);
    }

    public static function wordTranscript($word)
    {
        $trans = array("а"=>"a","б"=>"b","в"=>"v","г"=>"g","д"=>"d","е"=>"e",
            "ё"=>"yo","ж"=>"j","з"=>"z","и"=>"i","й"=>"i","к"=>"k",
            "л"=>"l", "м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
            "с"=>"s","т"=>"t", "у"=>"u","ф"=>"f","х"=>"h","ц"=>"c",
            "ч"=>"ch", "ш"=>"sh","щ"=>"sh","ы"=>"y","э"=>"e","ю"=>"yu",
            "я"=>"ya",
            "А"=>"a","Б"=>"b","В"=>"v","Г"=>"g","Д"=>"d","Е"=>"e",
            "Ё"=>"yo","Ж"=>"j","З"=>"z","И"=>"i","Й"=>"i","К"=>"k",
            "Л"=>"l","М"=>"m","Н"=>"n","О"=>"o","П"=>"p", "Р"=>"r",
            "С"=>"s","Т"=>"t","У"=>"u","Ф"=>"f", "Х"=>"h","Ц"=>"c",
            "Ч"=>"ch","Ш"=>"sh","Щ"=>"sh", "Ы"=>"y","Э"=>"e","Ю"=>"yu",
            "Я"=>"ya","ь"=>"","Ь"=>"","ъ"=>"","Ъ"=>"",
            " "=>"-","!"=>"","("=>"",")"=>"","+"=>"","\""=>"",","=>"","«"=>"","»"=>"","."=>"-"
        );

        $word = mb_strtolower(strtr($word, $trans));
        $word = mb_ereg_replace('(-)+', '-', $word, 'm');
        return trim($word, '-');
    }

}
?>