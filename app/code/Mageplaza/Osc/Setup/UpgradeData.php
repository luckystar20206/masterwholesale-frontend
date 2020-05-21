<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Osc
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Osc\Setup;

use Exception;
use Magento\Cms\Model\BlockFactory;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Customer\Api\AddressMetadataInterface as AddressInterface;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\Backend\Datetime;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Quote\Setup\QuoteSetup;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Setup\SalesSetup;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreRepository;
use Mageplaza\Osc\Helper\Data as OscHelper;
use Psr\Log\LoggerInterface;
use Zend_Serializer_Exception;
use Zend_Validate_Exception;

/**
 * Class UpgradeData
 * @package Mageplaza\Osc\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var QuoteSetupFactory
     */
    protected $quoteSetupFactory;

    /**
     * @var SalesSetupFactory
     */
    protected $salesSetupFactory;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var OscHelper
     */
    protected $oscHelper;

    /**
     * @var Config
     */
    protected $resourceConfig;

    /**
     * @var StoreRepository
     */
    protected $storeRepository;

    /**
     * @var BlockFactory
     */
    protected $blockFactory;

    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * UpgradeData constructor.
     *
     * @param QuoteSetupFactory $quoteSetupFactory
     * @param SalesSetupFactory $salesSetupFactory
     * @param Filesystem $filesystem
     * @param LoggerInterface $logger
     * @param OscHelper $oscHelper
     * @param Config $resourceConfig
     * @param StoreRepository $storeRepository
     * @param BlockFactory $blockFactory
     * @param CustomerSetupFactory $customerSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     */
    public function __construct(
        QuoteSetupFactory $quoteSetupFactory,
        SalesSetupFactory $salesSetupFactory,
        Filesystem $filesystem,
        LoggerInterface $logger,
        OscHelper $oscHelper,
        Config $resourceConfig,
        StoreRepository $storeRepository,
        BlockFactory $blockFactory,
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->quoteSetupFactory    = $quoteSetupFactory;
        $this->salesSetupFactory    = $salesSetupFactory;
        $this->fileSystem           = $filesystem;
        $this->logger               = $logger;
        $this->oscHelper            = $oscHelper;
        $this->resourceConfig       = $resourceConfig;
        $this->storeRepository      = $storeRepository;
        $this->blockFactory         = $blockFactory;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory  = $attributeSetFactory;
    }

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     * @throws Zend_Validate_Exception
     * @throws Exception
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var QuoteSetup $quoteInstaller */
        $quoteInstaller = $this->quoteSetupFactory->create(['resourceName' => 'quote_setup', 'setup' => $setup]);

        /** @var SalesSetup $salesInstaller */
        $salesInstaller = $this->salesSetupFactory->create(['resourceName' => 'sales_setup', 'setup' => $setup]);

        $setup->startSetup();
        if (version_compare($context->getVersion(), '2.1.0') < 0) {
            $entityAttributesCodes = [
                'osc_gift_wrap_amount'      => Table::TYPE_DECIMAL,
                'base_osc_gift_wrap_amount' => Table::TYPE_DECIMAL
            ];
            foreach ($entityAttributesCodes as $code => $type) {
                $quoteInstaller->addAttribute('quote_address', $code, ['type' => $type, 'visible' => false]);
                $quoteInstaller->addAttribute('quote_item', $code, ['type' => $type, 'visible' => false]);
                $salesInstaller->addAttribute('order', $code, ['type' => $type, 'visible' => false]);
                $salesInstaller->addAttribute('order_item', $code, ['type' => $type, 'visible' => false]);
                $salesInstaller->addAttribute('invoice', $code, ['type' => $type, 'visible' => false]);
                $salesInstaller->addAttribute('creditmemo', $code, ['type' => $type, 'visible' => false]);
            }

            $quoteInstaller->addAttribute('quote_address', 'used_gift_wrap', [
                'type'    => Table::TYPE_BOOLEAN,
                'visible' => false
            ]);
            $quoteInstaller->addAttribute('quote_address', 'gift_wrap_type', [
                'type'    => Table::TYPE_SMALLINT,
                'visible' => false
            ]);
            $salesInstaller->addAttribute('order', 'gift_wrap_type', [
                'type'    => Table::TYPE_SMALLINT,
                'visible' => false
            ]);
        }

        if (version_compare($context->getVersion(), '2.1.1') < 0) {
            $salesInstaller->addAttribute('order', 'osc_delivery_time', [
                'type'    => Table::TYPE_TEXT,
                'visible' => false
            ]);
        }

        if (version_compare($context->getVersion(), '2.1.2') < 0) {
            $salesInstaller->addAttribute('order', 'osc_survey_question', [
                'type'    => Table::TYPE_TEXT,
                'visible' => false
            ]);
            $salesInstaller->addAttribute('order', 'osc_survey_answers', [
                'type'    => Table::TYPE_TEXT,
                'visible' => false
            ]);
        }

        if (version_compare($context->getVersion(), '2.1.3') < 0) {
            $salesInstaller->addAttribute('order', 'osc_order_house_security_code', [
                'type'    => Table::TYPE_TEXT,
                'visible' => false
            ]);
        }

        if (version_compare($context->getVersion(), '2.1.4') < 0) {
            $this->insertBlock();
        }

        if (version_compare($context->getVersion(), '2.1.5') < 0) {
            $this->updateSealBlock($setup);
            $this->copyDefaultSeal();
        }

        if (version_compare($context->getVersion(), '2.1.6') < 0) {
            $this->insertCustomAttr($setup, $quoteInstaller, $salesInstaller);
        }

        if (version_compare($context->getVersion(), '2.1.7') < 0) {
            /** @var CustomerSetup $customerSetup */
            $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

            $customerSetup->updateAttribute(AddressInterface::ATTRIBUTE_SET_ID_ADDRESS, 'mposc_field_3', [
                'validate_rules' => $this->oscHelper->serialize(['input_validation' => 'date'])
            ]);
        }

        if (version_compare($context->getVersion(), '2.1.8') < 0) {
            /** @var CustomerSetup $customerSetup */
            $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

            $attributes = [
                'mposc_field_1' => 'varchar',
                'mposc_field_2' => 'varchar',
                'mposc_field_3' => 'datetime',
            ];
            foreach ($attributes as $attribute => $type) {
                $customerSetup->updateAttribute(AddressInterface::ATTRIBUTE_SET_ID_ADDRESS, $attribute, [
                    'backend_type' => $type
                ]);
            }
        }

        $setup->endSetup();
    }

    /**
     * @return $this
     * @throws Exception
     */
    private function insertBlock()
    {
        $block = $this->getSealBlockData();

        $cmsBlock = $this->blockFactory->create()->load($block['identifier'], 'identifier');
        if (!$cmsBlock->getId()) {
            $cmsBlock->setData($block)->save();
        }

        return $this;
    }

    /**
     * @return array
     */
    private function getSealBlockData()
    {
        $sealContent = '
            <div class="osc-trust-seals" style="text-align: center;">
                <div class="trust-seals-badges">
                    <a href="https://en.wikipedia.org/wiki/Trust_seal" target="_blank">
                        <img src="{{view url=Mageplaza_Osc/css/images/seal.png}}">
                    </a>
                </div>
                <div class="trust-seals-text">
                    <p>This is a demonstration of trust badge. Please contact your SSL or Security provider to have trust badges embed properly</p>
                </div>
            </div>';

        return [
            'title'      => __('One Step Checkout Seal Content'),
            'identifier' => 'osc-seal-content',
            'content'    => $sealContent,
            'stores'     => [Store::DEFAULT_STORE_ID],
            'is_active'  => 1
        ];
    }

    /**
     * @param ModuleDataSetupInterface $setup
     *
     * @throws LocalizedException
     * @throws Zend_Serializer_Exception
     */
    private function updateSealBlock($setup)
    {
        $stores = $this->storeRepository->getList();
        foreach ($stores as $store) {
            $storeId = $store->getId();
            if ($this->oscHelper->isEnableStaticBlock($storeId)) {
                continue;
            }

            $config = $this->oscHelper->getStaticBlockList($storeId);
            if ($config && is_array($config)) {
                foreach ($config as $key => $row) {
                    if ((int ) $row['position'] === 4) {
                        if (!isset($blockId)) {
                            $blockId = $row['block'];
                        }
                        unset($config[$key]);
                    }
                }

                $data = [
                    'osc/display_configuration/seal_block/is_enabled_seal_block' => 1,
                    'osc/block_configuration/list'                               => $this->oscHelper->serialize($config)
                ];
                if (isset($blockId)) {
                    $data['osc/display_configuration/seal_block/seal_static_block'] = $blockId;
                }
                $this->saveConfig($setup, $data, $storeId);
            }
        }
    }

    /**
     * Save config value
     *
     * @param ModuleDataSetupInterface $setup
     * @param array $data
     * @param int $scopeId
     *
     * @return $this
     * @throws LocalizedException
     */
    private function saveConfig($setup, $data, $scopeId)
    {
        $scope = $scopeId ? 'stores' : 'default';

        $connection = $setup->getConnection();
        foreach ($data as $path => $value) {
            $select = $connection->select()->from(
                $this->resourceConfig->getMainTable()
            )
                ->where('path = ?', $path)
                ->where('scope = ?', $scope)
                ->where('scope_id = ?', $scopeId);

            $row = $connection->fetchRow($select);

            $newData = ['scope' => $scope, 'scope_id' => $scopeId, 'path' => $path, 'value' => $value];
            if ($row) {
                $col = $this->resourceConfig->getIdFieldName();

                $whereCondition = [$col . '=?' => $row[$col]];
                $connection->update($this->resourceConfig->getMainTable(), $newData, $whereCondition);
            } elseif ($scope === 'default') {
                $connection->insert($this->resourceConfig->getMainTable(), $newData);
            }
        }

        return $this;
    }

    /**
     * Copy default seal images
     */
    private function copyDefaultSeal()
    {
        try {
            $mediaDirectory = $this->fileSystem->getDirectoryWrite(DirectoryList::MEDIA);

            $mediaDirectory->create('mageplaza/osc/seal/default');
            $targetPath = $mediaDirectory->getAbsolutePath('mageplaza/osc/seal/default/seal.png');

            $DS      = DIRECTORY_SEPARATOR;
            $oriPath = dirname(__DIR__)
                . $DS . 'view' . $DS . 'base' . $DS . 'web' . $DS . 'css' . $DS . 'images' . $DS . 'seal.png';

            $mediaDirectory->getDriver()->copy($oriPath, $targetPath);
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param QuoteSetup $quoteInstaller
     * @param SalesSetup $salesInstaller
     *
     * @throws LocalizedException
     * @throws Zend_Serializer_Exception
     * @throws Exception
     */
    private function insertCustomAttr($setup, $quoteInstaller, $salesInstaller)
    {
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        $customerEntity = $customerSetup->getEavConfig()->getEntityType(AddressInterface::ENTITY_TYPE_ADDRESS);
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        $attributeSet     = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $fields = [
            'mposc_field_1' => array_merge($this->getDefaultAttr(), [
                'label' => 'Custom Field 1',
                'input' => 'text',
            ]),
            'mposc_field_2' => array_merge($this->getDefaultAttr(), [
                'label' => 'Custom Field 2',
                'input' => 'text',
            ]),
            'mposc_field_3' => array_merge($this->getDefaultAttr(), [
                'label'          => 'Custom Field 3',
                'input'          => 'date',
                'frontend'       => \Magento\Eav\Model\Entity\Attribute\Frontend\Datetime::class,
                'backend'        => Datetime::class,
                'input_filter'   => 'date',
                'validate_rules' => $this->oscHelper->serialize(['input_validation' => 'date']),
            ]),
        ];

        foreach ($fields as $code => $attr) {
            $customerSetup->addAttribute(AddressInterface::ATTRIBUTE_SET_ID_ADDRESS, $code, $attr);

            $customerSetup->getEavConfig()->getAttribute(AddressInterface::ENTITY_TYPE_ADDRESS, $code)->addData([
                'attribute_set_id'   => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms'      => [
                    'customer_register_address',
                    'customer_address_edit',
                    'adminhtml_customer_address',
                    'onestepcheckout_index_index'
                ]
            ])->save();

            $quoteInstaller->addAttribute('quote_address', $code, ['type' => $attr['input'], 'visible' => false]);
            $salesInstaller->addAttribute('order_address', $code, ['type' => $attr['input'], 'visible' => false]);
        }
    }

    /**
     * @return array
     */
    private function getDefaultAttr()
    {
        return [
            'type'                  => 'static',
            'required'              => false,
            'visible'               => true,
            'user_defined'          => true,
            'sort_order'            => 200,
            'position'              => 200,
            'system'                => false,
            'is_used_in_grid'       => true,
            'is_visible_in_grid'    => true,
            'is_filterable_in_grid' => true,
            'is_searchable_in_grid' => true
        ];
    }
}
