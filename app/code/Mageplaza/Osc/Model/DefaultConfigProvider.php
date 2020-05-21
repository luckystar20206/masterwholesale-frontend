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

namespace Mageplaza\Osc\Model;

use Exception;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Cms\Block\Block;
use Magento\Customer\Model\AccountManagement;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\UrlInterface;
use Magento\GiftMessage\Model\CompositeConfigProvider;
use Magento\Paypal\Model\Config as PaypalConfig;
use Magento\Quote\Api\CartItemRepositoryInterface as QuoteItemRepository;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface;
use Magento\Quote\Api\ShippingMethodManagementInterface;
use Magento\Quote\Model\Cart\ShippingMethod;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Osc\Helper\Address as OscHelper;
use Mageplaza\Osc\Model\System\Config\Backend\SealBlockImage;
use Mageplaza\Osc\Model\System\Config\Source\AllowGuestCheckout;
use Zend_Serializer_Exception;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class DefaultConfigProvider implements ConfigProviderInterface
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var PaymentMethodManagementInterface
     */
    protected $paymentMethodManagement;

    /**
     * @var ShippingMethodManagementInterface
     */
    protected $shippingMethodManagement;

    /**
     * @var \Magento\Checkout\Model\CompositeConfigProvider
     */
    protected $giftMessageConfigProvider;

    /**
     * @var ModuleManager
     */
    protected $moduleManager;

    /**
     * @var OscHelper
     */
    protected $_oscHelper;

    /**
     * @var QuoteItemRepository
     */
    protected $quoteItemRepository;

    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var Block
     */
    protected $cmsBlock;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var PaypalConfig
     */
    protected $paypalConfig;

    /**
     * @var UrlInterface
     */
    protected $url;
    /**
     * @var Data
     */
    protected $directoryHelper;

    /**
     * DefaultConfigProvider constructor.
     *
     * @param CheckoutSession $checkoutSession
     * @param PaymentMethodManagementInterface $paymentMethodManagement
     * @param ShippingMethodManagementInterface $shippingMethodManagement
     * @param CompositeConfigProvider $configProvider
     * @param QuoteItemRepository $quoteItemRepository
     * @param StockRegistryInterface $stockRegistry
     * @param ModuleManager $moduleManager
     * @param OscHelper $oscHelper
     * @param Block $cmsBlock
     * @param StoreManagerInterface $storeManager
     * @param PaypalConfig $paypalConfig
     * @param UrlInterface $url
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        PaymentMethodManagementInterface $paymentMethodManagement,
        ShippingMethodManagementInterface $shippingMethodManagement,
        CompositeConfigProvider $configProvider,
        QuoteItemRepository $quoteItemRepository,
        StockRegistryInterface $stockRegistry,
        ModuleManager $moduleManager,
        OscHelper $oscHelper,
        Block $cmsBlock,
        StoreManagerInterface $storeManager,
        PaypalConfig $paypalConfig,
        UrlInterface $url,
        DirectoryHelper $directoryHelper
    ) {
        $this->checkoutSession           = $checkoutSession;
        $this->paymentMethodManagement   = $paymentMethodManagement;
        $this->shippingMethodManagement  = $shippingMethodManagement;
        $this->giftMessageConfigProvider = $configProvider;
        $this->quoteItemRepository       = $quoteItemRepository;
        $this->stockRegistry             = $stockRegistry;
        $this->moduleManager             = $moduleManager;
        $this->_oscHelper                = $oscHelper;
        $this->cmsBlock                  = $cmsBlock;
        $this->storeManager              = $storeManager;
        $this->paypalConfig              = $paypalConfig;
        $this->url                       = $url;
        $this->directoryHelper           = $directoryHelper;
    }

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function getConfig()
    {
        if (!$this->_oscHelper->isOscPage()) {
            return [];
        }

        $output = [
            'shippingMethods'       => $this->getShippingMethods(),
            'selectedShippingRate'  => !empty($existShippingMethod = $this->checkoutSession->getQuote()
                ->getShippingAddress()->getShippingMethod())
                ? $existShippingMethod : $this->_oscHelper->getDefaultShippingMethod(),
            'paymentMethods'        => $this->getPaymentMethods(),
            'selectedPaymentMethod' => $this->_oscHelper->getDefaultPaymentMethod(),
            'oscConfig'             => $this->getOscConfig(),
            'checkVersion'          => $this->_oscHelper->checkVersion()
        ];

        return $output;
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws Zend_Serializer_Exception
     */
    private function getOscConfig()
    {
        return [
            'addressFields'           => $this->_oscHelper->getAddressFields(),
            'autocomplete'            => [
                'type'                   => $this->_oscHelper->getAutoDetectedAddress(),
                'google_default_country' => $this->_oscHelper->getGoogleSpecificCountry(),
            ],
            'register'                => [
                'dataPasswordMinLength'        => $this->_oscHelper
                    ->getConfigValue(AccountManagement::XML_PATH_MINIMUM_PASSWORD_LENGTH),
                'dataPasswordMinCharacterSets' => $this->_oscHelper
                    ->getConfigValue(AccountManagement::XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER)
            ],
            'allowGuestCheckout'      => $this->allowGuestCheckout(),
            'showBillingAddress'      => $this->_oscHelper->getShowBillingAddress(),
            'newsletterDefault'       => $this->_oscHelper->isSubscribedByDefault(),
            'isUsedGiftWrap'          => (bool) $this->checkoutSession->getQuote()
                ->getShippingAddress()->getUsedGiftWrap(),
            'giftMessageOptions'      => array_merge_recursive($this->giftMessageConfigProvider->getConfig(), [
                'isEnableOscGiftMessageItems' => $this->_oscHelper->isEnableGiftMessageItems()
            ]),
            'isDisplaySocialLogin'    => $this->isDisplaySocialLogin(),
            'isUsedMaterialDesign'    => $this->_oscHelper->isUsedMaterialDesign(),
            'isAmazonAccountLoggedIn' => false,
            'geoIpOptions'            => [
                'isEnableGeoIp' => $this->_oscHelper->isEnableGeoIP(),
                'geoIpData'     => $this->_oscHelper->getGeoIpData()
            ],
            'compatible'              => [
                'isEnableModulePostNL' => $this->_oscHelper->isEnableModulePostNL(),
            ],
            'show_toc'                => $this->_oscHelper->getShowTOC(),
            'qtyIncrements'           => $this->getItemQtyIncrement(),
            'sealBlock'               => $this->getSealBlock(),
            'isShowItemListToggle'    => $this->_oscHelper->isShowItemListToggle(),
            'paymentCustomBtn'        => $this->getPaymentCustomBtn(),
            'updateCartUrl'           => $this->url->getUrl(
                'onestepcheckout/index/updateItemOptions',
                ['_secure' => true]
            ),
            'directoryData' => $this->getDirectoryData()
        ];
    }

    /**
     * @return array
     */
    public function getDirectoryData()
    {
        $output = [];
        $regionsData = $this->directoryHelper->getRegionData();
        /**
         * @var string $code
         * @var \Magento\Directory\Model\Country $data
         */
        foreach ($this->directoryHelper->getCountryCollection() as $code => $data) {
            $output[$code]['name'] = $data->getName();
            if (array_key_exists($code, $regionsData)) {
                foreach ($regionsData[$code] as $key => $region) {
                    $output[$code]['regions'][$key]['code'] = $region['code'];
                    $output[$code]['regions'][$key]['name'] = $region['name'];
                }
            }
        }
        return $output;
    }

    /**
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function allowGuestCheckout()
    {
        $allow = $this->_oscHelper->getAllowGuestCheckout($this->checkoutSession->getQuote());

        return (int) $allow !== AllowGuestCheckout::REQUIRE_CREATE_ACCOUNT;
    }

    /**
     * Return array of static blocks
     *
     * @return string
     * @throws LocalizedException
     */
    public function getSealBlock()
    {
        $sealContent = '';

        if ($this->_oscHelper->isEnabledSealBlock() === 1) {
            $blockId     = $this->_oscHelper->getSealStaticBlock();
            $sealContent = $this->cmsBlock->setBlockId($blockId)->toHtml();
        } else {
            if ($this->_oscHelper->isEnabledSealBlock() === 2) {
                $mediaUrl        = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
                $sealImage       = $mediaUrl . SealBlockImage::UPLOAD_DIR . $this->_oscHelper->getSealImage();
                $sealDescription = $this->_oscHelper->getSealDescription();

                $sealContent = '<img alt="seal-img" src="' . $sealImage . '"><p>' . $sealDescription . '</p>';
            }
        }

        return $sealContent;
    }

    /**
     * Returns array of payment methods
     *
     * @return array
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    private function getPaymentMethods()
    {
        $paymentMethods = [];
        $quote          = $this->checkoutSession->getQuote();
        if (!$quote->getIsVirtual()) {
            foreach ($this->paymentMethodManagement->getList($quote->getId()) as $paymentMethod) {
                $paymentMethods[] = [
                    'code'  => $paymentMethod->getCode(),
                    'title' => $paymentMethod->getTitle()
                ];
            }
        }

        return $paymentMethods;
    }

    /**
     * @return ShippingMethodInterface[]
     * @throws NoSuchEntityException
     * @throws StateException
     * @throws LocalizedException
     */
    private function getShippingMethods()
    {
        $methodLists = $this->shippingMethodManagement->getList($this->checkoutSession->getQuote()->getId());
        /** @var ShippingMethod $method */
        foreach ($methodLists as $key => $method) {
            $methodLists[$key] = $method->__toArray();
        }

        return $methodLists;
    }

    /**
     * Retrieve quote item data
     *
     * @return array
     */
    private function getItemQtyIncrement()
    {
        $itemQty = [];

        try {
            $quoteId = $this->checkoutSession->getQuote()->getId();
            if ($quoteId) {
                /** @var array $quoteItems */
                $quoteItems = $this->quoteItemRepository->getList($quoteId);

                /** @var Item $item */
                foreach ($quoteItems as $item) {
                    $stockItem = $this->stockRegistry->getStockItem(
                        $item->getProduct()->getId(),
                        $item->getStore()->getWebsiteId()
                    );
                    if ($stockItem->getEnableQtyIncrements() && $stockItem->getQtyIncrements()) {
                        $itemQty[$item->getId()] = $stockItem->getQtyIncrements() ?: 1;
                    }
                }
            }
        } catch (Exception $e) {
            $itemQty = [];
        }

        return $itemQty;
    }

    /**
     * @return bool
     */
    private function isDisplaySocialLogin()
    {
        return $this->moduleManager->isOutputEnabled('Mageplaza_SocialLogin')
            && !$this->_oscHelper->isDisabledSocialLoginOnCheckout();
    }

    /**
     * Payment methods that have custom button which replace default place order button
     *
     * @return array
     */
    public function getPaymentCustomBtn()
    {
        $result = [];

        $this->paypalConfig->setMethod(PaypalConfig::METHOD_EXPRESS);

        if (!$this->_oscHelper->checkVersion() && $this->paypalConfig->getValue('in_context')) {
            $result[] = 'paypal_express';
        }

        return $result;
    }
}
