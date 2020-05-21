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

namespace Mageplaza\Osc\Block\Adminhtml\Field;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Mageplaza\OrderAttributes\Helper\Data as oaHelper;
use Mageplaza\OrderAttributes\Model\Attribute;
use Mageplaza\Osc\Helper\Data;

/**
 * Class AbstractOrderField
 * @package Mageplaza\Osc\Block\Adminhtml\Field
 */
abstract class AbstractOrderField extends AbstractField
{
    const BLOCK_SCOPE = [];

    /**
     * @inheritdoc
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function _construct()
    {
        parent::_construct();

        /** Prepare collection */
        list($this->sortedFields, $this->availableFields) = $this->getFields();
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getFields()
    {
        if (!$this->isVisible()) {
            return [[], []];
        }

        /** @var oaHelper $oaHelper */
        $oaHelper = $this->helper->getObject(oaHelper::class);

        $availFields  = [];
        $sortedFields = [];
        $sortOrder    = 1;
        $isSepAdded   = false;

        foreach ($oaHelper->getFilteredAttributes() as $field) {
            if (in_array((int) $field->getPosition(), static::BLOCK_SCOPE, true)) {
                $availFields[] = $field;
            }
        }

        $sepLabel  = $this->getSeparatorLabel();
        $separator = new DataObject([
            'col_style'      => 'wide ui-state-disabled',
            'frontend_label' => $sepLabel,
        ]);

        $oaFields = $this->helper->getOAFieldPosition();

        usort($oaFields, function ($a, $b) {
            return ($a['bottom'] <= $b['bottom']) ? -1 : 1;
        });

        foreach ($oaFields as $field) {
            /** @var Attribute $avField */
            foreach ($availFields as $key => $avField) {
                if ($field['code'] === $avField->getAttributeCode()) {
                    unset($availFields[$key]);

                    if ($sepLabel && !$isSepAdded && !empty($field['bottom'])) {
                        $sortedFields[] = $separator;

                        $isSepAdded = true;
                    }

                    $avField
                        ->setColspan($field['colspan'])
                        ->setSortOrder($sortOrder++)
                        ->setColStyle($this->helper->getColStyle($field['colspan']))
                        ->setIsRequired($field['required'])
                        ->setIsRequiredMp($field['required']);

                    $sortedFields[] = $avField;
                    break;
                }
            }
        }

        if (!$isSepAdded && $sepLabel) {
            $sortedFields[] = $separator;
        }

        return [$sortedFields, $availFields];
    }

    /**
     * @return string
     */
    public function getBlockTitle()
    {
        return (string) __('Order Summary');
    }

    /**
     * @return bool
     */
    public function isVisible()
    {
        return $this->helper->isEnableOrderAttributes();
    }

    /**
     * @return bool
     */
    public function hasFields()
    {
        return count($this->availableFields) > 0 || count($this->sortedFields) > 1;
    }

    /**
     * @return string
     */
    public function getNoticeMessage()
    {
        if (!$this->isVisible()) {
            $url = "<a href='" . Data::ORDER_ATTR_URL . "' target='_blank'>Mageplaza Order Attributes</a>";

            return (string) __('Please install %1 to add more custom checkout fields.', $url);
        }

        if (!$this->hasFields()) {
            $url = $this->getUrl('mporderattributes/attribute/new');
            $url = "<a href='" . $url . "' target='_blank'>add new attributes</a>";

            return (string) __('Order Attributes module has already installed. Please %1 to manage them here.', $url);
        }

        return '';
    }

    /**
     * @return Phrase|string
     */
    private function getSeparatorLabel()
    {
        switch (static::BLOCK_ID) {
            case Shipping::BLOCK_ID:
                return __('Shipping Method');
            case Payment::BLOCK_ID:
                return __('Payment Method');
            case Order::BLOCK_ID:
                return __('Order Summary');
            default:
                return '';
        }
    }
}
