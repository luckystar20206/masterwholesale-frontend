<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


namespace Neklo\MakeAnOffer\Controller\Adminhtml\Request;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Neklo_MakeAnOffer::main';

    /**
     * Redirect url
     */
    const REDIRECT_URL = '*/request/index';

    /**
     * Field id
     */
    const ID_FIELD = 'selected';

    /**
     * @var \Neklo\MakeAnOffer\Model\RequestFactory
     */
    private $requestFactory;

    /**
     * MassDelete constructor.
     * @param Context $context
     * @param \Neklo\MakeAnOffer\Model\RequestFactory $requestFactory
     */
    public function __construct(
        Context $context,
        \Neklo\MakeAnOffer\Model\RequestFactory $requestFactory
    ) {
        $this->requestFactory = $requestFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $selected = $this->getSelected();
        try {
            if (!empty($selected)) {
                $collection = $this->requestFactory->create()
                    ->getCollection()
                    ->addFieldToFilter('main_table.id', ['in' => $selected]);
                foreach ($collection as $item) {
                    $item->delete();
                }

                $this->messageManager->addSuccessMessage(__('Items was successfully deleted'));
            } else {
                $this->messageManager->addError(__('Please select item(s).'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Operation error'));
        }
        return $this->_redirect(static::REDIRECT_URL);
    }

    protected function getSelected()
    {
        $selected = $this->getRequest()->getParam(self::ID_FIELD);
        $excluded = $this->getRequest()->getParam('excluded');
        if ($excluded !== null) {
            $selected = $this->requestFactory->create()
                ->getCollection()
                ->addFieldToFilter('main_table.id', ['nin' => $excluded])
                ->getAllIds();
        }
        if (!is_array($selected)) {
            $selected = explode(',', $selected);
        }

        return $selected;
    }
}