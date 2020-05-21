<?php
namespace Mwi\StoreTiming\Block\Adminhtml;
class Timing extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
		
        $this->_controller = 'adminhtml_timing';/*block grid.php directory*/
        $this->_blockGroup = 'Mwi_StoreTiming';
        $this->_headerText = __('Timing');
        $this->_addButtonLabel = __('Add New Entry'); 
        parent::_construct();
		
    }
}
