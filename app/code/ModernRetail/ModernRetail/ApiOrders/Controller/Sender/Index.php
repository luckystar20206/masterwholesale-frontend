<?php
namespace ModernRetail\ApiOrders\Controller\Sender;

class Index extends \Magento\Framework\App\Action\Action
{

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \ModernRetail\Base\Helper\Api $mrApiHelper,
        \Magento\Sales\Model\Order $order,
        \ModernRetail\Base\Helper\ApiLogger $logger,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \ModernRetail\ApiOrders\Helper\Data $mrApiOrderHelper,
        \ModernRetail\ApiOrders\Model\Queue $apiOrdersQueue,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    )
    {
        $this->apiLogger = $logger;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->mrApiHelper = $mrApiHelper;
        $this->order = $order;
        $this->_storeManager = $storeManager;
        $this->_mrApiOrderHelper = $mrApiOrderHelper;
        $this->queue = $apiOrdersQueue;
        $this->resultJsonFactory = $resultJsonFactory;
        return parent::__construct($context);
    }

    public function execute(){

        /*
         * Step #1. Send orders
         */
        $data = [];

        $result = $this->resultJsonFactory->create();
        try {
            $collection = $this->queue->getCollection();
            $collection->addFieldToFilter('status', array('scheduled'));
            $collection->addFieldToFilter('type', array('order'));
            $collection->getSelect()->limit(20);
            $collection->getSelect()->order('scheduled_at asc');

            foreach ($collection as $job) {
                $data[] = ['type' => $job->getData('type'),
                            'entity_id' => $job->getData('entity_id'),
                            'row_id' =>$job->getData('queue_row_id')];
                $job->send();
            }


        /**
         * Step #2. Send everything
         */

        $collection = $this->queue->getCollection();


        $collection->addFieldToFilter('status',array('scheduled'));
        $collection->addFieldToFilter('type',array('nin'=>['order']));
        $collection->getSelect()->limit(20);
        $collection->getSelect()->order('scheduled_at asc');
        foreach($collection as $job){
            $data[] = ['type' => $job->getData('type'),
                'entity_id' => $job->getData('entity_id'),
                'row_id' =>$job->getData('queue_row_id')];
             $job->send();
        }



       $collection = $this->queue->getCollection();
       foreach ($data as &$row){

            $id =intval($row['row_id']);
           $job = $collection->getItemById($id)->getData();
           $row['status'] = $job['status'];
        }


        if(!empty($data)){
            return $result->setData(['status'=>'OK',
                'data'=>$data]);
        }else{
            return $result->setData(['status'=>'EMPTY',
                'messsage'=>'It`s Nothing To Send.']);
        }

        }catch(\Exception $e){
            return $result->setData(['status'=>'FAILED',
                'messsage' => $e->getMessage()]);
        }

    }

}