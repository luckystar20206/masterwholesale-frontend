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
 * @package     Mageplaza_DeliveryTime
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\DeliveryTime\Block\Adminhtml\Config\Backend;

use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\Data\Form\Element\Factory;

/**
 * Class DateOff
 * @package Mageplaza\DeliveryTime\Block\Adminhtml\Config\Backend
 */
class DateOff extends AbstractFieldArray
{
    /**
     * @var string
     */
    protected $_template = 'Mageplaza_DeliveryTime::system/config/form/field/date-off.phtml';

    /**
     * @var Factory
     */
    protected $elementFactory;

    /**
     * DateOff constructor.
     *
     * @param Context $context
     * @param Factory $elementFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Factory $elementFactory,
        array $data = []
    ) {
        $this->elementFactory = $elementFactory;

        parent::__construct($context, $data);
    }

    /**
     * Initialise form fields
     *
     * @return void
     */
    public function _construct()
    {
        $this->addColumn('date_off', ['label' => __('Date')]);

        $this->_addAfter = false;

        parent::_construct();
    }

    /**
     * Render array cell for prototypeJS template
     *
     * @param string $columnName
     *
     * @return mixed|string
     * @throws Exception
     */
    public function renderCellTemplate($columnName)
    {
        if (!empty($this->_columns[$columnName])) {
            switch ($columnName) {
                case 'date_off':
                    $element = $this->elementFactory->create('date');
                    $element->setForm($this->getForm())
                        ->setName($this->_getCellInputElementName($columnName))
                        ->setHtmlId($this->_getCellInputElementId('<%- _id %>', $columnName))
                        ->setFormat('dd/m/yy');

                    return str_replace("\n", '', $element->getElementHtml());
                    break;
                default:
                    break;
            }
        }

        return parent::renderCellTemplate($columnName);
    }

    /**
     * @return string
     */
    public function getAddButtonLabel()
    {
        return $this->_addButtonLabel;
    }
}
