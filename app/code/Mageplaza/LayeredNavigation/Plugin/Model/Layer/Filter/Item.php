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

namespace Mageplaza\LayeredNavigation\Plugin\Model\Layer\Filter;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Theme\Block\Html\Pager;
use Mageplaza\LayeredNavigation\Helper\Data as LayerHelper;

/**
 * Class Item
 * @package Mageplaza\LayeredNavigation\Model\Plugin\Layer\Filter
 */
class Item
{
    /** @var UrlInterface */
    protected $_url;

    /** @var Pager */
    protected $_htmlPagerBlock;

    /** @var RequestInterface */
    protected $_request;

    /** @var \Mageplaza\LayeredNavigation\Helper\Data */
    protected $_moduleHelper;

    /**
     * Item constructor.
     *
     * @param UrlInterface $url
     * @param Pager $htmlPagerBlock
     * @param RequestInterface $request
     * @param \Mageplaza\LayeredNavigation\Helper\Data $moduleHelper
     */
    public function __construct(
        UrlInterface $url,
        Pager $htmlPagerBlock,
        RequestInterface $request,
        LayerHelper $moduleHelper
    )
    {
        $this->_url            = $url;
        $this->_htmlPagerBlock = $htmlPagerBlock;
        $this->_request        = $request;
        $this->_moduleHelper   = $moduleHelper;
    }

    /**
     * @param \Magento\Catalog\Model\Layer\Filter\Item $item
     * @param $proceed
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundGetUrl(\Magento\Catalog\Model\Layer\Filter\Item $item, $proceed)
    {
        if (!$this->_moduleHelper->isEnabled()) {
            return $proceed();
        }

        $value       = [];
        $filter      = $item->getFilter();
        $filterModel = $this->_moduleHelper->getFilterModel();
        if ($filterModel->getIsSliderTypes($filter) || $filter->getData('range_mode')) {
            $value = ["from-to"];
        } else if ($filterModel->isMultiple($filter)) {
            $requestVar = $filter->getRequestVar();
            if ($requestValue = $this->_request->getParam($requestVar)) {
                $value = explode(',', $requestValue);
            }
            if (!in_array($item->getValue(), $value)) {
                $value[] = $item->getValue();
            }
        }

        //Sort param on Url
        sort($value);

        if (sizeof($value)) {
            $query = [
                $filter->getRequestVar()                 => implode(',', $value),
                // exclude current page from urls
                $this->_htmlPagerBlock->getPageVarName() => null,
            ];

            return $this->_url->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true, '_query' => $query]);
        }

        return $proceed();
    }

    /**
     * @param \Magento\Catalog\Model\Layer\Filter\Item $item
     * @param $proceed
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundGetRemoveUrl(\Magento\Catalog\Model\Layer\Filter\Item $item, $proceed)
    {
        if (!$this->_moduleHelper->isEnabled()) {
            return $proceed();
        }

        $value       = [];
        $filter      = $item->getFilter();
        $filterModel = $this->_moduleHelper->getFilterModel();
        if ($filterModel->isMultiple($filter)) {
            $value = $filterModel->getFilterValue($filter);
            if (in_array($item->getValue(), $value)) {
                $value = array_diff($value, [$item->getValue()]);
            }
        }

        $params['_query']       = [$filter->getRequestVar() => count($value) ? implode(',', $value) : $filter->getResetValue()];
        $params['_current']     = true;
        $params['_use_rewrite'] = true;
        $params['_escape']      = true;

        return $this->_url->getUrl('*/*/*', $params);
    }
}
