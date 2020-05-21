<?php


namespace ModernRetail\Import\Controller\Remote;



class Run extends \Magento\Framework\App\Action\Action
{


    public function __construct(  \Magento\Framework\App\Action\Context $context, \ModernRetail\Import\Model\Xml $import, \ModernRetail\Import\Helper\Data $helper)
    {
        $this->import = $import;
        $this->helper = $helper;
        parent::__construct($context);
    }


    public function execute()
    {


        $needReindex = false;

        if (array_key_exists("bucket", $_GET)){
            $bucket = $_GET['bucket'];
        }

        if (array_key_exists("file", $_GET)){
            $file = $_GET['file'];
        }

        if (array_key_exists("reindex", $_GET)){
            if ($_GET['reindex']=="true"){
                $needReindex = true;
            }
        }


        if (file_exists($this->helper->getPath().DS.$bucket.DS.$file.".lock")){
          //  die('Already started');
        }

        /**
         * Send info that job started
         */


        try {
            $this->import->setPath($this->helper->getPath());
            $this->import->setBucket($bucket);
            $this->import->setXmlFile($file);


            @touch($this->helper->getPath().DS.$bucket.DS.$file.".lock");
            @unlink($this->helper->getPath().DS.$bucket.DS.$file.".done");

            $return = $this->import->proccess();

            if ($return===true){
                if($needReindex){
                    $this->import->reindex();
                }
                //rename($this->helper->getPath().DS.$bucket.DS.$file, $this->helper->getPath().DS.$bucket.DS."MRDONE_".$file);
                @unlink($this->helper->getPath().DS.$bucket.DS.$file.".lock");
                @touch($this->helper->getPath().DS.$bucket.DS.$file.".done");

            }else {
                /**
                 * send about job completion
                 */
                die("ERROR");
            }
        }catch(Exception $ex){
            /**
             * send about job completion
             */


        }

        die("ERROR");
    }
}