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

namespace Mageplaza\Osc\Block\Adminhtml\Field;

/**
 * Class Address
 * @package Mageplaza\Osc\Block\Adminhtml\Field
 */
class Address extends AbstractField
{
    const BLOCK_ID = 'mposc-address-information';

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();

        /** Prepare collection */
        list($this->sortedFields, $this->availableFields) = $this->helper->getSortedField(false);
    }

    /**
     * @return string
     */
    public function getBlockTitle()
    {
        return (string) __('Address Information');
    }
}
