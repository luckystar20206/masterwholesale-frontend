<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Controller\Adminhtml\Ebay\Account\PickupStore;

/**
 * Class Save
 * @package Ess\M2ePro\Controller\Adminhtml\Ebay\Account\PickupStore
 */
class Save extends \Ess\M2ePro\Controller\Adminhtml\Ebay\Account
{
    //########################################

    public function execute()
    {
        if (!$post = $this->getRequest()->getPostValue()) {
            return $this->_redirect(
                '*/*/index',
                ['account_id' => $this->getRequest()->getParam('account_id')]
            );
        }

        $id = (int)$this->getRequest()->getParam('id', 0);

        // Base prepare
        // ---------------------------------------
        $data = [];
        // ---------------------------------------

        // tab: general
        // ---------------------------------------
        $keys = [
            'name',
            'location_id',
            'account_id',
            'marketplace_id',
            'phone',
            'url',
            'pickup_instruction'
        ];

        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }
        // ---------------------------------------

        // tab: location
        // ---------------------------------------
        $keys = [
            'country',
            'region',
            'city',
            'postal_code',
            'address_1',
            'address_2',
            'latitude',
            'longitude',
            'utc_offset'
        ];

        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }
        // ---------------------------------------

        // tab: businessHours
        // ---------------------------------------
        $data['business_hours'] = $this->getHelper('Data')->jsonEncode($post['business_hours']);
        $data['special_hours'] = '';

        if (isset($post['special_hours'])) {
            $data['special_hours'] = $this->getHelper('Data')->jsonEncode($post['special_hours']);
        }
        // ---------------------------------------

        // tab: stockSettings
        // ---------------------------------------
        $keys = [
            'qty_mode',
            'qty_custom_value',
            'qty_custom_attribute',
            'qty_percentage',
            'qty_modification_mode',
            'qty_min_posted_value',
            'qty_max_posted_value'
        ];

        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }

        if (isset($post['default_mode']) && $post['default_mode'] == 0) {
            $data['qty_mode'] = \Ess\M2ePro\Model\Ebay\Account\PickupStore::QTY_MODE_SELLING_FORMAT_TEMPLATE;
        }
        // ---------------------------------------

        // creating of pickup store
        // ---------------------------------------
        if (!$this->getHelper('Component_Ebay_PickupStore')->validateRequiredFields($data)) {
            $this->getHelper('Data\Session')->setValue('pickup_store_form_data', $data);

            $this->getMessageManager()->addErrorMessage(
                $this->__('Validation error. You must fill all required fields.'),
                self::GLOBAL_MESSAGES_GROUP
            );

            return $id ? $this->_redirect('*/*/edit', ['id' => $id])
                       : $this->_redirect('*/*/new', ['account_id' => $this->getRequest()->getParam('account_id')]);
        }

        try {
            $dispatcherObject = $this->modelFactory->getObject('Ebay_Connector_Dispatcher');
            $connectorObj = $dispatcherObject->getVirtualConnector(
                'store',
                'add',
                'entity',
                $this->getHelper('Component_Ebay_PickupStore')->prepareRequestData($data),
                null,
                null,
                $this->getRequest()->getParam('account_id')
            );

            $dispatcherObject->process($connectorObj);
        } catch (\Exception $exception) {
            $this->getHelper('Module\Exception')->process($exception);
            $this->getHelper('Data\Session')->setValue('pickup_store_form_data', $data);

            $this->getMessageManager()->addErrorMessage($this->__(
                'The New Store has not been created. <br/>Reason: %error_message%',
                $exception->getMessage()
            ));

            return $id ? $this->_redirect('*/*/edit', ['id' => $id])
                       : $this->_redirect('*/*/new', ['account_id' => $this->getRequest()->getParam('account_id')]);
        }
        // ---------------------------------------

        $model = $this->activeRecordFactory->getObject('Ebay_Account_PickupStore');
        if ($id) {
            $model->load($id);
            $model->addData($data);
        } else {
            $model->setData($data);
        }
        $model->save();

        $this->getMessageManager()->addSuccessMessage(
            $this->__('Store was successfully saved.'),
            self::GLOBAL_MESSAGES_GROUP
        );

        return $this->_redirect($this->getHelper('Data')->getBackUrl(
            'list',
            [],
            [
                'list' => ['account_id' => $model->getAccountId()],
                'edit' => ['id' => $model->getId()]
            ]
        ));
    }

    //########################################
}
