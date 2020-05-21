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
namespace Bss\Simpledetailconfigurable\Observer;

use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Event\Observer as EventObserver;

class ProductSavingOpserver implements ObserverInterface
{
    private $additionalInfoSaving;

    private $moduleConfig;

    public function __construct(
        \Bss\Simpledetailconfigurable\Helper\AdditionalInfoSaving $additionalInfoSaving,
        \Bss\Simpledetailconfigurable\Helper\ModuleConfig $moduleConfig
    ) {
        $this->moduleConfig = $moduleConfig;
        $this->additionalInfoSaving = $additionalInfoSaving;
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $postData = $observer->getData('controller')->getRequest()->getPost('product');
        $productId = ($observer->getData('product')) ? $observer->getData('product')->getEntityId()
        : $postData['sdcp_preselect_id'];

        if ($this->moduleConfig->isModuleEnable() && array_key_exists('sdcp_preselect', $postData)) {
            $this->additionalInfoSaving->savePreselectKey($postData, $productId);
        }

        if ($this->moduleConfig->isModuleEnable() && array_key_exists('sdcp_general', $postData)) {
            $this->additionalInfoSaving->saveEnabledModuleOnProduct($productId, $postData['sdcp_general']);
        }
    }
}
