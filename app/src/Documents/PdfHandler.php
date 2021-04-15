<?php
namespace PoraboteApp\Documents;

class PdfHandler
{

    private $handler;

    function __construct()
    {
	    $this->handler = new \Mpdf\Mpdf;
    }

    function htmlToPdf($html, $options = [])
    {
	    
	    $options_default = [
		    'pageSize' => [ 'width' => 210, 'height' => $this->handler->y]
	    ];
	    $options = array_merge($options_default, $options);
	    
	    $this->handler->WriteHTML($html);

        $this->handler->page   = 0;
        $this->handler->state  = 0;
        unset($this->handler->pages[0]);

        // The $p needs to be passed by reference
        $p = 'P';
       // debug($this->handler->y);

        $this->handler->_setPageSize([$options['pageSize']['width'], $this->handler->y], $p);

            $this->handler->addPage();
            $this->handler->WriteHTML($html);
        

	    $this->handler->Output($options['path']);//, \Mpdf\Output\Destination::INLINE
	   
    }

    function pdfToImg($options = [])
    {
	 
	    //if($this->handler->y == 0) $this->_outputJSON(['error' => 'Данные пусты']);
	    
	    $options_default = [
		    'pageSize' => [ 'width' => 1000, 'height' => $this->handler->y]
	    ];
	    $options = array_merge($options_default, $options);

	    $imgsList = $this->extractPagesAsImages($options);
	    $filesObj = new \App\Controller\FilesController();
	    
	    $imgsInfoList = [];
	    foreach($imgsList as $imgPath) { 
	        $imgsInfoList[] = $filesObj->addMetaData($imgPath, [
		        'title' => '',
		        'dscr' => '',
		        'label' => (isset($options['label'])) ? $options['label'] : 'imgFromPdf',
		        'main' => (isset($options['main'])) ? $options['main'] : 'none',
		        'model_alias' => (isset($options['model_alias'])) ? $options['model_alias'] : 'Files',
		        'record_id' => $options['record_id'],
		        'parent_id' => (isset($options['parent_id'])) ? $options['parent_id'] : null
	        ]);   
	    }
	    return $imgsInfoList;   
	    	    
    }

    function extractPagesAsImages($options)
    {
	    $options_default = [
		    'pageSize' => [ 'width' => 1200, 'height' => 0]
	    ];
	    $options = array_merge($options_default, $options);

	    list($dirname, $basename, $extension, $filename ) = array_values(pathinfo($options['path']));

        $date = new \DateTime();    

        $imagick = new \Imagick($options['path']);
        $pages_count = $imagick->getNumberImages();
        
        $ext = (isset($options['ext'])) ? $options['ext'] : 'jpg';

        // создаем изображения из страниц PDF файла
        $imgsList = [];
        for ($pageNumber = 0; $pageNumber < $pages_count; $pageNumber++) {

            $newFilePath = $dirname . '/page_' . $pageNumber . '__' . $date->getTimestamp() . '.' . $ext . '';
            
            // создаем изображения из страниц PDF файла
            exec('convert \
                -density 400 \
                -colorspace CMYK \
                ' .$options['path']. '[' .$pageNumber. '] \
                -scale '.$options['pageSize']['width'].'x'.$options['pageSize']['height'].' \
                -quality 75  \
                -resize 100% '. $newFilePath  .'', $out, $error);

            $imgsList[$pageNumber] = $newFilePath;

        }
        
        return $imgsList;	    
    }

    function clonePdf($options = [])
    {
	    list($dirname, $basename, $extension, $filename ) = array_values(pathinfo($options['path']));
	    
	    $pagecount = $this->handler->SetSourceFile($options['path']);
	    
	    
	    $this->handler->SetImportUse();
	    

        for ($i=1; $i<=$pagecount; $i++) {
            $import_page = $this->handler->ImportPage();
            $this->handler->UseTemplate($import_page);
	    
            if ($i < $pagecount)
                $this->handler->AddPage();
        }
    
        //return $this->handler;
        $this->handler->Output('scan_img_'.bin2hex(random_bytes(5)).'_.pdf',\Mpdf\Output\Destination::INLINE);	    
    }

	
}
	
?>