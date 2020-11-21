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
namespace Porabote\Components\ExcelParser;

use finfo;
use SplFileInfo;
use Cake\Http\Session;

/**
 * Convenience class for reading, writing and appending to files.
 */
class ExcelParser
{

    public $workbook;
    public $sheets = [
        'count' => 0
    ];
    public $strings;
    public $count_cell_x;

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
    public $pwd;

    /**
     * Constructor
     *
     * @param string $path Absolute path to file
     */
    public function __construct($file)
    {
        $this->setFile($file);
    }

    function setWorkbook()
    {
        $this->File->unzipFile();
        $this->workbook = \simplexml_load_file($this->File->getUnzipAbsolutePath() . '/xl/workbook.xml');

        $this->setSheets();
    }

    function setSheets()
    {
        foreach($this->workbook->sheets->children() as $sheet) {
            $this->sheets['count']++;
            $this->sheets['sheets'][(string) $sheet->attributes()->sheetId] = (string)$sheet->attributes()->name;
        }
    }

    // Парсим отдельным лист экселя
    function parseSheet($sheetNumber, $limit = 100, $remove = false)
    {
        // Получаем все текстовые данные
        $this->setStrings();

        $sheetFilePath = $this->File->getUnzipAbsolutePath() . '/xl/worksheets/sheet' . $sheetNumber . '.xml';
        $xml = simplexml_load_file($sheetFilePath);

        //по каждой строке
        $rowNumber = 0;
        $out = [
            'count_cell_y' => count($xml->sheetData->row),
            'cells' => [],
            'handledCount' => (count($xml->sheetData->row) <= $limit) ? count($xml->sheetData->row) : $limit
        ];

        //Handle rows
        for($i = 0; $i <= $out['handledCount'];$i++) {

            if($limit && $rowNumber >= $limit) break;

            $out['cells'][$rowNumber] = [];

            //Handle cells
            $cell = 0;
            if($xml->sheetData->row[0]) {
                foreach ($xml->sheetData->row[$i] as $child) {
                    $attr = $child->attributes();
                    $value = isset($child->v) ? (string) $child->v : false;
                    $out['cells'][$rowNumber][$cell] = (isset($attr['t']) && isset($this->strings[$value]) ) ? $this->strings[$value] : $value;
                    $cell++;

                    if($cell > $this->count_cell_x) $this->count_cell_x = $cell;

                }
            }
            $rowNumber++;

            if($remove && isset($xml->sheetData->row[0])) {
                $node = dom_import_simplexml($xml->sheetData->row[0]);
                $node->parentNode->removeChild($node);
            }

        }

        if($remove) {
            file_put_contents($file_path, $xml->saveXML());
        }

        $out['count_cell_x'] = $this->count_cell_x;

        return $out;
    }


    /*
     * Get All Lists
     *
     * */
    function getSheets($sheetNumber = null)
    {
//        if(isset($_GET['sheet'])) $sheetNumber = $_GET['sheet'];
//
//        $out = [];
//        // Получаем все числовые значения и сливаем их с тектовыми
//
//        $out = $this->parseSheet($file_folder, 'sheet' .$sheetNumber. '.xml');
//
//        $out_sheets['sheet'] = $out;
//        $out_sheets['sheets_list'] = $sheets;
//        $out_sheets['count_cell_x'] = $this->count_cell_x;
//        return $out_sheets;
    }

    /*
     * Set string variables
     *
     * */
    function setStrings()
    {
        $xml = \simplexml_load_file($this->File->getUnzipAbsolutePath() . '/xl/sharedStrings.xml');

        foreach ($xml->children() as $item) {
            $this->strings[] = (string)$item->t;
        }
    }



    //SHARED

    private function setFile($file)
    {
        if(is_string($file)) {
            $this->File = new \Porabote\Filesystem\File($file);
        } else if (is_object($file)) {
            $this->File = $file;
        }

    }




}