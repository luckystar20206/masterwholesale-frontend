<?php
namespace ModernRetail\Import\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class AttributesCheck extends AbstractHelper
{

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource

    )
    {
        $this->_resource = $resource;
        $this->connection = $this->_resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
    }

    public function checkAttributes($_attributesCodes)
    {

        $attributes = [];
        foreach ($_attributesCodes as $attr) {
            $attributes[] = "'$attr'";
        }
        $attributesCodes = implode(',', $attributes);
        $catalogEavAttributeTable = $this->_resource->getTableName('catalog_eav_attribute');
        $eavAttributeTable = $this->_resource->getTableName('eav_attribute');

        /**
         * Search for exist attributes
         *
         */

        $sql = "SELECT ea.attribute_id, ea.attribute_code, ca.is_global FROM $eavAttributeTable AS ea
        LEFT JOIN $catalogEavAttributeTable AS ca ON ca.attribute_id = ea.attribute_id
        WHERE ea.attribute_code IN ($attributesCodes)";

        $_existAttributes = $this->connection->query($sql);
        $existAttributes = $_existAttributes->fetchAll();

        $exceptionAttr = [];
        $nonGlobal = [];
        if (count($existAttributes) > 0) {
            $attrToCheck = [];
            foreach ($existAttributes as $attr) {
                $attrToCheck[] = $attr['attribute_code'];
                if ($attr['is_global'] == 0) {
                    $nonGlobal[] = $attr['attribute_code'];
                }
            }
            $exceptionAttr = array_diff($_attributesCodes, $attrToCheck);
        }

        $nonGlobalAttr = null;
        if (count($nonGlobal) > 0) {
            $nonGlobalAttr = implode(',', $nonGlobal);
        }
        $nonExistingAttr = null;
        if (count($exceptionAttr) > 0) {
            $nonExistingAttr = implode(',', $exceptionAttr);
        }

        /**
         * Check attributes
         */

        if ($nonGlobalAttr || $nonExistingAttr) {

            $msg = 'Attribute(s) ' . $nonExistingAttr . ' not exists in magento and ' . $nonGlobalAttr . ' hasn`t global scope';

            if (!$nonExistingAttr) {
                $msg = 'Attribute(s) ' . $nonGlobalAttr . ' hasn`t global scope';
            }
            if (!$nonGlobalAttr) {
                $msg = 'Attribute(s) ' . $nonExistingAttr . ' not exists in magento.';
            }

            return $msg;
        }

        return false;

    }
}