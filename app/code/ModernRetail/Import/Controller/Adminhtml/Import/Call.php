<?php
namespace  ModernRetail\Import\Controller\Adminhtml\Import;


use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Call extends \ModernRetail\Import\Controller\Adminhtml\Import {

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \ModernRetail\Import\Helper\Data $helper,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
 
    ) { 
        $this->_dir = $dir;
        $this->_resource = $resource;  
        $this->resource = $resource; 
        $this->_storeManager = $storeManager;
		
		 $this->magehelper = $helper;
        $this->context = $context;
        parent::__construct($context, $resultPageFactory, $resultLayoutFactory, $resultForwardFactory,$data);
    }

    public function execute(){

        $action = $this->getRequest()->getParam("action");
        $file = $this->getRequest()->getParam("file");
        $bucket = $this->getRequest()->getParam("bucket");

        $result = array(
            "error"=>0
        );
        try {
            $action = "_ajax_".$action."Action";
            $actionResult = $this->$action();
            $result = array_merge($result,$actionResult);
            $result['log'] = $this->_readLog($bucket, $file);
        }catch(Exception $e){
            $result = array(
                "error"=>1,
                "message"=>$e->getMessage()
            );
        }

        header('Content-Type: application/json');
        die(json_encode($result));
    }




    public function _ajax_reportAction(){
        $connection = $this->resource->getConnection('core_read');
        $sql  = "select entity_id,sku from ".$connection->getTableName("catalog_product_entity")." where type_id = 'simple'";
        $rows = $connection->fetchAll($sql);

        $csv = array("entity_id,sku");
        foreach($rows as $row){
            $csv[] ='"'.$row['entity_id'].'","'.$row['sku'].'"';
        }
        //@mkdir($this->magehelper->getPath())
        file_put_contents( $this->magehelper->getPath().DS."..".DS."reports".DS."report-".date("m-d-Y").".csv",join("\n",$csv));
        return array("file"=>"/mr_import".DS."reports".DS."report-".date("m-d-Y").".csv");
    }

    public function _ajax_runAction(){



        ini_set("max_execution_time",10);
        $file = $this->getRequest()->getParam("file");
        $bucket = $this->getRequest()->getParam("bucket");
        $reindex = $this->getRequest()->getParam("reindex");
        if (!$file || !$bucket) throw new Exception("File or bucket not found");

       
        if ($reindex=="true"){
            $reindex = "reindex=true";
        }else {
            $reindex = "reindex=false";
        }

  
            $this->context->getEventManager()->dispatch("integrator_run_file",array("bucket"=>$bucket,"file"=>$file,"debug"=>false,"reindex"=>$reindex));


        return array("result"=>"executed");
    }

    public function _ajax_checkAction(){
        $file = $this->getRequest()->getParam("file");
        $bucket = $this->getRequest()->getParam("bucket");
        if (!$file || !$bucket) throw new Exception("File or bucket not found");

        if (file_exists($this->magehelper->getPath().DS.$bucket.DS.$file.".done")){
            return array("finished"=>1);
        }
        return array("finished"=>0);
    }

    public function _readLog($bucket,$file){
        if (file_exists($this->magehelper->getPath().DS.$bucket.DS.$file.".log"))
        { 
            $content = file_get_contents($this->magehelper->getPath().DS.$bucket.DS.$file.".log");
            return nl2br($content);
        }

        return false;
    }


}