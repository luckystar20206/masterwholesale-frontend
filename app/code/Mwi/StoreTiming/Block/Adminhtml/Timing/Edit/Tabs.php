<?php
namespace Mwi\StoreTiming\Block\Adminhtml\Timing\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected function _construct()
    {
		
        parent::_construct();
        $this->setId('checkmodule_timing_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Timing Information'));
    }
}