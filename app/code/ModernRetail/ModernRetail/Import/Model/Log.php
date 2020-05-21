<?php
namespace ModernRetail\Import\Model;

class Log  extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'mr_import_log';

    protected $_cacheTag = 'mr_import_log';

    protected $_eventPrefix = 'mr_import_log';

    protected function _construct()
    {
        $this->_init('ModernRetail\Import\Model\ResourceModel\Log');
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

    public function log($data){

        $fileId = $data['file_id'];
        $fileName = $data['file_name'];
        $status = $data['status'];
        $message = $data['message'];


        try {
            $findRow = $this->getCollection()->addFieldToFilter('file_id',$fileId);
            if ($findRow->count()>0){
                $findRow = $findRow->getFirstItem();
                if($findRow->getData('status') == 'error' || $findRow->getData('status') == 'failed' && $status == 'complete'){
                    return $this;
                }

                //$data['queue_row_id'] = $findRow->getData('queue_row_id');
                $findRow->setData('message',$message);
                $findRow->setData('status',$status);
                $findRow->save();
                return $findRow;
            }else {

                $this->setData($data);
                $this->save();
                return $this;
            }
        }catch (\Exception $ex){

        }
    }

}