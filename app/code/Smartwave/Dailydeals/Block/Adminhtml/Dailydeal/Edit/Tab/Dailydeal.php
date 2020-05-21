<?php
namespace Smartwave\Dailydeals\Block\Adminhtml\Dailydeal\Edit\Tab;

class Dailydeal extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Country options
     *
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $booleanOptions;

    /**
     * Discount Type options
     *
     * @var \Smartwave\Dailydeals\Model\Dailydeal\Source\SwDiscountType
     */
    protected $swDiscountTypeOptions;

    protected $swDealProductOptions;
    /**
     * constructor
     *
     * @param \Magento\Config\Model\Config\Source\Yesno $booleanOptions
     * @param \Smartwave\Dailydeals\Model\Dailydeal\Source\SwDiscountType $swDiscountTypeOptions
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Config\Model\Config\Source\Yesno $booleanOptions,
        \Smartwave\Dailydeals\Model\Dailydeal\Source\SwDiscountType $swDiscountTypeOptions,
        \Smartwave\Dailydeals\Model\Dailydeal\Source\SwDealProduct $swDealProductOptions,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
    
        $this->booleanOptions         = $booleanOptions;
        $this->swDiscountTypeOptions = $swDiscountTypeOptions;
        $this->swDealProductOptions = $swDealProductOptions;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Smartwave\Dailydeals\Model\Dailydeal $dailydeal */
        $dailydeal = $this->_coreRegistry->registry('sw_dailydeals_dailydeal');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('dailydeal_');
        $form->setFieldNameSuffix('dailydeal');
        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Dailydeal Information'),
                'class'  => 'fieldset-wide'
            ]
        );
        if ($dailydeal->getId()) {
            $fieldset->addField(
                'dailydeal_id',
                'hidden',
                ['name' => 'dailydeal_id']
            );
        }
        $fieldset->addField(
            'sw_product_sku',
            'select',
            [
                'name'  => 'sw_product_sku',
                'label' => __('Product Sku'),
                'title' => __('Product Sku'),
                'onchange' => 'checkSelectedItem(this.value)',
                'values' => array_merge(['' => ''], $this->swDealProductOptions->toOptionArray()),
            ]
        );

        $fieldset->addField(
            'sw_deal_enable',
            'select',
            [
                'name'  => 'sw_deal_enable',
                'label' => __('Enable Deal'),
                'title' => __('Enable Deal'),
                'values' => $this->booleanOptions->toOptionArray(),
            ]
        );
        $fieldset->addField(
            'sw_discount_type',
            'select',
            [
                'name'  => 'sw_discount_type',
                'label' => __('Discount Type'),
                'title' => __('Discount Type'),
                'values' => array_merge(['' => ''], $this->swDiscountTypeOptions->toOptionArray()),
            ]
        );
        $fieldset->addField(
            'sw_discount_amount',
            'text',
            [
                'name'  => 'sw_discount_amount',
                'label' => __('Discount Value'),
                'title' => __('Discount Value'),
            ]
        );
        $fieldset->addField(
            'sw_date_from',
            'date',
            [
                'name'  => 'sw_date_from',
                'label' => __('Date From'),
                'title' => __('Date From'),
                'date_format' => $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT),
                'time_format' => $this->_localeDate->getTimeFormat(\IntlDateFormatter::SHORT),
        
                'class' => 'validate-date',
            ]
        );
        $fieldset->addField(
            'sw_date_to',
            'date',
            [
                'name'  => 'sw_date_to',
                'label' => __('Date To'),
                'title' => __('Date To'),
                'date_format' => $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT),
                'time_format' => $this->_localeDate->getTimeFormat(\IntlDateFormatter::SHORT),
                // 'time_format' => 'hh:mm:ss a',
                'class' => 'validate-date',
            ]
        );

        $dailydealData = $this->_session->getData('sw_dailydeals_dailydeal_data', true);
        if ($dailydealData) {
            $dailydeal->addData($dailydealData);
        } else {
            if (!$dailydeal->getId()) {
                $dailydeal->addData($dailydeal->getDefaultValues());
            }
        }
        $form->addValues($dailydeal->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Dailydeal');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}
