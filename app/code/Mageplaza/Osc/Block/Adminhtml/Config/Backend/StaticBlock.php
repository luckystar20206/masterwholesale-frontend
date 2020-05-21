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

namespace Mageplaza\Osc\Block\Adminhtml\Config\Backend;

use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Cms\Model\ResourceModel\Block\CollectionFactory;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\Data\Form\Element\Factory;
use Mageplaza\Osc\Model\System\Config\Source\StaticBlockPosition;

/**
 * Class StaticBlock
 * @package Mageplaza\AbandonedCart\Block\Adminhtml\Config\Backend
 */
class StaticBlock extends AbstractFieldArray
{
    /**
     * @var Factory
     */
    protected $elementFactory;

    /**
     * @var StaticBlockPosition
     */
    protected $blockPosition;

    /**
     * @var CollectionFactory
     */
    protected $blockFactory;

    /**
     * StaticBlock constructor.
     *
     * @param Context $context
     * @param Factory $elementFactory
     * @param StaticBlockPosition $blockPosition
     * @param CollectionFactory $blockFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Factory $elementFactory,
        StaticBlockPosition $blockPosition,
        CollectionFactory $blockFactory,
        array $data = []
    ) {
        $this->elementFactory = $elementFactory;
        $this->blockPosition = $blockPosition;
        $this->blockFactory = $blockFactory;

        parent::__construct($context, $data);
    }

    /**
     * Initialise form fields
     *
     * @return void
     */
    public function _construct()
    {
        $this->addColumn('block', ['label' => __('Block')]);
        $this->addColumn('position', ['label' => __('Position')]);
        $this->addColumn('sort_order', ['label' => __('Sort Order'), 'style' => 'width: 100px']);

        $this->_addAfter = false;
        $this->_addButtonLabel = __('More');

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
                case 'block':
                    $options = $this->blockFactory->create()->toOptionArray();
                    break;
                case 'position':
                    $options = $this->blockPosition->toOptionArray();
                    break;
                default:
                    $options = '';
                    break;
            }
            if ($options) {
                foreach ($options as $index => &$item) {
                    if (is_array($item) && isset($item['label'])) {
                        $item['label'] = $this->escapeHtml($item['label']);
                    }
                }

                unset($item);

                $element = $this->elementFactory->create('select');
                $element->setForm($this->getForm())
                    ->setName($this->_getCellInputElementName($columnName))
                    ->setHtmlId($this->_getCellInputElementId('<%- _id %>', $columnName))
                    ->setValues($options)
                    ->setStyle('width: 200px');

                return str_replace("\n", '', $element->getElementHtml());
            }
        }

        return parent::renderCellTemplate($columnName);
    }
}
