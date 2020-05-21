<?php
namespace WeltPixel\GoogleCards\Observer;

use Magento\Framework\Event\ObserverInterface;

class AddUpdateHandlesObserver implements ObserverInterface
{      
    /**
    * @var \Magento\Framework\App\Config\ScopeConfigInterface
    */
    protected $scopeConfig;
    
    const XML_PATH_GOOGLECARDS_ENABLE_FACEBOOK_GRAPH = 'weltpixel_google_cards/facebook_opengraph/enable';
    const XML_PATH_GOOGLECARDS_ENABLE_GOOGLE_CARDS   = 'weltpixel_google_cards/general/enable';

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }
    
    /**
     * Add New Layout handle
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return self
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $layout = $observer->getData('layout');

        /** Apply only on pages where page is rendered */
        $currentHandles = $layout->getUpdate()->getHandles();
        if (!in_array('default', $currentHandles)) {
            return $this;
        }

        $enableFacebookGraph = $this->scopeConfig->getValue(self::XML_PATH_GOOGLECARDS_ENABLE_FACEBOOK_GRAPH,  \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $enableGoogleCards   = $this->scopeConfig->getValue(self::XML_PATH_GOOGLECARDS_ENABLE_GOOGLE_CARDS, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($enableFacebookGraph) {
            $layout->getUpdate()->addHandle('weltpixel_googlecards_remove_opengraph');
        }

        if ($enableGoogleCards) {
            $layout->getUpdate()->addHandle('weltpixel_googlecards_remove_schema');
        }

        return $this;
    }
}
