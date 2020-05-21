<?php
namespace ModernRetail\TotalReport\Block\Adminhtml;

class AllocatedReport  extends \Magento\Backend\Block\Widget\Grid\Container{

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data
    ) {
        parent::__construct($context, $data);
        $this->_blockGroup = '\ModernRetail\TotalReport';
        $this->_controller = 'adminhtml_allocatedReport';
    }
}