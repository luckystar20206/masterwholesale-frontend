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

namespace Mageplaza\LayeredNavigation\Model\Layer;

use Magento\Framework\App\RequestInterface;
use Mageplaza\LayeredNavigation\Helper\Data as LayerHelper;

/**
 * Class Filter
 * @package Mageplaza\LayeredNavigation\Model\Layer
 */
class Filter
{
    /** @var \Magento\Framework\App\RequestInterface */
    protected $request;

    /** @var array Slider types */
    protected $sliderTypes = [LayerHelper::FILTER_TYPE_SLIDER];

    /**
     * Filter constructor.
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * Layered configuration for js widget
     *
     * @param \Magento\Catalog\Model\Layer\Filter\AbstractFilter $filters
     * @param $config
     * @return mixed
     */
    public function getLayerConfiguration($filters, $config)
    {
        $slider = [];
        foreach ($filters as $filter) {
            if ($this->getIsSliderTypes($filter) && $filter->getItemsCount()) {
                $slider[$filter->getRequestVar()] = $filter->getSliderConfig();
            }
        }
        $config->setData('slider', $slider);

        return $this;
    }

    /**
     * @param $filter
     * @param null $types
     * @return bool
     */
    public function getIsSliderTypes($filter, $types = null)
    {
        $filterType = $this->getFilterType($filter);
        $types      = $types ?: $this->sliderTypes;

        return in_array($filterType, $types);
    }

    /**
     * @param \Magento\Catalog\Model\Layer\Filter\AbstractFilter $filter
     * @param null $compareType
     * @return bool|string
     */
    public function getFilterType($filter, $compareType = null)
    {
        $type = LayerHelper::FILTER_TYPE_LIST;
        if ($filter->getRequestVar() == 'price') {
            $type = LayerHelper::FILTER_TYPE_SLIDER;
        }

        return $compareType ? ($type == $compareType) : $type;
    }

    /**
     * Get option url. If it has been filtered, return removed url. Else return filter url
     *
     * @param \Magento\Catalog\Model\Layer\Filter\Item $item
     * @return mixed
     */
    public function getItemUrl($item)
    {
        if ($this->isSelected($item)) {
            return $item->getRemoveUrl();
        }

        return $item->getUrl();
    }

    /**
     * Check if option is selected or not
     *
     * @param $item
     * @return bool
     */
    public function isSelected($item)
    {
        $filterValue = $this->getFilterValue($item->getFilter());
        if (!empty($filterValue) && in_array($item->getValue(), $filterValue)) {
            return true;
        }

        return false;
    }

    /**
     * @param \Magento\Catalog\Model\Layer\Filter\AbstractFilter $filter
     * @param bool|true $explode
     * @return array|mixed
     */
    public function getFilterValue($filter, $explode = true)
    {
        $filterValue = $this->request->getParam($filter->getRequestVar());
        if (empty($filterValue)) {
            return [];
        }

        return $explode ? explode(',', $filterValue) : $filterValue;
    }

    /**
     * Allow to show counter after options
     *
     * @param \Magento\Catalog\Model\Layer\Filter\AbstractFilter $filter
     * @return bool
     */
    public function isShowCounter($filter)
    {
        return true;
    }

    /**
     * Allow multiple filter
     *
     * @param \Magento\Catalog\Model\Layer\Filter\AbstractFilter $filter
     * @return bool
     */
    public function isMultiple($filter)
    {
        if ($this->getIsSliderTypes($filter) || $filter->getRequestVar() == 'price') {
            return false;
        }

        return true;
    }

    /**
     * Checks whether the option reduces the number of results
     *
     * @param \Magento\Catalog\Model\Layer\Filter\AbstractFilter $filter
     * @param int $optionCount Count of search results with this option
     * @param int $totalSize Current search results count
     * @return bool
     */
    public function isOptionReducesResults($filter, $optionCount, $totalSize)
    {
        $result = $optionCount <= $totalSize;

        if ($this->isShowZero($filter)) {
            return $result;
        }

        return $optionCount && $result;
    }

    /**
     * @param \Magento\Catalog\Model\Layer\Filter\AbstractFilter $filter
     * @return bool
     */
    public function isShowZero($filter)
    {
        return false;
    }
}
