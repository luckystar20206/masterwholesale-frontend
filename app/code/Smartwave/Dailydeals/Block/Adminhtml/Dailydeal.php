<?php
namespace Smartwave\Dailydeals\Block\Adminhtml;

class Dailydeal extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_dailydeal';
        $this->_blockGroup = 'Sw_Dailydeals';
        $this->_headerText = __('Dailydeals');
        $this->_addButtonLabel = __('Create New Dailydeal');
        parent::_construct();
    }
}
