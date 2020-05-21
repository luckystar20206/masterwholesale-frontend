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
use Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Coupon
{
    /**
     * @var RequestFactory
     */
    private $requestCollection;

    /**
     * @var CollectionFactory
     */
    private $couponCollection;

    /**
     * @var DateTime
     */
    private $date;

    /**
     * Coupon constructor.
     * @param RequestCollectionFactory $requestCollection
     * @param CollectionFactory $couponCollection
     * @param DateTime $date
     */
    public function __construct(
        RequestCollectionFactory $requestCollection,
        CollectionFactory $couponCollection,
        DateTime $date
    ) {
        $this->requestCollection = $requestCollection;
        $this->couponCollection = $couponCollection;
        $this->date = $date;
    }

    public function execute()
    {
        $requestCollection = $this->requestCollection->create()
            ->addFieldToFilter('main_table.status', ['eq' => Status::COMPLETED_REQUEST_STATUS]);
        $couponCodes = [];
        foreach ($requestCollection as $item) {
            $couponCodes[] = $item->getCoupon();
        }

        $currentDate = $this->date->gmtDate('Y-m-d H:i:s');
        $couponCollection = $this->couponCollection->create()
            ->addFieldToFilter('code', ['in' => $couponCodes])
            ->addFieldToFilter('times_used', ['eq' => 0])
            ->addFieldToFilter('expiration_date', ['lt' => $currentDate]);
        foreach ($couponCollection as $coupon) {
            foreach ($requestCollection as $request) {
                if ($request->getCoupon() == $coupon->getCode()) {
                    $request->setStatus(Status::EXPIRED_REQUEST_STATUS);
                    $request->save(); // @codingStandardsIgnoreLine
                }
            }
        }

        return $this;
    }
}
