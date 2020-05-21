<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * BSS Commerce does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BSS Commerce does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   BSS
 * @package    Bss_Simpledetailconfigurable
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2016 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Simpledetailconfigurable\Block\Adminhtml\Product;

/**
 * Product inventory data
 */
class PreselectTab extends \Magento\Backend\Block\Widget\Tab
{
    private $moduleConfig;

    private $linkData;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Bss\Simpledetailconfigurable\Helper\ModuleConfig $moduleConfig,
        \Bss\Simpledetailconfigurable\Helper\ProductData $linkData,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->moduleConfig = $moduleConfig;
        $this->linkData = $linkData;
    }

    public function getSelectedOption($attr, $value)
    {
        $data = $this->linkData->getSelectingData($this->_request->getParam('id'));
        if (array_key_exists($attr, $data) && $data[$attr] == $value) {
            return 'selected="selected"';
        } else {
            return '';
        }
    }
    
    public function getHtmlPreselectField()
    {
        $html = '';
        if ($this->_request->getParam('id')) {
            $attributes = $this->linkData->getSelectingKey($this->_request->getParam('id'));
            $html .= '<input type="hidden" name="product[sdcp_preselect_id]" value="'
            . $this->_request->getParam('id')
            . '">';
            foreach ($attributes as $attr => $child) {
                $html .= '<div class="field"><label class="label" for="'
                . $attr
                . '"><span>'
                . $attr
                . '</span></label><div class="control"><select id="'
                . $attr
                . '" name="product[sdcp_preselect]['
                . $attr
                . ']">';
                $html .= '<option value="">Not Selected</option>';
                foreach ($child['child'] as $key => $value) {
                    $html .= '<option value="'
                    . $value
                    . '" '
                    . $this->getSelectedOption($attr, $value)
                    . '>'
                    . $value
                    . '</option>';
                }
                $html .= '</select></div><div class="field-service">[GLOBAL]</div></div>';
            }
        }
        return $html;
    }
}
