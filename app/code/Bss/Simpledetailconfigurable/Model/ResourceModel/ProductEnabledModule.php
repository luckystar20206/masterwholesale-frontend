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
namespace Bss\Simpledetailconfigurable\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ProductEnabledModule extends AbstractDb
{
    public function _construct()
    {
        $this->_init('sdcp_product_enabled', 'product_id');
    }

    public function saveEnabled($productId, $enabled, $isAjax)
    {
        $connection = $this->getConnection();
        $bind = [
            'product_id' => $productId,
            'enabled' => $enabled,
            'is_ajax_load' => $isAjax
        ];
        $connection->insert($this->getMainTable(), $bind);
    }
    
    public function deleteOldKey($productId)
    {
        $this->getConnection()->delete($this->getMainTable(), ['product_id=?' => $productId]);
    }
}
