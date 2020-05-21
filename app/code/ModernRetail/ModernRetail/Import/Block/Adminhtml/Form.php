<?php

namespace ModernRetail\Import\Block\Adminhtml;

/**
 * Banner grid container.
 * @category Magestore
 * @package  Magestore_Bannerslider
 * @module   Bannerslider
 * @author   Magestore Developer
 */
class Form extends \Magento\Backend\Block\Widget\Form
{

    public $form = null;


    public function __construct(\Magento\Backend\Block\Template\Context $context,\Magento\Framework\Data\Form $form,
                                \Magento\Framework\App\Request\Http $request,
                                \Magento\Backend\Helper\Data $mageHelper,
                                \ModernRetail\Import\Helper\Data $helper, array $data)
    {
        $this->form = $form;
        $this->helper = $helper;
        $this->_helper = $mageHelper;
        $this->request = $request;
        parent::__construct($context, $data);
    }

    protected function _prepareForm()
    {
//
//        $form = new \Magento\Framework\Data\Form(
//            array(
//                'id' => 'edit_form',
//                'action' => $this->getUrl('*/*/upload', array('id' =>    $this->getRequest()->getParam('id'))),
//                'method' => 'post',
//                'enctype' => 'multipart/form-data'
//            )
//        );

        $form = $this->form;
        $form->setAction($this->_helper->getUrl('*/*/upload', array('id' =>    $this->request->getParam('id'))));
        $form->setMethod('post');
        $form->setEnctype('multipart/form-data');
        $form->setUseContainer(true);
        $this->setForm($form);
        $fieldset = $form->addFieldset('mr_import_fieldset',
            array('legend'=>'File Upload'));


        $fieldset->addField('mr_import_file', 'file', array(
            'label'     => 'XML File To Import',
            'required'  => false,
            'name'      => 'mr_import_file',
        ));

        $fieldset->addField('mr_import_submit', 'submit', array(
            'label'     => "",
            'required'  => false,
            "class"=>"upload-mr-file",
            'after_element_html' => '<small>Click to upload file</small>',
            'name'      => 'submit',
            'value'=>"Upload File"
        ));


    }
}
