<?php
namespace ModernRetail\Import\Controller\Remote;

class Check extends \ModernRetail\Import\Controller\RemoteAbstract
{

    public function execute()
    {

        $bucket = date("m-d-Y");
        $file = $this->_request->getParam('file');

        if (file_exists($this->helper->getPath().DS.$bucket.DS.$file.".done")){
            die("FINISHED");
        }

        if (file_exists($this->helper->getPath().DS.$bucket.DS.$file.".log"))
        {
            $content = file_get_contents($this->helper->getPath().DS.$bucket.DS.$file.".log");
            die($content);
        }
    }
}