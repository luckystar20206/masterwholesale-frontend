<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Helper\Component\Ebay;

/**
 * Class Motors
 * @package Ess\M2ePro\Helper\Component\Ebay
 */
class Motors extends \Ess\M2ePro\Helper\AbstractHelper
{
    const TYPE_EPID_MOTOR = 1;
    const TYPE_KTYPE      = 2;
    const TYPE_EPID_UK    = 3;
    const TYPE_EPID_DE    = 4;

    const EPID_SCOPE_MOTORS = 1;
    const EPID_SCOPE_UK     = 2;
    const EPID_SCOPE_DE     = 3;

    const PRODUCT_TYPE_VEHICLE    = 0;
    const PRODUCT_TYPE_MOTORCYCLE = 1;
    const PRODUCT_TYPE_ATV        = 2;

    const MAX_ITEMS_COUNT_FOR_ATTRIBUTE = 3000;

    private $moduleConfig;
    private $resourceConnection;

    //########################################

    public function __construct(
        \Ess\M2ePro\Model\Config\Manager\Module $moduleConfig,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->moduleConfig = $moduleConfig;
        $this->resourceConnection = $resourceConnection;
        parent::__construct($helperFactory, $context);
    }

    //########################################

    public function getAttribute($type)
    {
        switch ($type) {
            case self::TYPE_EPID_MOTOR:
                return $this->moduleConfig->getGroupValue(
                    '/ebay/motors/',
                    'epids_motor_attribute'
                );

            case self::TYPE_KTYPE:
                return $this->moduleConfig->getGroupValue(
                    '/ebay/motors/',
                    'ktypes_attribute'
                );

            case self::TYPE_EPID_UK:
                return $this->moduleConfig->getGroupValue(
                    '/ebay/motors/',
                    'epids_uk_attribute'
                );

            case self::TYPE_EPID_DE:
                return $this->moduleConfig->getGroupValue(
                    '/ebay/motors/',
                    'epids_de_attribute'
                );
        }

        return '';
    }

    //########################################

    public function parseAttributeValue($value)
    {
        $parsedData = [
            'items' => [],
            'filters' => [],
            'groups' => []
        ];

        if (empty($value)) {
            return $parsedData;
        }

        $value = trim($value, ',') . ',';

        preg_match_all(
            '/("?(\d+)"?,)|' .
             '("?(\d+?)"?\|"(.+?)",)|' .
             '("?(ITEM)"?\|"(\d+?)"?\|"(.+?)",)|' .
             '("?(FILTER)"?\|"?(\d+?)"?,)|' .
             '("?(GROUP)"?\|"?(\d+?)"?,)/',
            $value,
            $matches
        );

        $items = [];
        foreach ($matches[0] as $item) {
            $item = explode('|', $item);

            $item[0] = trim(trim($item[0], ','), '"');
            $item[1] = (empty($item[1])) ? '' : trim(trim($item[1], ','), '"');
            $item[2] = (empty($item[2])) ? '' : trim(trim($item[2], ','), '"');

            $items[] = [$item[0],$item[1],$item[2]];
        }

        foreach ($items as $item) {
            if (empty($item[0])) {
                continue;
            }

            if ($item[0] == 'FILTER') {
                if ((empty($item[1]))) {
                    continue;
                }

                if (in_array($item[1], $parsedData['filters'])) {
                    continue;
                }

                $parsedData['filters'][] = $item[1];
            } elseif ($item[0] == 'GROUP') {
                if ((empty($item[1]))) {
                    continue;
                }

                if (in_array($item[1], $parsedData['groups'])) {
                    continue;
                }

                $parsedData['groups'][] = $item[1];
            } else {
                if ($item[0] === 'ITEM') {
                    $itemId = $item[1];
                    $itemNote = $item[2];
                } else {
                    $itemId = $item[0];
                    $itemNote = $item[1];
                }

                $parsedData['items'][$itemId]['id'] = $itemId;
                $parsedData['items'][$itemId]['note'] = $itemNote;
            }
        }

        return $parsedData;
    }

    public function buildAttributeValue(array $data)
    {
        $strs = [];

        if (!empty($data['items'])) {
            $strs[] = $this->buildItemsAttributeValue($data['items']);
        }

        if (!empty($data['filters'])) {
            $strs[] = $this->buildFilterAttributeValue($data['filters']);
        }

        if (!empty($data['groups'])) {
            $strs[] = $this->buildGroupAttributeValue($data['groups']);
        }

        return implode(',', $strs);
    }

    // ---------------------------------------

    public function buildItemsAttributeValue(array $items)
    {
        if (empty($items)) {
            return '';
        }

        $values = [];
        foreach ($items as $item) {
            $value = '"ITEM"|"' . $item['id'] . '"';

            $note = trim($item['note']);

            if (!empty($note)) {
                $value .= '|"' . $note . '"';
            }

            $values[] = $value;
        }

        return implode(',', $values);
    }

    public function buildFilterAttributeValue(array $filters)
    {
        if (empty($filters)) {
            return '';
        }

        $values = [];
        foreach ($filters as $id) {
            $values[] = '"FILTER"|"' . $id . '"';
        }

        return implode(',', $values);
    }

    public function buildGroupAttributeValue(array $groups)
    {
        if (empty($groups)) {
            return '';
        }

        $values = [];
        foreach ($groups as $id) {
            $values[] = '"GROUP"|"' . $id . '"';
        }

        return implode(',', $values);
    }

    //########################################

    public function isTypeBasedOnEpids($type)
    {
        if (in_array($type, [
            self::TYPE_EPID_MOTOR,
            self::TYPE_EPID_UK,
            self::TYPE_EPID_DE])
        ) {
            return true;
        }

        return false;
    }

    public function isTypeBasedOnKtypes($type)
    {
        return $type == self::TYPE_KTYPE;
    }

    //########################################

    public function getDictionaryTable($type)
    {
        if ($this->isTypeBasedOnEpids($type)) {
            return $this->getHelper('Module_Database_Structure')->getTableNameWithPrefix(
                'm2epro_ebay_dictionary_motor_epid'
            );
        }

        if ($this->isTypeBasedOnKtypes($type)) {
            return $this->getHelper('Module_Database_Structure')->getTableNameWithPrefix(
                'm2epro_ebay_dictionary_motor_ktype'
            );
        }

        return '';
    }

    public function getIdentifierKey($type)
    {
        if ($this->isTypeBasedOnEpids($type)) {
            return 'epid';
        }

        if ($this->isTypeBasedOnKtypes($type)) {
            return 'ktype';
        }

        return '';
    }

    //########################################

    public function getEpidsScopeByType($type)
    {
        switch ($type) {
            case self::TYPE_EPID_MOTOR:
                return self::EPID_SCOPE_MOTORS;

            case self::TYPE_EPID_UK:
                return self::EPID_SCOPE_UK;

            case self::TYPE_EPID_DE:
                return self::EPID_SCOPE_DE;

            default:
                return null;
        }
    }

    public function getEpidsTypeByMarketplace($marketplaceId)
    {
        switch ((int)$marketplaceId) {
            case \Ess\M2ePro\Helper\Component\Ebay::MARKETPLACE_MOTORS:
                return self::TYPE_EPID_MOTOR;

            case \Ess\M2ePro\Helper\Component\Ebay::MARKETPLACE_UK:
                return self::TYPE_EPID_UK;

            case \Ess\M2ePro\Helper\Component\Ebay::MARKETPLACE_DE:
                return self::TYPE_EPID_DE;

            default:
                return null;
        }
    }

    //########################################
}
