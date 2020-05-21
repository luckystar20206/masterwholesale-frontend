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
 * @package     Mageplaza_LayeredNavigation
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\LayeredNavigation\Plugin\Controller\Product;

use Magento\Framework\App\RequestInterface;
use Mageplaza\LayeredNavigation\Helper\Data;

/**
 * Class CompareWishlist
 * @package Mageplaza\LayeredNavigation\Plugin\Controller\Product
 */
class CompareWishlist
{
    /** @var \Magento\Framework\App\RequestInterface */
    protected $request;

    /** @var \Mageplaza\LayeredNavigation\Helper\Data */
    protected $dataHelper;

    /**
     * Add constructor.
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Mageplaza\LayeredNavigation\Helper\Data $helperData
     */
    public function __construct(
        RequestInterface $request,
        Data $helperData
    )
    {
        $this->request    = $request;
        $this->dataHelper = $helperData;
    }

    /**
     * @param \Magento\Catalog\Controller\Product\Compare\Add|\Magento\Wishlist\Controller\Index\Add $action
     * @param $page
     * @return mixed
     */
    public function afterExecute($action, $page)
    {
        if ($this->dataHelper->isEnabled() && $this->request->isAjax()) {
            return '';
        }

        return $page;
    }
}
