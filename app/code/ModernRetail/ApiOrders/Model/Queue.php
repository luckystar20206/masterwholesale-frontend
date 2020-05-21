<?php
namespace ModernRetail\ApiOrders\Model;

class  Queue extends  \Magento\Framework\Model\AbstractModel {

    const CACHE_TAG = 'mr_api_queue';

    protected $_cacheTag = 'mr_api_queue';

    protected $_eventPrefix = 'mr_api_queue';


    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        $senders = []

    )
    {

        $this->senders = $senders;
        $this->date = $date;
        parent::__construct($context,$registry);
    }


    protected function _construct()
    {
        $this->_init('ModernRetail\ApiOrders\Model\ResourceModel\Queue');

    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getDefaultValues()
    {
        $values = [];

        return $values;
    }

    public function add($type,$entity_id){


        try {
        $findRow = $this->getCollection()->addFieldToFilter('type',$type)
                        ->addFieldToFilter('entity_id',$entity_id);
        if ($findRow->count()>0){
            $findRow = $findRow->getFirstItem();
            //$data['queue_row_id'] = $findRow->getData('queue_row_id');
            $findRow->setData('sent_at',null);
            $findRow->setData('status','scheduled');
            $findRow->save();
            return $findRow;
        }else {
            $data =  [
                'type'=>$type,
                'entity_id'=>$entity_id,
                'scheduled_at'=> $this->date->date()->format('Y-m-d H:i:s'),
                'sent_at'=>null,
                'status'=>'scheduled',
            ];



            $this->setData($data);
            $this->save();
            return $this;
        }
        }catch (\Exception $ex){

        }
    }
 
    /**
     * Sending to API
     */
    public function send(){

        $this->setData('status','pending');
        //$this->save();
        $sender = $this->senders[$this->getData('type')];

        $result = $sender->send($this->getData('entity_id'));

        $this->setData('request',json_encode($result['request'],true));
        $this->setData('response',json_encode($result['response'],true));

        if ($result['error']===true){
            $this->setData('status','failed');
        }else {
            $this->setData('status','complete');
        }


       $this->setData('tag','order/' . $result['order_id']);

        $this->setData('sent_at', $this->date->date()->format('Y-m-d H:i:s'));
        $this->save();
        return $result;
    }




}