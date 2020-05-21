<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace ModernRetail\ApiOrders\Controller\Adminhtml\Shipment;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class MassSend extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(Context $context, Filter $filter, CollectionFactory $collectionFactory, \ModernRetail\ApiOrders\Model\Queue $apiOrdersQueue, \ModernRetail\ApiOrders\Helper\Data $mrApiOrdersHelper)
    {
        parent::__construct($context, $filter);
        $this->queue = $apiOrdersQueue;
        $this->collectionFactory = $collectionFactory;
        $this->mrApiOrdersHelper = $mrApiOrdersHelper;
    }

    /**
     * Cancel selected orders
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
        $exported = 0;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        foreach ($collection->getItems() as $shipment) {
            try {
                $shipment = $objectManager->create('Magento\Sales\Model\Order\Shipment')->load($shipment->getId());
                $mrApiOrderHelper = $objectManager->create('\ModernRetail\ApiOrders\Helper\Data');
                $mrApiOrderHelper->sendShipment($shipment);

                if (!$mrApiOrderHelper->isEnabled($shipment->getStoreId())) {
                    $this->messageManager->addError('This shipment came from store which disabled in Modern Retail API Shipments');
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $resultRedirect->setPath($this->getComponentRefererUrl());
                    return $resultRedirect;
                }

                $queue = $this->queue->add("shipment", $shipment->getId());
                $result = $queue->send();
            } catch (\Exception $ex) {
                $this->messageManager->addError($ex->getMessage());
            }
            $exported++;
        }

        $notExported = $collection->count() - $exported;

        if ($notExported) {
            $this->messageManager->addError(__('%1 shipment(s) cannot be sent.', $notExported));
        }

        if ($exported) {
            $this->messageManager->addSuccess(__('We sent %1 shipment(s) to Modern Retail API', $exported));
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->getComponentRefererUrl());
        return $resultRedirect;
    }
}
