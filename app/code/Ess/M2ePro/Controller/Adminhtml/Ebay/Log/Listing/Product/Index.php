<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Controller\Adminhtml\Ebay\Log\Listing\Product;

use Ess\M2ePro\Controller\Adminhtml\Context;

/**
 * Class Index
 * @package Ess\M2ePro\Controller\Adminhtml\Ebay\Log\Listing\Product
 */
class Index extends \Ess\M2ePro\Controller\Adminhtml\Ebay\Log\Listing
{
    //########################################

    protected $filterManager;

    public function __construct(
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Ebay\Factory $ebayFactory,
        Context $context
    ) {
        $this->filterManager = $filterManager;

        parent::__construct($ebayFactory, $context);
    }

    public function execute()
    {
        $listingId = $this->getRequest()->getParam(
            \Ess\M2ePro\Block\Adminhtml\Log\Listing\Product\AbstractGrid::LISTING_ID_FIELD,
            false
        );
        $listingProductId = $this->getRequest()->getParam(
            \Ess\M2ePro\Block\Adminhtml\Log\Listing\Product\AbstractGrid::LISTING_PRODUCT_ID_FIELD,
            false
        );

        if ($listingId) {
            $listing = $this->ebayFactory->getCachedObjectLoaded('Listing', $listingId, null, false);

            if ($listing === null) {
                $this->getMessageManager()->addErrorMessage($this->__('Listing does not exist.'));
                return $this->_redirect('*/*/index');
            }

            $this->getResult()->getConfig()->getTitle()->prepend(
                $this->__('M2E Pro Listing "%s%" Log', $listing->getTitle())
            );
        } elseif ($listingProductId) {
            $listingProduct = $this->ebayFactory->getObjectLoaded('Listing\Product', $listingProductId, null, false);

            if ($listingProduct === null) {
                $this->getMessageManager()->addErrorMessage($this->__('Listing Product does not exist.'));
                return $this->_redirect('*/*/index');
            }

            $this->getResult()->getConfig()->getTitle()->prepend($this->__(
                'M2E Pro Listing Product "%1%" Log',
                $this->filterManager->truncate($listingProduct->getMagentoProduct()->getName(), ['length' => 28])
            ));
        } else {
            $this->getResult()->getConfig()->getTitle()->prepend($this->__('Listings Logs & Events'));
        }

        $this->addContent($this->createBlock('Ebay_Log_Listing_Product_View'));

        return $this->getResult();
    }

    //########################################
}
