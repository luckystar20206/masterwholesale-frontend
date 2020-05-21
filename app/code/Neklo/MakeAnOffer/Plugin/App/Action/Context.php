<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


namespace Neklo\MakeAnOffer\Plugin\App\Action;

use Neklo\MakeAnOffer\Model\Customer\Context as CustomerSessionContext;
use Magento\Framework\App\ObjectManager;

class Context
{
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    public $httpContext;

    /**
     * Context constructor.
     * @param \Magento\Framework\App\Http\Context $httpContext
     */
    public function __construct(
        \Magento\Framework\App\Http\Context $httpContext
    ) {
        $this->httpContext = $httpContext;
    }

    /**
     * @param \Magento\Framework\App\ActionInterface $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\App\RequestInterface $request
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundDispatch(
        \Magento\Framework\App\ActionInterface $subject,
        \Closure $proceed,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $customerSession = ObjectManager::getInstance()->get(\Magento\Customer\Model\Session::class);
        $customerId = $customerSession->getCustomerId();
        $customerEmail = $customerSession->getCustomer()->getEmail();
        if (!$customerId) {
            $customerId = 0;
            $customerEmail = 0;
        }

        $this->httpContext->setValue(
            CustomerSessionContext::CONTEXT_CUSTOMER_ID,
            $customerId,
            false
        );
        $this->httpContext->setValue(
            CustomerSessionContext::CONTEXT_CUSTOMER_EMAIL,
            $customerEmail,
            false
        );

        return $proceed($request);
    }
}
