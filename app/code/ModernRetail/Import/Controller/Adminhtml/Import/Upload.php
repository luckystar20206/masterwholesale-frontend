<?php
namespace  ModernRetail\Import\Controller\Adminhtml\Import;


use Magento\Backend\App\Action\Context;
use Magento\Framework\File\Uploader;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;

class Upload extends \ModernRetail\Import\Controller\Adminhtml\Import {


    public $uploader = null;

    public function __construct(
            \Magento\Backend\App\Action\Context $context,
            \Magento\Framework\View\Result\PageFactory $resultPageFactory,
            \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
            \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
            \ModernRetail\Import\Helper\Data $helper,
            array $data = []

            )
    {

        //$this->uploader = $uploader; 
        $this->magehelper = $helper; 
        
        parent::__construct($context, $resultPageFactory, $resultLayoutFactory, $resultForwardFactory,$data);
    }

    public function execute(){

        if ($data = $this->getRequest()->getPost()) {

            if(isset($_FILES['mr_import_file']['name']) && $_FILES['mr_import_file']['name'] != '') {
                try {
                    /* Starting upload */
                    $uploader = new Uploader('mr_import_file');

                    //$uploader = new Varien_File_Uploader('mr_import_file');

                    // Any extention would work
                    $uploader->setAllowedExtensions(array('xml'));
                    $uploader->setAllowRenameFiles(false);

                    // Set the file upload mode
                    // false -> get the file directly in the specified folder
                    // true -> get the file in the product like folders
                    //    (file.jpg will go in something like /media/f/i/file.jpg)
                    $uploader->setFilesDispersion(false);
 
                    // We set media as the upload dir
                    @mkdir($this->magehelper->getPath().DS.date("m-d-Y"));
                    $path = $this->magehelper->getPath().DS.date("m-d-Y");
                    $uploader->save($path, str_replace(" ","_",$_FILES['mr_import_file']['name'] ));

                    $this->messageManager->addSuccess('File was successfully uploaded');


                } catch (Exception $e) {
                    $this->messageManager->addError($e->getMessage());

                }


            }
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}