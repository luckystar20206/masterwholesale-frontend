<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Adminhtml VAT ID validation block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Smartwave\Porto\Block\System\Config\Form\Button\Import;

class Demo extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Button Label
     *
     * @var string
     */
    protected $_buttonLabel = 'Import';

    protected $_actionUrl;
    
    protected $_demoVersion;
    
    private $_helper;
    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Smartwave\Porto\Helper\Data $helper
    ) {
        $this->_helper = $helper;
        
        parent::__construct($context);
    }
    /**
     * Set Button Label
     *
     * @param string $buttonLabel
     * @return \Smartwave\Porto\Block\System\Config\Form\Button\Import\Cms
     */
    public function setButtonLabel($buttonLabel)
    {
        $this->_buttonLabel = $buttonLabel;
        return $this;
    }

    /**
     * Get Action Url
     *
     * @return string
     */
    public function getActionUrl()
    {
        return $this->_actionUrl;
    }

    /**
     * Set Validate VAT Button Label
     *
     * @param string $vatButtonLabel
     * @return \Smartwave\Porto\Block\System\Config\Form\Button\Import\Cms
     */
    public function setActionUrl($actionUrl)
    {
        $this->_actionUrl = $actionUrl;
        return $this;
    }
    
    /**
     * Get Import Type
     *
     * @return string
     */
    public function getDemoVersion()
    {
        return $this->_demoVersion;
    }

    /**
     * Set Validate VAT Button Label
     *
     * @param string $vatButtonLabel
     * @return \Smartwave\Porto\Block\System\Config\Form\Button\Import\Cms
     */
    public function setDemoVersion($demoVersion)
    {
        $this->_demoVersion = $demoVersion;
        return $this;
    }
    
    /**
     * Set template to itself
     *
     * @return \Smartwave\Porto\Block\System\Config\Form\Button\Import\Cms
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('system/config/demo_button.phtml');
        }
        return $this;
    }

    /**
     * Unset some non-related element parameters
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    public function getDemos()
    {
        return  [
            ['demo_version' => 'demo01', 'label' => __('Demo 1')], 
            ['demo_version' => 'demo02', 'label' => __('Demo 2')], 
            ['demo_version' => 'demo03', 'label' => __('Demo 3')], 
            ['demo_version' => 'demo04', 'label' => __('Demo 4')], 
            ['demo_version' => 'demo05', 'label' => __('Demo 5')], 
            ['demo_version' => 'demo06', 'label' => __('Demo 6')], 
            ['demo_version' => 'demo07', 'label' => __('Demo 7')], 
            ['demo_version' => 'demo08', 'label' => __('Demo 8')], 
            ['demo_version' => 'demo09', 'label' => __('Demo 9')], 
            ['demo_version' => 'demo10', 'label' => __('Demo 10')], 
            ['demo_version' => 'demo11', 'label' => __('Demo 11')], 
            ['demo_version' => 'demo12', 'label' => __('Demo 12')], 
            ['demo_version' => 'demo13', 'label' => __('Demo 13')], 
            ['demo_version' => 'demo14', 'label' => __('Demo 14')],
            ['demo_version' => 'demo15', 'label' => __('Demo 15')],
            ['demo_version' => 'demo16', 'label' => __('Demo 16')],
            ['demo_version' => 'demo17', 'label' => __('Demo 17')],
            ['demo_version' => 'demo18', 'label' => __('Demo 18')],
            ['demo_version' => 'demo19', 'label' => __('Demo 19')],
            ['demo_version' => 'demo20', 'label' => __('Demo 20')],
            ['demo_version' => 'demo21', 'label' => __('Demo 21')],
            ['demo_version' => 'demo01_old', 'label' => __('Old - Demo 1')], 
            ['demo_version' => 'demo02_old', 'label' => __('Old - Demo 2')], 
            ['demo_version' => 'demo03_old', 'label' => __('Old - Demo 3')], 
            ['demo_version' => 'demo04_old', 'label' => __('Old - Demo 4')], 
            ['demo_version' => 'demo05_old', 'label' => __('Old - Demo 5')], 
            ['demo_version' => 'demo06_old', 'label' => __('Old - Demo 6')], 
            ['demo_version' => 'demo07_old', 'label' => __('Old - Demo 7')], 
            ['demo_version' => 'demo08_old', 'label' => __('Old - Demo 8')], 
            ['demo_version' => 'demo09_old', 'label' => __('Old - Demo 9')], 
            ['demo_version' => 'demo10_old', 'label' => __('Old - Demo 10')], 
            ['demo_version' => 'demo11_old', 'label' => __('Old - Demo 11')], 
            ['demo_version' => 'demo12_old', 'label' => __('Old - Demo 12')], 
            ['demo_version' => 'demo13_old', 'label' => __('Old - Demo 13')], 
            ['demo_version' => 'demo14_old', 'label' => __('Old - Demo 14')],
            ['demo_version' => 'demo15_old', 'label' => __('Old - Demo 15')],
            ['demo_version' => 'demo16_old', 'label' => __('Old - Demo 16')],
            ['demo_version' => 'demo17_old', 'label' => __('Old - Demo 17')],
            ['demo_version' => 'demo18_old', 'label' => __('Old - Demo 18')],
            ['demo_version' => 'demo19_old', 'label' => __('Old - Demo 19')],
            ['demo_version' => 'demo20_old', 'label' => __('Old - Demo 20')],
        ];
    }

    /**
     * Get the button and scripts contents
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $originalData = $element->getOriginalData();
        $buttonLabel = !empty($originalData['button_label']) ? $originalData['button_label'] : $this->_buttonLabel;
        $action = !empty($originalData['action_url']) ? $originalData['action_url'] : '';
        if($action) {
            $this->setActionUrl($action);
        }
        // $demo_version = !empty($originalData['demo_version']) ? $originalData['demo_version'] : '';
        // if($demo_version) {
        //     $this->setDemoVersion($demo_version);
        // }
        
        $after_html = "";
        $button_class = "";
        if(!$this->_helper->checkPurchaseCode()) {
            $button_class = "disabled";
            $after_html = '<em style="color:#f00;font-size:10px;line-height:1;">Activation is required.</em>';
        }
        $this->addData(
            [
                'button_label' => __($buttonLabel),
                //'demo_version' => $demo_version,
                'button_class' => $button_class,
                'html_id' => $element->getHtmlId(),
                'ajax_url' => $this->_urlBuilder->getUrl($action),
            ]
        );
        return $this->_toHtml().$after_html;
    }
}
