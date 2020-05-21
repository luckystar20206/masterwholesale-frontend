<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


namespace Neklo\MakeAnOffer\Cron;

use Neklo\MakeAnOffer\Model\Source\Status;
use Neklo\MakeAnOffer\Model\ResourceModel\Request\CollectionFactory as RequestCollectionFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Neklo\MakeAnOffer\Helper\Config;

class Delete
{
    /**
     * @var RequestCollectionFactory
     */
    private $requestCollection;

    /**
     * @var DateTime
     */
    private $date;

    /**
     * @var Config
     */
    private $configHelper;

    /**
     * Delete constructor.
     * @param RequestCollectionFactory $requestCollection
     * @param DateTime $date
     * @param Config $configHepler
     */
    public function __construct(
        RequestCollectionFactory $requestCollection,
        DateTime $date,
        Config $configHepler
    ) {
        $this->requestCollection = $requestCollection;
        $this->date = $date;
        $this->configHelper = $configHepler;
    }

    public function execute()
    {
        $isCronEnabled = $this->configHelper->isCronEnabled();
        if (!$isCronEnabled) {
            return $this;
        }

        $daysToDelete = $this->configHelper->getDeleteAfter();

        $dateToDelete = $this->date->gmtDate('Y-m-d H:i:s', "-{$daysToDelete} days");

        $requestCollection = $this->requestCollection->create()
            ->addFieldToFilter('main_table.status', ['eq' => Status::DECLINED_REQUEST_STATUS])
            ->addFieldToFilter('main_table.created_at', ['lt' => $dateToDelete]);

        foreach ($requestCollection as $request) {
            $request->delete(); // @codingStandardsIgnoreFile
        }
        return $this;
    }
}
