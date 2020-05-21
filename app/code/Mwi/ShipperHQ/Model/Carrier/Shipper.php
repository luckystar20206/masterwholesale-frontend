<?php

namespace Mwi\ShipperHQ\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use ShipperHQ\Shipper\Helper\Config;
use ShipperHQ\WS\Client;

class Shipper extends \ShipperHQ\Shipper\Model\Carrier\Shipper
{

    public $orderTotal; //order total from M2
    private $costSubTotal; //total cogs for order
    private $prefMargin = .25; //minimum margin
    private $maxShipCost = 150; //preferred maximum ship cost
    private $liftgateFee = 75;
    /**
     * Flag for check carriers for activity
     *
     * @var string
     */
    const ACTIVE_FLAG = 'active';

    const IGNORE_EMPTY_ZIP = 'ignore_empty_zip';
    private static $shippingOptions = ['liftgate_required', 'notify_required', 'inside_delivery', 'destination_type'];
    /**
     * @var string
     */
    protected $_code = 'shipper';
    protected $shipperRequest = null;
    /**
     * Raw rate request data
     *
     * @var Varien_Object|null
     */
    protected $rawRequest = null;
    /**
     * @var Config
     */
    protected $shipperDataHelper;
    /**
     * @var \ShipperHQ\Shipper\Helper\Rest
     */
    protected $restHelper;
    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $rateMethodFactory;
    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $rateFactory;
    /**
     * Application Event Dispatcher
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;
    /**
     * @var \ShipperHQ\Shipper\Helper\Package
     */
    protected $packageHelper;
    /**
     *
     * @var \ShipperHQ\Shipper\Helper\CarrierGroup
     */
    protected $carrierGroupHelper;
    /**
     * Rate result data
     *
     * @var Mage_Shipping_Model_Rate_Result|null
     */
    protected $result = null;
    protected $quote;
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    /**
     * @var \ShipperHQ\Shipper\Helper\LogAssist
     */
    private $shipperLogger;
    /**
     * @var Client\WebServiceClientFactory
     */
    private $shipperWSClientFactory;
    /**
     * @var Processor\CarrierConfigHandler
     */
    private $carrierConfigHandler;
    /**
     * @var \ShipperHQ\Shipper\Helper\CarrierCache
     */
    private $carrierCache;
    /**
     * @var Processor\BackupCarrier
     */
    private $backupCarrier;
    /**
     * @var \ShipperHQ\Lib\Rate\Helper
     */
    private $shipperRateHelper;
    /**
     * @var \ShipperHQ\Lib\Rate\ConfigSettingsFactory
     */
    private $configSettingsFactory;
    private $allowedMethodsHelper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;
    protected $_scopeConfig;

    public function __construct(
        \ShipperHQ\Shipper\Helper\Data $shipperDataHelper,
        \ShipperHQ\Shipper\Helper\Rest $restHelper,
        \ShipperHQ\Shipper\Helper\CarrierCache $carrierCache,
        \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger,
        \ShipperHQ\Shipper\Model\Carrier\Processor\ShipperMapper $shipperMapper,
        \ShipperHQ\Shipper\Model\Carrier\Processor\CarrierConfigHandler $carrierConfigHandler,
        \ShipperHQ\Shipper\Model\Carrier\Processor\BackupCarrier $backupCarrier,
        \Magento\Framework\Registry $registry,
        \ShipperHQ\WS\Client\WebServiceClientFactory $shipperWSClientFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $resultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \ShipperHQ\Shipper\Helper\CarrierGroup $carrierGroupHelper,
         \ShipperHQ\Lib\Rate\Helper $shipperLibRateHelper,
        \ShipperHQ\Lib\Rate\ConfigSettingsFactory $configSettingsFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \ShipperHQ\Lib\AllowedMethods\Helper $allowedMethodsHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \ShipperHQ\Shipper\Helper\Package $packageHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {

        $this->shipperDataHelper = $shipperDataHelper;
        $this->restHelper = $restHelper;
        $this->shipperMapper = $shipperMapper;
        $this->rateFactory = $resultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->registry = $registry;
        $this->shipperLogger = $shipperLogger;
        $this->shipperWSClientFactory = $shipperWSClientFactory;
        $this->_rateErrorFactory = $rateErrorFactory;
        $this->carrierConfigHandler = $carrierConfigHandler;
        $this->carrierCache = $carrierCache;
        $this->backupCarrier = $backupCarrier;
        $this->carrierGroupHelper = $carrierGroupHelper;
        $this->shipperRateHelper = $shipperLibRateHelper;
        $this->configSettingsFactory = $configSettingsFactory;
        $this->eventManager = $eventManager;
        $this->allowedMethodsHelper = $allowedMethodsHelper;
        $this->checkoutSession = $checkoutSession;
        $this->packageHelper = $packageHelper;
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * Collect and get rates
     *
     * @param RateRequest $request
     * @return bool|Result|Error
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag(self::ACTIVE_FLAG)) {
            return false;
        }

        //Set quote on request so we can get store ID from quote
        if (is_array($request->getAllItems())) {
            $item = current($request->getAllItems());
            if ($item instanceof QuoteItem) {
                $request->setQuote($item->getQuote());
                $this->quote = $item->getQuote();
            }
        }

        if (!$this->shipperDataHelper->getCredentialsEntered($request)) {
            $this->shipperLogger->postDebug(
                'Shipperhq_Shipper',
                'No credentials entered',
                'Missing API key or Authentication key. Ignoring request'
            );
            return false;
        }

        if ($request->getDestPostcode() === null && $this->getConfigFlag(self::IGNORE_EMPTY_ZIP)) {
            $this->shipperLogger->postDebug(
                'Shipperhq_Shipper',
                'Ignoring rate request',
                'Configuration settings are to ignore requests as zipcode is empty'
            );
            return false;
        }
        $initVal = microtime(true);

        $this->cacheEnabled = $this->getConfigFlag('use_cache');
        $this->orderTotal = $request->getBaseSubtotalInclTax();
        $this->setRequest($request);
        $this->result = $this->getQuotes();
        $elapsed = microtime(true) - $initVal;
        $this->shipperLogger->postDebug('Shipperhq_Shipper', 'Long lapse', $elapsed);

        return $this->getResult();
    }

