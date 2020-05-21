<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


namespace Neklo\MakeAnOffer\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Neklo\MakeAnOffer\Model\Customer\Context as CustomerContext;
use Neklo\MakeAnOffer\Model\Source\Status;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Model\ProductRepository;
use Neklo\MakeAnOffer\Model\RequestFactory;
use Neklo\MakeAnOffer\Helper\Statistic;
use Magento\Framework\Data\Form\FormKey\Validator;
use Neklo\MakeAnOffer\Helper\Config;
use Neklo\MakeAnOffer\Helper\Validate;
use Magento\Framework\App\Http\Context as HttpContext;
use Neklo\MakeAnOffer\Helper\Email;

class Index extends Action
{
    /**
     * @var RequestFactory
     */
    private $requestFactory;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var Statistic
     */
    private $statisticHelper;

    /**
     * @var Validator
     */
    private $formKeyValidator;

    /**
     * @var Config
     */
    private $configHelper;

    /**
     * @var Validate
     */
    private $validateHelper;

    /**
     * @var HttpContext
     */
    private $httpContext;

    /**
     * @var Email
     */
    private $emailHelper;

    /**
     * Index constructor.
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param ProductRepository $productRepository
     * @param RequestFactory $requestFactory
     * @param Statistic $statisticHelper
     * @param Validator $formKeyValidator
     * @param Config $configHelper
     * @param Validate $validateHelper
     * @param HttpContext $httpContext
     * @param Email $emailHelper
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ProductRepository $productRepository,
        RequestFactory $requestFactory,
        Statistic $statisticHelper,
        Validator $formKeyValidator,
        Config $configHelper,
        Validate $validateHelper,
        HttpContext $httpContext,
        Email $emailHelper
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->productRepository = $productRepository;
        $this->requestFactory = $requestFactory;
        $this->statisticHelper = $statisticHelper;
        $this->formKeyValidator = $formKeyValidator;
        $this->configHelper = $configHelper;
        $this->validateHelper = $validateHelper;
        $this->httpContext = $httpContext;
        $this->emailHelper = $emailHelper;
        parent::__construct($context);
    }

    /**
     * @return $this|\Magento\Framework\Controller\Result\Json
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();

        if (!$this->formKeyValidator->validate($this->getRequest()) || !$this->getRequest()->isAjax()) {
            $response = [
                'message' => __('Not Valid Request'),
                'type' => 'error',
            ];
            $resultJson->setData($response);

            return $resultJson;
        }

        if (!$this->validateHelper->validateCaptcha($this->getRequest())) {
            $response['message'] = __('Incorrect Captcha');
            $response['type'] = 'error';
            $resultJson->setData($response);

            return  $resultJson;
        }

        $productId = trim($this->getRequest()->getParam('product_id'));
        $email = trim($this->getRequest()->getParam('email'));
        $link = trim($this->getRequest()->getParam('link'));
        $requestPrice = trim($this->getRequest()->getParam('request_price'));
        $qty = trim($this->getRequest()->getParam('qty'));
        $storeId = trim($this->getRequest()->getParam('store_id'));
        $productPrice = trim($this->getRequest()->getParam('current_price'));
        $productOptions = $this->getRequest()->getParam('product_options', false);

        if (!$this->validateHelper->validateData($email, $link, $requestPrice, $qty, $productId)) {
            $response = [
                'message' => __('Invalid Data'),
                'type' => 'error',
            ];
            $resultJson->setData($response);

            return $resultJson;
        }

        $request = $this->requestFactory->create();
        $product = $this->productRepository->getById($productId);
        if (!$product->getId()) {
            return $this;
        }

        $productPrice = $productPrice * $qty;
        $requestPrice = $requestPrice * $qty;
        $productSku = $product->getSku();
        $requestedSaleAmount = $productPrice - $requestPrice;
        $customerId = $this->httpContext->getValue(CustomerContext::CONTEXT_CUSTOMER_ID);

        if ($customerId) {
            $request->setCustomerId($customerId);
        }

        if ($productOptions) {
            $request->setProductOptions(json_encode($productOptions));
        }

        $request->setProductId($productId);
        $request->setProductSku($productSku);
        $request->setEmail($email);
        $request->setLink($link);
        $request->setPrice($productPrice);
        $request->setRequestPrice($requestPrice);
        $request->setRequestedSaleAmount($requestedSaleAmount);
        $request->setProductQty($qty);
        $request->setStoreId($storeId);
        $request->setStatus(Status::NEW_REQUEST_STATUS);

        try {
            $request->save();

            $this->statisticHelper->addTotalRequests($productId, $productSku);

            $resultJson = $this->resultJsonFactory->create();

            $responseMessage =  $this->configHelper->getSuccessMessage($storeId);
            $response = [
                'message' => $responseMessage,
                'type' => 'success',
                ];

            if ($this->configHelper->isNotifyCustomerEnabled($storeId)) {
                $this->emailHelper->notifyCustomer($request);
            }

            if ($this->configHelper->isNotifyAdminEnabled()) {
                $this->emailHelper->notifyAdmin($request);
            }
        } catch (\Exception $exception) {
            $response = [
                'message' => 'Something went wrong. Try again later please',
                'type'    => 'error',
            ];
            $resultJson->setData($response);

            return $resultJson;
        }

        $resultJson->setData($response);

        return $resultJson;
    }
}
