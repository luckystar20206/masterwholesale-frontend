<?php
namespace WeltPixel\GoogleCards\Plugin;
use Magento\Review\Block\Product\ReviewRenderer as SubjectBlock;
/**
 * Class ReviewRendererPlugin
 * @package WeltPixel\GoogleCards\Plugin
 */
class ReviewRendererPlugin
{
    const XML_PATH_GOOGLECARDS_ENABLE_GOOGLE_CARDS = 'weltpixel_google_cards/general/enable';
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Request\Http $request
    )
    {
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
    }
    /**
     * Added item to the review - this is a fix for Luma theme
     * @param SubjectBlock $subject
     * @param string $result
     * @return string
     */
    public function afterGetReviewsSummaryHtml(SubjectBlock $subject, $result = '')
    {
        $moduleName = $this->request->getModuleName();
        $enableGoogleCards = $this->scopeConfig->getValue(self::XML_PATH_GOOGLECARDS_ENABLE_GOOGLE_CARDS, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if($enableGoogleCards && $moduleName == 'cms') {
            if ($result != '' && !is_null($subject->getRequest()) && $product = $subject->getProduct()) {
                $result = '<div itemscope itemtype="https://schema.org/Product"><div itemprop="name" content="' . htmlspecialchars($product->getName()) . '"></div>' . $result . '</div>';
            }
        }
        return $result;
    }
}