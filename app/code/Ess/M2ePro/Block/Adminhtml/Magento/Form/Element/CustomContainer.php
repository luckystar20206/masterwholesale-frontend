<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Block\Adminhtml\Magento\Form\Element;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Escaper;

/**
 * Class CustomContainer
 * @package Ess\M2ePro\Block\Adminhtml\Magento\Form\Element
 */
class CustomContainer extends AbstractElement
{
    /**
     * @param \Magento\Framework\Data\Form\Element\Factory $factoryElement
     * @param \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        Escaper $escaper,
        $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->setType('custom_container');
    }

    /**
     * @return string
     */
    public function getElementHtml()
    {
        $html = $this->getBeforeElementHtml()
            . '<div id="'
            . $this->getHtmlId()
            . '" '.$this->getClass().
            $this->serialize(
                $this->getHtmlAttributes()
            )
            .'>'
            . $this->getText()
            . '</div>'
            . $this->getAfterElementHtml();
        return $html;
    }

    public function getHtmlAttributes()
    {
        return array_diff(parent::getHtmlAttributes(), ['class']);
    }

    protected function getClass()
    {
        $cssClass = ' class="control-value admin__field-value ';

        if (isset($this->_data['container_class'])) {
            return $cssClass . $this->_data['container_class'].'" ';
        }

        return $cssClass . '" ';
    }
}
