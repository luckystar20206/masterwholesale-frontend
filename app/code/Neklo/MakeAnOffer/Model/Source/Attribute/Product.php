<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


namespace Neklo\MakeAnOffer\Model\Source\Attribute;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory;
use Magento\Framework\DB\Ddl\Table;

class Product extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    const ATTRIBUTE_CONTROLLED_BY_CATEGORY_SELECTION = 2;
    const ATTRIBUTE_YES = 1;
    const ATTRIBUTE_NO = 0;

    /**
     * @var OptionFactory
     */
    public $optionFactory;

    public function getAllOptions()
    {
        if (null === $this->_options) {
            $this->_options = [
                [
                    'label' => __('Controlled by Category Selection'),
                    'value' => self::ATTRIBUTE_CONTROLLED_BY_CATEGORY_SELECTION
                ],
                ['label' => __('Yes'), 'value' => self::ATTRIBUTE_YES],
                ['label' => __('No'), 'value' => self::ATTRIBUTE_NO],
            ];
        }
        return $this->_options;
    }

    /**
     * Get a text for option value
     *
     * @param string|integer $value
     * @return string|bool
     */
    public function getOptionText($value)
    {
        foreach ($this->getAllOptions() as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }

    /**
     * Retrieve flat column definition
     *
     * @return array
     */
    public function getFlatColumns()
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        return [
            $attributeCode => [
                'unsigned' => false,
                'default' => null,
                'extra' => null,
                'type' => Table::TYPE_INTEGER,
                'nullable' => true,
                'comment' => 'Custom Attribute Options  ' . $attributeCode . ' column',
            ],
        ];
    }
}
