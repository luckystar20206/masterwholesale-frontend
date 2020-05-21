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

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Widget\Context;
use Magento\Customer\Model\Attribute;
use Mageplaza\Osc\Helper\Address;

/**
 * Class AbstractField
 * @package Mageplaza\Osc\Block\Adminhtml\Field
 */
abstract class AbstractField extends Template
{
    const BLOCK_ID = '';

    /**
     * @var string
     */
    protected $_template = 'Mageplaza_Osc::field/position.phtml';

    /**
     * @var Address
     */
    protected $helper;

    /**
     * @var Attribute[]
     */
    protected $sortedFields = [];

    /**
     * @var Attribute[]
     */
    protected $availableFields = [];

    /**
     * AbstractField constructor.
     *
     * @param Context $context
     * @param Address $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Address $helper,
        array $data = []
    ) {
        $this->helper = $helper;

        parent::__construct($context, $data);
    }

    /**
     * Retrieve the header text
     *
     * @return string
     */
    abstract public function getBlockTitle();

    /**
     * @return string
     */
    public function getBlockId()
    {
        return static::BLOCK_ID;
    }

    /**
     * @return Attribute[]
     */
    public function getSortedFields()
    {
        return $this->sortedFields;
    }

    /**
     * @return Attribute[]
     */
    public function getAvailableFields()
    {
        return $this->availableFields;
    }

    /**
     * @return Address
     */
    public function getHelperData()
    {
        return $this->helper;
    }

    /**
     * @return bool
     */
    public function isVisible()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function hasFields()
    {
        return true;
    }

    /**
     * @return string
     */
    public function getNoticeMessage()
    {
        return '';
    }
}
