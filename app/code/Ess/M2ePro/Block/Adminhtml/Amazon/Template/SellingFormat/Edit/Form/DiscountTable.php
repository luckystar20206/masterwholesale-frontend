<?php

/**
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Any usage is forbidden
 */

namespace Ess\M2ePro\Block\Adminhtml\Amazon\Template\SellingFormat\Edit\Form;

/**
 * Class DiscountTable
 * @package Ess\M2ePro\Block\Adminhtml\Amazon\Template\SellingFormat\Edit\Form
 */
class DiscountTable extends \Ess\M2ePro\Block\Adminhtml\Magento\AbstractBlock
{
    protected $_template = 'amazon/template/selling_format/discount_table.phtml';

    //########################################

    public function getAttributes()
    {
        return $this->getData('attributes');
    }

    //########################################
}
