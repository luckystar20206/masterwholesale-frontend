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
namespace Bss\Simpledetailconfigurable\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ProductOptions\ConfigInterface;
use Magento\Catalog\Model\Config\Source\Product\Options\Price as ProductOptionsPrice;
use Magento\Framework\UrlInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Ui\Component\Modal;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\HtmlContent;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Element\Checkbox;
use Magento\Ui\Component\Form\Element\ActionDelete;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\DataType\Boolean;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Ui\Component\Form\Element\DataType\Media;

/**
 * Data provider for Configurable panel
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BssSDCPTab extends AbstractModifier
{
    private $locator;

    private $moduleConfig;

    private $linkData;

    public function __construct(
        LocatorInterface $locator,
        \Bss\Simpledetailconfigurable\Helper\ModuleConfig $moduleConfig,
        \Bss\Simpledetailconfigurable\Helper\ProductData $linkData
    ) {
        $this->locator = $locator;
        $this->linkData = $linkData;
        $this->moduleConfig = $moduleConfig;
    }

    public function modifyData(array $data)
    {
        $productId = $this->locator->getProduct()->getId();
        if ($productId != null && $this->moduleConfig->isModuleEnable()) {
            if ($this->moduleConfig->preselectConfig() && $this->linkData->getSelectingData($productId)) {
                $data = array_replace_recursive(
                    $data,
                    [
                        $productId => [
                            'product' => [
                                'sdcp_preselect' => $this->linkData->getSelectingData($productId)
                            ]
                        ]
                    ]
                );
            }
            if ($this->linkData->getEnabledModuleOnProduct($productId)) {
                $data = array_replace_recursive(
                    $data,
                    [
                        $productId => [
                            'product' => [
                                'sdcp_general' => $this->linkData->getEnabledModuleOnProduct($productId)->getData()
                            ]
                        ]
                    ]
                );
            }
        }
        return $data;
    }

    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;
        if ($this->locator->getProduct()->getId() != null &&
            $this->locator->getProduct()->getTypeId() == 'configurable') {
            if ($this->moduleConfig->isModuleEnable()) {
                if ($this->moduleConfig->preselectConfig()) {
                    $this->createCustomOptionsPanel();
                }
                $this->createEnabledPanel();
            }
        }
        return $this->meta;
    }

    public function createCustomOptionsPanel()
    {
        $this->meta = array_replace_recursive(
            $this->meta,
            [
                'SDCP_preselect' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('SDCP Preselect'),
                                'componentType' => Fieldset::NAME,
                                'dataScope' => 'data.product.sdcp_preselect',
                                'collapsible' => true,
                                'sortOrder' => 10,
                            ],
                        ],
                    ],
                    'children' => $this->getConfigAttributesSelect(),
                ]
            ]
        );
        return $this;
    }
    public function createEnabledPanel()
    {
        $this->meta = array_replace_recursive(
            $this->meta,
            [
                'SDCP_enabled' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('SDCP General'),
                                'componentType' => Fieldset::NAME,
                                'dataScope' => 'data.product.sdcp_general',
                                'collapsible' => true,
                                'sortOrder' => 5,
                            ],
                        ],
                    ],
                    'children' => $this->getEnabledField(),
                ]
            ]
        );
        return $this;
    }

    public function getEnabledField()
    {
        $result = [];
        $productId = $this->locator->getProduct()->getId();
        if ($productId != null) {
            $result = [
                'enabled' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Enabled Module'),
                                'componentType' => Field::NAME,
                                'formElement' => Select::NAME,
                                'dataScope' => 'enabled',
                                'dataType' => Text::NAME,
                                'sortOrder' => 10,
                                'options' => [
                                    ['value' => '0', 'label' => __('No')],
                                    ['value' => '1', 'label' => __('Yes')]
                                ],
                            ],
                        ],
                    ],
                ],
                'is_ajax_load' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Enabled Ajax Load Detail'),
                                'componentType' => Field::NAME,
                                'formElement' => Select::NAME,
                                'dataScope' => 'is_ajax_load',
                                'dataType' => Text::NAME,
                                'sortOrder' => 20,
                                'options' => [
                                    ['value' => '0', 'label' => __('No')],
                                    ['value' => '1', 'label' => __('Yes')]
                                ],
                            ],
                        ],
                    ],
                ],

            ];
        }
        return $result;
    }
    public function getConfigAttributesSelect()
    {
        $result = [];
        $sortOrder = 10;
        $productId = $this->locator->getProduct()->getId();
        if ($productId != null) {
            foreach ($this->linkData->getSelectingKey($productId) as $key => $value) {
                $result[$key] = [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __($value['label']),
                                'componentType' => Field::NAME,
                                'formElement' => Select::NAME,
                                'dataScope' => $key,
                                'dataType' => Text::NAME,
                                'sortOrder' => $sortOrder,
                                'options' => $this->getOptions($value['child']),
                            ],
                        ],
                    ],
                ];
                $sortOrder += 10;
            }
        }
        return $result;
    }
    
    public function getOptions($values)
    {
        $result = [];
        $result[0] = ['value' => '', 'label' => __('Not Selected')];
        foreach ($values as $key => $value) {
            $result[] = ['value' => $key, 'label' => __($value)];
        }
        return $result;
    }
}
