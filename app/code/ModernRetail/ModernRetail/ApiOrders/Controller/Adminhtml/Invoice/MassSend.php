<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace ModernRetail\ApiOrders\Controller\Adminhtml\Invoice;

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
        foreach ($collection->getItems() as $invoice) {
            try {
                $invoice = $objectManager->create('Magento\Sales\Model\Order\Invoice')->load($invoice->getId());
                $mrApiOrderHelper = $objectManager->create('\ModernRetail\ApiOrders\Helper\Data');
                $mrApiOrderHelper->sendInvoice($invoice);

                if (!$mrApiOrderHelper->isEnabled($invoice->getStoreId())) {
                    $this->messageManager->addError('This invoice came from store which disabled in Modern Retail API Invoices');
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $resultRedirect->setPath($this->getComponentRefererUrl());
                    return $resultRedirect;
                }

                $queue = $this->queue->add("invoice", $invoice->getId());
                $result = $queue->send();
            } catch (\Exception $ex) {
                $this->messageManager->addError($ex->getMessage());
            }
            $exported++;
        }

        $notExported = $collection->count() - $exported;

        if ($notExported) {
            $this->messageManager->addError(__('%1 invoice(s) cannot be sent.', $notExported));
        }

        if ($exported) {
            $this->messageManager->addSuccess(__('We sent %1 invoice(s) to Modern Retail API', $exported));
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->getComponentRefererUrl());
        return $resultRedirect;
    }
}
