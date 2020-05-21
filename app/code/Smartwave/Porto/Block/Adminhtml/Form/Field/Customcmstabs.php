<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Adminhtml catalog inventory "Minimum Qty Allowed in Shopping Cart" field
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Smartwave\Porto\Block\Adminhtml\Form\Field;

class Customcmstabs extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn('tab_title', ['label' => __('Title')]);
        $this->addColumn('staticblock_id', ['label' => __('StaticBlock')]);
        $this->addColumn('category_ids', ['label' => __('Categories')]);
        $this->addColumn('product_skus', ['label' => __('Products')]);
        $this->addColumn('sort_order', ['label' => __('Order')]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Tab');
    }
}
