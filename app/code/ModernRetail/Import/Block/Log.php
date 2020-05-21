<?php
namespace ModernRetail\Import\Block;
class Log extends \Magento\Framework\View\Element\Template
{
    protected $log;
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \ModernRetail\Import\Model\Log $log,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\Framework\App\RequestInterface $request,
        \ModernRetail\Import\Helper\Summary $summary

    )
    {
        $this->dir = $dir;
        $this->log = $log;
        $this->request = $request;
        $this->summary = $summary;
        parent::__construct($context);
    }

    public function getPath(){
        return "/pub/mr_import/data/";
    }

    public function getLogCollection()
    {
        $now = new \DateTime();
        $from = (new \DateTime())->modify('-24 hours');
        $collection = $this->log->getCollection();
        $collection
            ->getSelect()
            ->order(array('date DESC'))
            ->limit(20);
//        $collection->addFieldToFilter('date', array('from' => $from, 'to' => $now));
        $collection->addFieldToFilter('status', array('in' => ['error', 'failed', 'processing']));

        return $collection;
    }




    public function _prepareLayout()
    {
        //$this->pageConfig->getTitle()->set(__('Modern Retail Integration Log Table'));
        return parent::_prepareLayout();
    }

    public function getRequest()
    {
        $_params = $this->request->getParams();
        $params = $this->prepareParams($_params);
        if(count($params) < 1){
            return [];
        }

        return $params;
    }

    public function getIntegration($params)
    {

        $date = date('Y-m-d');
        if(array_key_exists('date',$params)){
            $date = $params['date'];
        }

        $to = date('Y-m-d', strtotime($date . ' +1 day'));
        $from = $date;

        $collection = $this->log->getCollection();

        $collection->addFieldToFilter('date', array("from" => $from, "to" => $to));

        $collection->addFieldToFilter('file_name', $params['file']);

        if($collection->count() > 0){
            return $collection->getFirstItem();
        }

        return false;
    }

    public function prepareParams($_params)
    {
        $params = [];

        if(!array_key_exists('file',$_params)){
            return $params;
        }

        foreach ($_params as $key => $value) {


            $val = trim($value);
            $str = strip_tags($val);

            $params[$key] = $str;

        }

        return $params;

    }




}