<?php
namespace Smartwave\Dailydeals\Block\Adminhtml\Dailydeal;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * constructor
     *
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
    
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize Dailydeal edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'dailydeal_id';
        $this->_blockGroup = 'Smartwave_Dailydeals';
        $this->_controller = 'adminhtml_dailydeal';
        parent::_construct();
        $this->buttonList->update('save', 'label', __('Save Dailydeal'));
        $this->buttonList->add(
            'save-and-continue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'saveAndContinueEdit',
                            'target' => '#edit_form'
                        ]
                    ]
                ]
            ],
            -100
        );
        $this->buttonList->update('delete', 'label', __('Delete Dailydeal'));
    }
    /**
     * Retrieve text for header element depending on loaded Dailydeal
     *
     * @return string
     */
    public function getHeaderText()
    {
        /** @var \Smartwave\Dailydeals\Model\Dailydeal $dailydeal */
        $dailydeal = $this->coreRegistry->registry('sw_dailydeals_dailydeal');
        if ($dailydeal->getId()) {
            return __("Edit Dailydeal '%1'", $this->escapeHtml($dailydeal->getSw_product_sku()));
        }
        return __('New Dailydeal');
    }
}
