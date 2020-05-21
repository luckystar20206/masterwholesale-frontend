<?php
namespace ModernRetail\Import\Controller\Remote;

class Execute extends  \ModernRetail\Import\Controller\RemoteAbstract
{

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \ModernRetail\Import\Model\Xml $import,
        \ModernRetail\Import\Helper\Data $helper,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager

    ) {
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context, $import, $helper, $resource,$storeManager);
    }

    public function execute()
    {

            $action = $this->_request->getParam('action');

            if (isset($_FILES['mr_import_file']['name']) && $_FILES['mr_import_file']['name'] != '') {
                $resultForward = $this->resultForwardFactory->create();

                return $resultForward->forward('import');
            }

            if ($action) {
             
                /** @var \Magento\Backend\Model\View\Result\Forward $resultForward */
                $resultForward = $this->resultForwardFactory->create();
                return $resultForward->forward($action);
            }

    }
}