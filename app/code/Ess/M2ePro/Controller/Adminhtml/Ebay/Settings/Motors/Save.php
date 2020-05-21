<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Controller\Adminhtml\Ebay\Settings\Motors;

use Ess\M2ePro\Controller\Adminhtml\Ebay\Settings;

/**
 * Class Save
 * @package Ess\M2ePro\Controller\Adminhtml\Ebay\Settings\Motors
 */
class Save extends Settings
{
    //########################################

    public function execute()
    {
        $motorsAttributes = [];

        if ($motorsEpidsMotorAttribute = $this->getRequest()->getParam('motors_epids_motor_attribute')) {
            $motorsAttributes[] = $motorsEpidsMotorAttribute;
        }
        if ($motorsEpidsUkAttribute = $this->getRequest()->getParam('motors_epids_uk_attribute')) {
            $motorsAttributes[] = $motorsEpidsUkAttribute;
        }
        if ($motorsEpidsDeAttribute = $this->getRequest()->getParam('motors_epids_de_attribute')) {
            $motorsAttributes[] = $motorsEpidsDeAttribute;
        }
        if ($motorsKtypesAttribute = $this->getRequest()->getParam('motors_ktypes_attribute')) {
            $motorsAttributes[] = $motorsKtypesAttribute;
        }

        if (count($motorsAttributes) != count(array_unique($motorsAttributes))) {
            $this->setJsonContent([
                'success' => false,
                'messages' => [
                    ['error' => $this->__('Motors Attributes can not be the same.')]
                ]
            ]);
            return $this->getResult();
        }

        $this->getHelper('Module')->getConfig()->setGroupValue(
            '/ebay/motors/',
            'epids_motor_attribute',
            $motorsEpidsMotorAttribute
        );
        $this->getHelper('Module')->getConfig()->setGroupValue(
            '/ebay/motors/',
            'epids_uk_attribute',
            $motorsEpidsUkAttribute
        );
        $this->getHelper('Module')->getConfig()->setGroupValue(
            '/ebay/motors/',
            'epids_de_attribute',
            $motorsEpidsDeAttribute
        );
        $this->getHelper('Module')->getConfig()->setGroupValue(
            '/ebay/motors/',
            'ktypes_attribute',
            $motorsKtypesAttribute
        );

        $this->setAjaxContent($this->getHelper('Data')->jsonEncode([
            'success' => true
        ]), false);

        return $this->getResult();
    }

    //########################################
}
