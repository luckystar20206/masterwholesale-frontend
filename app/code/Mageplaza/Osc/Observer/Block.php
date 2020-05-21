<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Osc
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Osc\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Mageplaza\Osc\Helper\Data;

/**
 * Class Block
 * @package Mageplaza\Osc\Observer
 */
class Block implements ObserverInterface
{
    /**
     * @var bool
     */
    private $isSet = false;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * Block constructor.
     *
     * @param Data $helperData
     * @param RequestInterface $request
     */
    public function __construct(
        Data $helperData,
        RequestInterface $request
    ) {
        $this->helperData = $helperData;
        $this->request    = $request;
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        if ($this->request->getFullActionName() === 'onestepcheckout_index_index') {
            /** @var AbstractBlock $block */
            $block     = $observer->getEvent()->getBlock();
            $transport = $observer->getEvent()->getTransport();
            $oscRoute  = $this->helperData->getOscRoute();
            $html      = $transport->getHtml();
            $html      .= '<script> window.oscRoute = ' . Data::jsonEncode($oscRoute) . '</script>';
            if (!$this->isSet && $block->getLayout()->isBlock('require.js')) {
                $transport->setHtml($html);
                $this->isSet = true;
            }
        }
    }
}
