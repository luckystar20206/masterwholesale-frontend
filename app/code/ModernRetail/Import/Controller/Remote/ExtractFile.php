<?php

namespace ModernRetail\Import\Controller\Remote;

class ExtractFile extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \ModernRetail\Import\Model\Log $log,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    )
    {

        $this->resultJsonFactory = $resultJsonFactory;
        $this->log = $log;
        return parent::__construct($context);
    }

    public function execute()
    {

        $_params = [];

        if (array_key_exists('date', $_GET)) {
            if (array_key_exists('name', $_GET)) {
                $_params['name'] = $_GET['name'];

            }
            $_params['date'] = $_GET['date'];
        }
        if (count($_params) > 0) {
            $params = $this->prepareParams($_params);
        } else {
            return $this->resultJsonFactory->create()->setData(['status' => 'EMPTY', 'data' => 'Please, specify the date']);
        }

        try {
            $data = $this->collectData($params);
//            if (array_key_exists('items', $data) && count($data['items']) > 0) {
//                $data = $this->addLinks($data);
//            }

            if (count($data) > 0 && $data['totalRecords'] > 0) {
                $result = $this->resultJsonFactory->create()->setData(['status' => 'FOUND', 'data' => $data['items']]);
            } else {
                $result = $this->resultJsonFactory->create()->setData(['status' => 'EMPTY', 'data' => 'Nothing was found or wrong request.']);
            }
        } catch (\Exception $e) {
            return $this->resultJsonFactory->create()->setData(['status' => 'ERROR', 'data' => $e->getMessage()]);
        }
        return $result;
    }

    public function collectData($params)
    {

        $to = date('Y-m-d', strtotime($params['date'] . ' +1 day'));
        $from = $params['date'];

        $collection = $this->log->getCollection();

        $collection->addFieldToFilter('date', array("from" => $params['date'], "to" => $to));

        if (array_key_exists('name', $params)) {
            $collection->addFieldToFilter('file_name', $params['name']);
        }
        return $collection->toArray();
    }

    public function prepareParams($_params)
    {
        $params = [];


        foreach ($_params as $key => $value) {

            $val = trim($value);
            $str = strip_tags($val);

            $params[$key] = $str;

        }

        return $params;

    }

}