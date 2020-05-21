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

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\UrlInterface;
use Mageplaza\Osc\Helper\Data as OscHelper;

/**
 * Class RedirectToOneStepCheckout
 * @package Mageplaza\Osc\Observer
 */
class RedirectToOneStepCheckout implements ObserverInterface
{
    /**
     * @var UrlInterface
     */
    protected $_url;

    /**
     * @var OscHelper
     */
    protected $_oscHelper;

    /**
     * RedirectToOneStepCheckout constructor.
     *
     * @param UrlInterface $url
     * @param OscHelper $oscHelper
     */
    public function __construct(
        UrlInterface $url,
        OscHelper $oscHelper
    ) {
        $this->_url = $url;
        $this->_oscHelper = $oscHelper;
    }

    /**
     * @param Observer $observer
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(Observer $observer)
    {
        if ($this->_oscHelper->isEnabled() && $this->_oscHelper->isRedirectToOneStepCheckout()) {
            $observer->getRequest()->setParam('return_url', $this->_url->getUrl($this->_oscHelper->getOscRoute()));
        }
    }
}
