<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Block\Adminhtml\Walmart\Template\Synchronization;

/**
 * Class Edit
 * @package Ess\M2ePro\Block\Adminhtml\Walmart\Template\Synchronization
 */
class Edit extends \Ess\M2ePro\Block\Adminhtml\Walmart\Template\Edit
{
    //########################################

    public function _construct()
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->_controller = 'adminhtml_walmart_template_synchronization';
        // ---------------------------------------

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');
        // ---------------------------------------

        // ---------------------------------------
        $url = $this->getHelper('Data')->getBackUrl('list');
        $this->addButton('back', [
            'label'     => $this->__('Back'),
            'onclick'   => 'WalmartTemplateSynchronizationObj.backClick(\'' . $url . '\')',
            'class'     => 'back'
        ]);
        // ---------------------------------------

        $isSaveAndClose = (bool)$this->getRequest()->getParam('close_on_save', false);

        if (!$isSaveAndClose
            && $this->getHelper('Data\GlobalData')->getValue('tmp_template')
            && $this->getHelper('Data\GlobalData')->getValue('tmp_template')->getId()
        ) {
            // ---------------------------------------
            $this->addButton('duplicate', [
                'label'     => $this->__('Duplicate'),
                'onclick'   => 'WalmartTemplateSynchronizationObj.duplicateClick'
                    .'(\'Walmart-template-synchronization\')',
                'class'     => 'add M2ePro_duplicate_button primary'
            ]);
            // ---------------------------------------

            // ---------------------------------------
            $this->addButton('delete', [
                'label'     => $this->__('Delete'),
                'onclick'   => 'WalmartTemplateSynchronizationObj.deleteClick()',
                'class'     => 'delete M2ePro_delete_button primary'
            ]);
            // ---------------------------------------
        }

        // ---------------------------------------

        if ($isSaveAndClose) {
            $this->removeButton('back');

            $saveButtons = [
                'id' => 'save_and_close',
                'label' => $this->__('Save And Close'),
                'class' => 'add',
                'button_class' => '',
                'onclick'   => 'WalmartTemplateSynchronizationObj.saveAndCloseClick('
                    . '\'' . $this->getSaveConfirmationText() . '\','
                    . '\'' . \Ess\M2ePro\Block\Adminhtml\Walmart\Template\Grid::TEMPLATE_SYNCHRONIZATION . '\''
                    . ')',
                'class_name' => 'Ess\M2ePro\Block\Adminhtml\Magento\Button\SplitButton',
                'options' => [
                    'save' => [
                        'label' => $this->__('Save And Continue Edit'),
                        'onclick' => 'WalmartTemplateSynchronizationObj.saveAndEditClick('
                            . '\'\','
                            . 'undefined,'
                            . '\'' . $this->getSaveConfirmationText() . '\','
                            . '\'' . \Ess\M2ePro\Block\Adminhtml\Walmart\Template\Grid::TEMPLATE_SYNCHRONIZATION . '\''
                            . ')'
                    ]
                ]
            ];
        } else {
            $saveButtons = [
                'id' => 'save_and_continue',
                'label' => $this->__('Save And Continue Edit'),
                'class' => 'add',
                'button_class' => '',
                'onclick'   => 'WalmartTemplateSynchronizationObj.saveAndEditClick('
                    . '\'\','
                    . 'undefined,'
                    . '\'' . $this->getSaveConfirmationText() . '\','
                    . '\'' . \Ess\M2ePro\Block\Adminhtml\Walmart\Template\Grid::TEMPLATE_SYNCHRONIZATION . '\''
                    . ')',
                'class_name' => 'Ess\M2ePro\Block\Adminhtml\Magento\Button\SplitButton',
                'options' => [
                    'save' => [
                        'label'     => $this->__('Save And Back'),
                        'onclick'   =>'WalmartTemplateSynchronizationObj.saveClick('
                            . '\'\','
                            . '\'' . $this->getSaveConfirmationText() . '\','
                            . '\'' . \Ess\M2ePro\Block\Adminhtml\Walmart\Template\Grid::TEMPLATE_SYNCHRONIZATION . '\''
                            . ')',
                        'class'     => 'save primary'
                    ]
                ]
            ];
        }

        // ---------------------------------------

        $this->addButton('save_buttons', $saveButtons);
        // ---------------------------------------

        $this->css->addFile('walmart/template.css');
    }

    //########################################
}