    public function getConfigData($field)
    {
        if (empty($this->_code)) {
            return false;
        }
        $path = 'carriers/' . $this->_code . '/' . $field;

        return $this->_scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()
        );
    }

    public function saveConfig($path, $value, $scope = 'default', $scopeId = 0, $refreshRequired = true)
    {
        if ($this->shipperDataHelper->getConfigValue($path) != $value) {
            $this->resourceConfig->saveConfig(rtrim($path, '/'), $value, $scope, $scopeId);
            if ($refreshRequired) {
                $this->shipperDataHelper->getCheckout()->setConfigUpdated(true);
            }
        }
    }

    public function getAllShippingMethods()
    {
        $this->carrierConfigHandler->saveConfig(
            \ShipperHQ\Shipper\Model\System\Message\Credentials::SHIPPERHQ_INVALID_CREDENTIALS_SUPPLIED,
            0
        );

        $result = [];
        $allowedMethods = [];

        $credentialsPerStore = $this->shipperMapper->getAllCredentialsTranslation();

        $allAllowedMethodsResponse = [];

        foreach ($credentialsPerStore as $storeId => $credentials) {
            $allMethodsRequest = $credentials;
            $requestString = $this->carrierCache->serialize($allMethodsRequest);
            $resultSet = $this->carrierCache->getCachedQuotes($requestString, $this->getCarrierCode());
            $timeout = $this->restHelper->getWebserviceTimeout();
            if (!$resultSet) {
                $allowedMethodUrl = $this->restHelper->getAllowedMethodGatewayUrl();
                $resultSet = $this->shipperWSClientFactory->create()->sendAndReceive(
                    $allMethodsRequest,
                    $allowedMethodUrl,
                    $timeout
                );
            }

            //Todo add store name to log output
            $allowedMethodResponse = $this->object_to_array($resultSet['result']);
            $debugData = $resultSet['debug'];
            $this->shipperLogger->postDebug('Shipperhq_Shipper', 'Allowed methods response', $debugData);
            if (!is_array($allowedMethodResponse)) {
                $this->shipperLogger->postInfo(
                    'Shipperhq_Shipper',
                    'Allowed Methods: No or invalid response received from Shipper HQ',
                    $allowedMethodResponse
                );
                $shipperHQ = "<a href=https://shipperhq.com/ratesmgr/websites>ShipperHQ</a> ";
                $result['result'] = false;
                $result['error'] = 'ShipperHQ is not contactable, verify the details from the website configuration in '
                    . $shipperHQ;

                return $result;
            } elseif (isset($allowedMethodResponse['errors']) && !empty($allowedMethodResponse['errors'])) {
                $this->shipperLogger->postInfo(
                    'Shipperhq_Shipper',
                    'Allowed methods: response contained following errors',
                    $allowedMethodResponse
                );
                $error = 'ShipperHQ Error: ';
                foreach ($allowedMethodResponse['errors'] as $anError) {
                    if (isset($anError['internalErrorMessage'])) {
                        $error .= ' ' . $anError['internalErrorMessage'];
                    } elseif (isset($anError['externalErrorMessage']) && $anError['externalErrorMessage'] != '') {
                        $error .= ' ' . $anError['externalErrorMessage'];
                    }
                    //SHQ16-1708
                    if (isset($anError['errorCode']) && $anError['errorCode'] == '3') {
                        $this->carrierConfigHandler->saveConfig(
                            \ShipperHQ\Shipper\Model\System\Message\Credentials::SHIPPERHQ_INVALID_CREDENTIALS_SUPPLIED,
                            1
                        );
                    }
                }
                $result['result'] = false;
                $result['error'] = $error;

                return $result;
            } elseif (empty($allowedMethodResponse['carrierMethods'])) {
                $this->shipperLogger->postInfo(
                    'Shipperhq_Shipper',
                    'Allowed methods web service did not return any carriers or shipping methods',
                    $allowedMethodResponse
                );
                $result['result'] = false;
                $result['warning'] =
                    'ShipperHQ Warning: No carriers setup, log in to ShipperHQ Dashboard and create carriers';

                return $result;
            }

            $this->carrierCache->setCachedQuotes($requestString, $resultSet, $this->getCarrierCode());

            $allAllowedMethodsResponse[] = $allowedMethodResponse;
        }

        $carrierConfig = $this->allowedMethodsHelper->extractAllowedMethodsAndCarrierConfig(
            $allAllowedMethodsResponse,
            $allowedMethods
        );

        $this->shipperLogger->postDebug(
            'Shipperhq_Shipper',
            'Allowed methods parsed result ',
            $allowedMethods
        );
        // go set carrier titles
        $this->carrierConfigHandler->setCarrierConfig($carrierConfig);
        $this->saveAllowedMethods($allowedMethods);

        //SHQ18-112 persist Magento version to config to increase efficiency of future requests
        $this->carrierConfigHandler->saveConfig(
            'carriers/shipper/magento_version',
            $this->shipperMapper->getMagentoVersion()
        );

        //SHQ18-2680 Persist edition to config  to increase efficiency of future requests
        $this->carrierConfigHandler->saveConfig(
            'carriers/shipper/magento_edition',
            $this->shipperMapper->getMagentoEdition()
        );

        return $allowedMethods;
    }

    public function saveAllowedMethods($allowedMethodsArray)
    {
        $carriersCodesString = $this->shipperDataHelper->encode($allowedMethodsArray);
        $this->carrierConfigHandler->saveConfig(
            $this->shipperDataHelper->getAllowedMethodsPath(),
            $carriersCodesString
        );
    }

    public function getAllowedMethodsByCode($requestedCode = null)
    {
        $arr = [];

        $allowedConfigValue = $this->shipperDataHelper->getConfigValue(
            $this->shipperDataHelper->getAllowedMethodsPath()
        );

        $allowed = $this->shipperDataHelper->decode($allowedConfigValue);
        if ($allowed === null) {
            $this->shipperLogger->postDebug(
                'Shipperhq_Shipper',
                'Allowed methods config is empty',
                'Please refresh your carriers by pressing Save button on the shipping method configuration screen '
                . 'from Stores > Configuration > Shipping Methods'
            );
            return $arr;
        }
        $arr = $this->allowedMethodsHelper->getAllowedMethodsArray($allowed, $requestedCode);

        if (count($arr) < 1 && $this->getConfigFlag(self::ACTIVE_FLAG)) {
            $this->shipperLogger->postDebug(
                'Shipperhq_Shipper',
                'There are no allowed methods for ' . $requestedCode,
                'If you expect to see shipping methods for this carrier, '
                . 'please refresh your carriers by pressing Save button on the shipping method configuration screen '
                . 'from Stores > Configuration > Shipping Methods'
            );
        }
        return $arr;
    }

    /**
     * Prepare and set request to this instance
     *
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @return $this
     */
    public function setRequest(\Magento\Quote\Model\Quote\Address\RateRequest $request)
    {
        //SHQ16-1261 - further detail as values not on shipping address
        if (!$this->quote) {
            $this->quote = $this->shipperDataHelper->getQuote();
        }
        $shippingAddress = $this->quote->getShippingAddress();

        $key = $this->shipperDataHelper->getAddressKey($shippingAddress);
        $existing = $this->getExistingValidation($key); //SHQ16-1902
        $validate = true;
        if (is_array($existing) && !empty($existing)) {
            if (isset($existing['key']) && $existing['key'] == $key) {
                $validate = false;
            }
        } else {
            $validate = $this->shipperRateHelper->shouldValidateAddress(
                $shippingAddress->getValidationStatus(),
                $shippingAddress->getDestinationType()
            );
        }
        $request->setValidateAddress($validate);

        $request->setSelectedOptions($this->getSelectedOptions($shippingAddress, $existing));

        $isCheckout = $this->shipperDataHelper->isCheckout($this->quote);
        $cartType = ($isCheckout !== null && $isCheckout != 1) ? "CART" : "STD";
        if ($this->quote->getIsMultiShipping()) {
            $cartType = 'MAC';
        }
        $request->setCartType($cartType);

        $remoteIP = $this->quote->getRemoteIp();
        $request->setIpAddress($remoteIP);
        $this->eventManager->dispatch(
            'shipperhq_carrier_set_request',
            ['request' => $request]
        );

        $this->shipperRequest = $this->shipperMapper->getShipperTranslation($request);
        $this->rawRequest = $request;
        return $this;
    }

    private function getExistingValidation($key)
    {
        $sessionValues = $this->checkoutSession->getShipAddressValidation();
        if (is_array($sessionValues)) {
            if (isset($sessionValues['key']) && $sessionValues['key'] == $key) {
                return $sessionValues;
            }
        }
        return [];
    }

    private function getSelectedOptions($shippingAddress, $sessionValues = [])
    {
        $shipOptions = [];

        foreach (self::$shippingOptions as $option) {
            if (is_array($sessionValues) && isset($sessionValues[$option])) {
                $this->shipperLogger->postDebug(
                    'ShipperHQ Shipper',
                    'Using Session value for setting option ' . $option,
                    $sessionValues[$option]
                );
                $shipOptions[] = ['name' => $option, 'value' => $sessionValues[$option]];
            } elseif ($shippingAddress->getData($option) != '') {
                $this->shipperLogger->postDebug(
                    'ShipperHQ Shipper',
                    'Using Shipping Address value for setting option ' . $option,
                    $shippingAddress->getData($option)
                );
                $shipOptions[] = ['name' => $option, 'value' => $shippingAddress->getData($option)];
            }
        }
        return $shipOptions;
    }

    /**
     * Do remote request for and handle errors
     *
     * @return Mage_Shipping_Model_Rate_Result
     */
    private function getQuotes()
    {
        $requestString = $this->carrierCache->serialize($this->shipperRequest);
        $resultSet = $this->carrierCache->getCachedQuotes($requestString, $this->getCarrierCode());
        $timeout = $this->restHelper->getWebserviceTimeout();
        if (!$resultSet) {
            $initVal = microtime(true);
            $resultSet = $this->shipperWSClientFactory->create()->sendAndReceive(
                $this->shipperRequest,
                $this->restHelper->getRateGatewayUrl(),
                $timeout
            );
            $elapsed = microtime(true) - $initVal;
            $this->shipperLogger->postDebug('Shipperhq_Shipper', 'Short lapse', $elapsed);

            if (!$resultSet['result']) {
                $backupRates = $this->backupCarrier->getBackupCarrierRates(
                    $this->rawRequest,
                    $this->getConfigData("backup_carrier")
                );
                if ($backupRates) {
                    return $backupRates;
                }
            }
            $this->carrierCache->setCachedQuotes($requestString, $resultSet, $this->getCarrierCode());
        }
        $this->shipperLogger->postInfo('Shipperhq_Shipper', 'Rate request and result', $resultSet['debug']);
        return $this->parseShipperResponse($resultSet['result']);
    }

    /**
     * @param $shipperResponse
     * @return Mage_Shipping_Model_Rate_Result
     */
    private function parseShipperResponse($shipperResponse)
    {

        $debugRequest = $this->shipperRequest;

        // SHQ18-2869 process response as array
        $shipperResponse = $this->object_to_array($shipperResponse);

        $debugData = ['request' => json_encode($debugRequest, JSON_PRETTY_PRINT), 'response' => $shipperResponse];
        if (!is_object($shipperResponse) && !is_array($shipperResponse)) {
            $this->shipperLogger->postInfo('Shipperhq_Shipper', 'Shipper HQ did not return a response', $debugData);

            return $this->returnGeneralError(
                'Shipper HQ did not return a response - could not contact ShipperHQ. Please review your settings'
            );
        }
        $transactionId = $this->shipperRateHelper->extractTransactionId($shipperResponse);
        $this->registry->unregister('shipperhq_transaction');

        $this->registry->register('shipperhq_transaction', $transactionId);

        //first check and save globals for display purposes
        $globals = [];
        if(is_array($shipperResponse) && array_key_exists('globalSettings', $shipperResponse)) {
            $globals = $this->shipperRateHelper->extractGlobalSettings($shipperResponse);
            $globals['transaction'] = $transactionId;
            $this->shipperDataHelper->setGlobalSettings($globals);
        }

        $result = $this->rateFactory->create();

        // If no rates are found return error message
        if (!empty($shipperResponse->errors)) {
            $this->shipperLogger->postInfo('Shipperhq_Shipper', 'Shipper HQ returned an error', $debugData);
            if (isset($shipperResponse['errors'])) {
                foreach ($shipperResponse['errors'] as $error) {
                    $this->appendError($result, $error, $this->_code, $this->getConfigData('title'));
                }
            }
            return $result;
        }

        if (isset($shipperResponse['carrierGroups'])) {
            $carrierRates = $this->processRatesResponse($shipperResponse, $transactionId);
        } else {
            $carrierRates = [];
        }

        if ($this->rawRequest->getValidateAddress()) { //SHQ18-141 only persist address validation if we have validated
            $this->persistAddressValidation($shipperResponse);
        }

        if (count($carrierRates) == 0) {
            $this->shipperLogger->postInfo(
                'Shipperhq_Shipper',
                'Shipper HQ did not return any carrier rates',
                $debugData
            );
            return $result;
        }

        foreach ($carrierRates as $carrierRate) {
            if (isset($carrierRate['error'])) {
                $carriergroupId = null;
                $carrierGroupDetail = null;
                if (array_key_exists('carriergroup_detail', $carrierRate)
                    && isset($carrierRate['carriergroup_detail'])
                ) {
                    if (array_key_exists('carrierGroupId', $carrierRate['carriergroup_detail'])) {
                        $carriergroupId = $carrierRate['carriergroup_detail']['carrierGroupId'];
                    }
                    $carrierGroupDetail = $carrierRate['carriergroup_detail'];
                }
                $this->appendError(
                    $result,
                    $carrierRate['error'],
                    $carrierRate['code'],
                    $carrierRate['title'],
                    $carriergroupId,
                    $carrierGroupDetail
                );
                continue;
            }

            if (!array_key_exists('rates', $carrierRate)) {
                $this->shipperLogger->postInfo(
                    'Shipperhq_Shipper',
                    'Shipper HQ did not return any rates for '
                    . $carrierRate['code']
                    . ' '
                    . $carrierRate['title'],
                    $debugData
                );
            } else {
                $baseRate = 1;
                $baseCurrencyCode = $this->shipperDataHelper->getBaseCurrencyCode();
                foreach ($carrierRate['rates'] as $rateDetails) {
                    if (isset($rateDetails['currency'])) {
                        if ($rateDetails['currency'] != $baseCurrencyCode || $baseRate != 1) {
                            $baseRate = $this->shipperDataHelper->getBaseCurrencyRate($rateDetails['currency']);
                            if (!$baseRate) {
                                $error = __('Can\'t convert rate from "%1".', $rateDetails['currency']);
                                $this->appendError(
                                    $result,
                                    $error,
                                    $carrierRate['code'],
                                    $carrierRate['title'],
                                    $rateDetails['carriergroup_detail']['carrierGroupId'],
                                    $rateDetails['carriergroup_detail']
                                );
                                $this->shipperLogger->postCritical(
                                    'Shipperhq_Shipper',
                                    'Currency Rate Missing',
                                    'Currency code in shipping rate is ' . $rateDetails['currency']
                                    . ' but there is no currency conversion rate configured'
                                    . ' so we cannot display this shipping rate'
                                );
                                continue;
                            }
                        }
                    }

                    $rate = $this->rateMethodFactory->create();
                    $rate->setCarrier($carrierRate['code']);
                    $lengthCarrierCode = strlen($carrierRate['code']);

                    $rate->setCarrierTitle(__($carrierRate['title']));

                    $methodCombineCode = preg_replace('/&|;| /', "", $rateDetails['methodcode']);
                    //SHQ16-1520 - enforce limit on length of shipping carrier code
                    // and method code of less than 35 - M2 hard limit of 40
                    $lengthMethodCode = strlen($methodCombineCode);

                    if ($lengthCarrierCode + $lengthMethodCode > 38) {
                        $total = $lengthCarrierCode + $lengthMethodCode;
                        $trim = $total - 35;
                        $methodCombineCode = substr($methodCombineCode, $trim, $lengthMethodCode);
                    }
                    $rate->setMethodTitle(__($rateDetails['method_title']));
                    $rate->setMethod($methodCombineCode);
                    $tooltip = "";
                    if (isset($rateDetails['tooltip']) && !empty($rateDetails['tooltip'])) {
                        $tooltip = $rateDetails['tooltip'];
                    } elseif (isset($carrierRate['custom_description']) && !empty($carrierRate['custom_description'])) {
                        $tooltip = $carrierRate['custom_description'];
                    }
                    $rate->setTooltip($tooltip);

                    if (array_key_exists('method_description', $rateDetails)) {
                        $rate->setMethodDescription(__($rateDetails['method_description']));
                    }

                    //SHQ18-1804 presence of customsMessage means we need to include the customs for display
                    //If customsMessage is blank, it's likely we are meant to hide duties.
                    if (isset($rateDetails['carriergroup_detail']['customDuties']) &&
                        $rateDetails['carriergroup_detail']['customDuties'] > 0 &&
                        isset($rateDetails['carriergroup_detail']['customsMessage']) &&
                        $rateDetails['carriergroup_detail']['customsMessage'] != '') {

                        $dutiesMessage = sprintf("$%01.2f ", $rateDetails['carriergroup_detail']['customDuties']) . $rateDetails['carriergroup_detail']['customsMessage'];
                        $rate->setCustomDuties(__($dutiesMessage));
                    }

                    if (isset($rateDetails['carriergroup_detail']['hideNotifications']) && $rateDetails['carriergroup_detail']['hideNotifications']) {
                        $rate->setHideNotifications($rateDetails['carriergroup_detail']['hideNotifications']);
                    }

                    // Update freight rates - this adjusts the shipping cost down for shipped UPS freight orders
                    if($carrierRate['code'] == "shqupsfreight1" && $rateDetails['price'] > 100){
                        //$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/logfile.log');
                        //$logger = new \Zend\Log\Logger();
                        //$logger->addWriter($writer);
                        //$logger->info('Array Log'.print_r(, true)); // Array Log
                        $desc = ($rateDetails["carriergroup_detail"]["destination_type"] == 'RESIDENTIAL') ? 'Residential Address LTL with liftgage (additional fees may apply)' : 'Commercial LTL';
                        $rate->setCarrierTitle(__($desc));
                        $this->costSubTotal = 0;
                        $this->orderTotal = 0;
                        $items = $this->quote->getAllVisibleItems();
                        foreach($items as $item) {
                          $this->costSubTotal += ($item->getQty())*(($item->getBaseCost())?$item->getBaseCost():$item->getPrice());
                          $this->orderTotal += $item->getQty() * $item->getPrice();
                        }
                        $totalCost = (($this->orderTotal + $this->maxShipCost)*($this->prefMargin - $this->margin($rateDetails['cost']))) + $this->maxShipCost;
                        $totalCost = ($totalCost > 0) ? $totalCost : 0;
                        $rate->setCost($totalCost * $baseRate);
                        $rate->setPrice($totalCost * $baseRate);
                    } elseif ($carrierRate['code'] == "shqupsfreight1" && $rateDetails['price'] == $this->liftgateFee) {
                        $desc = ($rateDetails["carriergroup_detail"]["destination_type"] == 'RESIDENTIAL') ? 'Residential Address LTL with liftgage (additional fees may apply)' : 'Commercial LTL';
                        $rate->setCarrierTitle(__($desc));
                        $rate->setCost($rateDetails['cost'] * $baseRate);
                        $rate->setPrice($rateDetails['price'] * $baseRate);
                    } else {
                        $rate->setCost($rateDetails['cost'] * $baseRate);
                        $rate->setPrice($rateDetails['price'] * $baseRate);
                    }


                    if (array_key_exists('nypAmount', $rateDetails)) {
                        $rate->setNypAmount($rateDetails['nypAmount'] * $baseRate);
                    }

                    if (array_key_exists('carrier_type', $rateDetails)) {
                        $rate->setCarrierType($rateDetails['carrier_type']);
                    }

                    if (array_key_exists('carrier_id', $rateDetails)) {
                        $rate->setCarrierId($rateDetails['carrier_id']);
                    }

                    if (array_key_exists('dispatch_date', $rateDetails)) {
                        $rate->setDispatchDate($rateDetails['dispatch_date']);
                    }

                    if (array_key_exists('delivery_date', $rateDetails)) {
                        $rate->setDeliveryDate($rateDetails['delivery_date']);
                    }

                    if (array_key_exists('carriergroup_detail', $rateDetails)
                        && isset($rateDetails['carriergroup_detail'])
                    ) {
                        $carrierGroupDetail = $baseRate != 1 ? $this->updateWithCurrrencyConversion(
                            $rateDetails['carriergroup_detail'],
                            $baseRate
                        ) :
                            $rateDetails['carriergroup_detail'];

                        $rate->setCarriergroupShippingDetails(
                            $this->shipperDataHelper->encode($carrierGroupDetail)
                        );
                        if (array_key_exists('carrierGroupId', $carrierGroupDetail)) {
                            $rate->setCarriergroupId($carrierGroupDetail['carrierGroupId']);
                        }

                        if (array_key_exists('checkoutDescription', $carrierGroupDetail)) {
                            $rate->setCarriergroup($carrierGroupDetail['checkoutDescription']);
                        }
                    }
                    $result->append($rate);
                }
                if (isset($carrierRate['shipments'])) {
                    $this->persistShipments($carrierRate['shipments']);
                }
            }
        }
        return $result;
    }

    /**
     *
     * Build up an error message when no carrier rates returned
     * @return Mage_Shipping_Model_Rate_Result
     */
    private function returnGeneralError($message = null)
    {
        $result = $this->rateFactory->create();
        $error = $this->_rateErrorFactory->create();
        $error->setCarrier($this->_code);
        $error->setCarrierTitle($this->getConfigData('title'));
        $error->setCarriergroupId('');
        if ($message && $this->shipperDataHelper->getConfigValue('carriers/shipper/debug')) {
            $error->setErrorMessage($message);
        } else {
            $error->setErrorMessage($this->getConfigData('specificerrmsg'));
        }
        $result->append($error);
        return $result;
    }

    /**
     *
     * Generate error message from ShipperHQ response.
     * Display of error messages per carrier is managed in SHQ configuration
     *
     * @param $result
     * @param $errorDetails
     * @return Mage_Shipping_Model_Rate_Result
     */
    private function appendError(
        $result,
        $errorDetails,
        $carrierCode,
        $carrierTitle,
        $carrierGroupId = null,
        $carrierGroupDetail = null
    ) {

        if (is_object($errorDetails)) {
            $errorDetails = get_object_vars($errorDetails);
        }
        if ((array_key_exists('internalErrorMessage', $errorDetails) && $errorDetails['internalErrorMessage'] != '')
            || (array_key_exists('externalErrorMessage', $errorDetails) && $errorDetails['externalErrorMessage'] != '')
        ) {
            $errorMessage = false;

            if ($this->getConfigData("debug") && array_key_exists('internalErrorMessage', $errorDetails)
                && $errorDetails['internalErrorMessage'] != ''
            ) {
                $errorMessage = $errorDetails['internalErrorMessage'];
            } elseif (array_key_exists('externalErrorMessage', $errorDetails)
                && $errorDetails['externalErrorMessage'] != ''
            ) {
                $errorMessage = $errorDetails['externalErrorMessage'];
            }

            if ($errorMessage) {
                $error = $this->_rateErrorFactory->create();
                $error->setCarrier($carrierCode);
                $error->setCarrierTitle($carrierTitle);
                $error->setErrorMessage($errorMessage);
                if ($carrierGroupId !== null) {
                    $error->setCarriergroupId($carrierGroupId);
                }
                if (is_array($carrierGroupDetail) && array_key_exists('checkoutDescription', $carrierGroupDetail)) {
                    $error->setCarriergroup($carrierGroupDetail['checkoutDescription']);
                }

                $result->append($error);

                $this->shipperLogger->postInfo('Shipperhq_Shipper', 'Shipper HQ returned error', $errorDetails);
            }
        }
        return $result;
    }

    private function processRatesResponse($shipperResponse, $transactionId)
    {
        $carrierGroups = $shipperResponse['carrierGroups'];
        $ratesArray = [];

        $timezone = $this->shipperDataHelper->getConfigValue('general/locale/timezone');
        $configSettings = $this->configSettingsFactory->create([
            'hideNotifications' => $this->getConfigFlag('hide_notify'),
            'transactionIdEnabled' => $this->shipperDataHelper->isTransactionIdEnabled(),
            'locale' => $this->getLocaleInGlobals(),
            'shipperHQCode' => $this->_code,
            'shipperHQTitle' => $this->getConfigFlag('title'),
            'timezone' => $timezone
        ]);

        $splitCarrierGroupDetail = [];
        foreach ($carrierGroups as $carrierGroup) {

            $carrierGroupDetail = $this->shipperRateHelper->extractCarrierGroupDetail(
                $carrierGroup,
                $transactionId,
                $configSettings
            );
            $this->setCarriergroupOnItems($carrierGroupDetail, $carrierGroup['products']);
            //Pass off each carrier group to helper to decide best fit to process it.
            //Push result back into our array
            foreach ($carrierGroup['carrierRates'] as $carrierRate) {

                $this->carrierConfigHandler->saveCarrierResponseDetails($carrierRate, $carrierGroupDetail);
                $carrierResultWithRates = $this->shipperRateHelper->extractShipperHQRates(
                    $carrierRate,
                    $carrierGroupDetail,
                    $configSettings,
                    $splitCarrierGroupDetail
                );
                $ratesArray[] = $carrierResultWithRates;
                //push out event so other modules can save their data
                $this->eventManager->dispatch(
                    'shipperhq_carrier_rate_response_received',
                    ['carrier_rate_response' => $carrierRate, 'carrier_group_detail' => $carrierGroupDetail]
                );
            }
        }

        //check for configuration here for display
        if (isset($shipperResponse['mergedRateResponse'])) {
            $mergedRatesArray = [];
            foreach ($shipperResponse['mergedRateResponse']['carrierRates'] as $carrierRate) {

                $this->carrierConfigHandler->saveCarrierResponseDetails($carrierRate, null);
                $mergedResultWithRates = $this->shipperRateHelper->extractShipperHQMergedRates(
                    $carrierRate,
                    $splitCarrierGroupDetail,
                    $configSettings,
                    $transactionId
                );
                $mergedRatesArray[] = $mergedResultWithRates;
            }
            $ratesArray = $mergedRatesArray;
        }

        $carriergroupDescriber = $shipperResponse['globalSettings']['carrierGroupDescription'];
        if ($carriergroupDescriber != '') {
            $this->carrierConfigHandler->saveConfig(
                $this->shipperDataHelper->getCarrierGroupDescPath(),
                $carriergroupDescriber
            );
        }

        $this->carrierConfigHandler->refreshConfig();

        return $ratesArray;
    }

    private function getLocaleInGlobals()
    {
        $locale = $this->shipperDataHelper->getGlobalSetting('preferredLocale');
        return $locale ? $locale : 'en-US';
    }

    private function setCarriergroupOnItems($carriergroupDetails, $productInRateResponse)
    {
        $rateItems = [];
        foreach ($productInRateResponse as $item) {
            $rateItems[$item['sku']] = $item['qty'];
        }

        foreach ($this->rawRequest->getAllItems() as $quoteItem) {
            if (array_key_exists($quoteItem->getSku(), $rateItems)) {
                $quoteItem->setCarriergroupId($carriergroupDetails['carrierGroupId']);
                $quoteItem->setCarriergroup($carriergroupDetails['name']);

                //need to work out how to distinguish between quote address items on multi address checkout
                $this->carrierGroupHelper->saveCarrierGroupItem(
                    $quoteItem,
                    $carriergroupDetails['carrierGroupId'],
                    $carriergroupDetails['name']
                );
            }
        }
    }

    private function persistAddressValidation($shipperResponse)
    {
        //we've validated so we need to save
        $shippingAddress = $this->quote->getShippingAddress();
        $key = $this->shipperDataHelper->getAddressKey($shippingAddress);
        $existing = [];
        $addressType = $this->shipperRateHelper->extractDestinationType($shipperResponse);
        $validationStatus = $this->shipperRateHelper->extractAddressValidationStatus($shipperResponse);
        $validatedAddress = $this->shipperRateHelper->extractValidatedAddress($shipperResponse);
        if ($validationStatus) {
            $existing['validation_status'] = $validationStatus;
        }

        if ($addressType) {
            $existing['destination_type'] = $addressType;
        }

        if ($validatedAddress) {
            foreach ($validatedAddress as $field => $value) {
                if ($field == 'addressType') {
                    continue;
                }
                if ($field == 'zipcode') { // handle where it's returning zipcode instead of postcode
                    $field == 'postcode';
                }
                $existing['validated_shipping_' .$field] = $value;
            }
        }

        if (!empty($existing)) {
            $existing['key'] = $key;
            $this->checkoutSession->setShipAddressValidation($existing);
        }
    }

    /*
     *
     * Build array of rates based on split or merged rates display
     */

    private function updateWithCurrrencyConversion($carrierGroupDetail, $currencyConversionRate)
    {
        if (is_array($carrierGroupDetail) && isset($carrierGroupDetail[0])) {
            // Merged rates return a numeric array of assoc arrays. If there is a 0 key we know this is the case
            foreach ($carrierGroupDetail as $k => $detail) {
                $carrierGroupDetail[$k]['cost'] *= $currencyConversionRate;
                $carrierGroupDetail[$k]['price'] *= $currencyConversionRate;
            }
        } else {
            $carrierGroupDetail['cost'] *= $currencyConversionRate;
            $carrierGroupDetail['price'] *= $currencyConversionRate;
        }
        return $carrierGroupDetail;
    }

    private function persistShipments($shipmentArray)
    {
        $shippingAddress = $this->quote->getShippingAddress();
        $addressId = $shippingAddress->getId();
        $this->packageHelper->saveQuotePackages($addressId, $shipmentArray);
    }

    private function object_to_array($obj)
    {
        if(is_object($obj)) $obj = (array) $obj;

        if(is_array($obj)) {
            $new = array();
            foreach($obj as $key => $val) {
                $new[$key] = $this->object_to_array($val);
            }
        }
        else $new = $obj;
        return $new;
    }

    private function margin($freightQuote = 0){

        return (($this->orderTotal + $this->maxShipCost) - ($this->costSubTotal + $freightQuote))/($this->orderTotal + $this->maxShipCost);
    }
}
