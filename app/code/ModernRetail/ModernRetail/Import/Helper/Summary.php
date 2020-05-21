<?php
namespace ModernRetail\Import\Helper;

use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\App\Helper\AbstractHelper;

class Summary  extends AbstractHelper{


    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->resource = $resource;
        $this->dir = $dir;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }


    public function getMagentoVersion(){
        return $this->magentoVersion->getVersion();
    }

    public function getData($days = 1)
    {
        $days = max(1,$days);
        $mr_import_log = $this->resource->getTableName('mr_import_log');
        $sql = "select status,type, count(status) as `count` from $mr_import_log where `date` >= now() - INTERVAL $days DAY group by status,type";

        $readConnection = $this->resource->getConnection('core_read');
        $result = $readConnection->query($sql)->fetchAll();

        $summary = ['total' => 0];
        foreach ($result as $data) {
            $summary['total'] += $data['count'];
            $summary[$data['type']][] = $data;
        }
        /**
         * retrieve History
         */
        $sql = "select * from $mr_import_log where `date` >= now() - INTERVAL $days DAY order by date desc";
        $history = $readConnection->query($sql)->fetchAll();
        $url = $this->storeManager->getStore()->getBaseUrl();
        return ['url'=>$url,'jobs'=>$summary,'history'=>$history];
    }


}