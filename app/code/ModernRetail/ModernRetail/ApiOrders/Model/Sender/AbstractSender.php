<?php
namespace ModernRetail\ApiOrders\Model\Sender;

abstract class  AbstractSender  {

    public $helper;

    protected $_apiPath = "client/order";
    public $entityName = null;

    public function __construct(

        \Magento\Framework\Model\AbstractModel $entityModel,
        \Magento\Catalog\Model\ProductFactory $productloader,
        \ModernRetail\Base\Helper\ApiLogger $logger,
        \ModernRetail\Base\Helper\Api $apiHelper,
        \Magento\Framework\Event\Manager $_eventManager
    )
    {
        $this->entityModel = $entityModel;
        $this->productloader = $productloader;
        $this->logger = $logger;
        $this->apiHelper = $apiHelper;
        $this->_eventManager = $_eventManager;
    }




    public function convertAddress($address){
        if (!$address) return [];
        return  [
            'first_name' => $address->getFirstname(),
            'last_name' => $address->getLastname(),
            'email' => $address->getEmail(),
            'country' => $address->getCountryId(),
            'state' => $address->getRegion(),
            'city' => $address->getCity(),
            'postcode' => $address->getPostcode(),
            'street' => $address->getStreet(),
            'telephone' => $address->getTelephone(),
            'company' => $address->getCompany(),
        ];
    }

    public function getEntityName(){
        if ($this->entityName) return $this->entityName;
        $parts = explode("\\",get_class($this));
        $this->entityName = array_pop($parts);
        return $this->entityName;
    }

    public function getObject($object){
        if (is_object($object)===false ){
            $object = $this->entityModel->load($object);
        }
        return $object;
    }

    public function buildRequest($object){
        return [];
    }

    public function send($object){



        $entityName = $this->getEntityName();
        $object = $this->getObject($object);


        if (!$object->getId()) return [
            'error'=>true,
            'request'=>$this->getEntityName().' not found',
            'response'=>$this->getEntityName().' not found',
        ];


        $order_id = $object->getId();
        if ($object->getOrderId()){
            $order_id = $object->getOrderId();
        }


        $apiPath = $this->_apiPath;
        $apiPath = str_replace(":id",$object->getOrderId(),$apiPath);

        $data = $this->buildRequest($object);


        try {
            $this->logger->info("Trying to send $entityName to MRAPI", ['id' => $object->getId(), '#' => $object->getIncrementId()]);
            $this->logger->info("Trying to send $entityName to MRAPI", $data);
            $result = $this->apiHelper->apiPOST($apiPath,$data,$object->getStoreId());
            $this->logger->info($result['message']);
            /*
            if ($order->getSentToMrApi() != 1 && $markAsSent === true) {
                $table = $this->connection->getTableName('sales_order');
                $this->connection->query("update $table set sent_to_mr_api = 1 where entity_id = {$order->getId()}");

                $table = $this->connection->getTableName('sales_order_grid');
                $this->connection->query("update $table set sent_to_mr_api = 1 where entity_id = {$order->getId()}");
            }*/
            return['error'=>false,'request'=>$data,'response'=>$result,'order_id'=>$order_id];
        } catch (\Exception $ex) {
            $this->logger->error($ex->getMessage());
            return['error'=>true,'request'=>$data,'response'=>$ex->getMessage(),'order_id'=>$order_id];
        }


    }

}