<?php
namespace Smartwave\Dailydeals\Controller\Adminhtml\Dailydeal;

class Delete extends \Smartwave\Dailydeals\Controller\Adminhtml\Dailydeal
{
    /**
     * execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('dailydeal_id');
        if ($id) {
            $sw_product_sku = "";
            try {
                /** @var \Smartwave\Dailydeals\Model\Dailydeal $dailydeal */
                $dailydeal = $this->dailydealFactory->create();
                $dailydeal->load($id);
                $sw_product_sku = $dailydeal->getSw_product_sku();
                $dailydeal->delete();
                $this->messageManager->addSuccess(__('The Dailydeal has been deleted.'));
                $this->_eventManager->dispatch(
                    'adminhtml_sw_dailydeals_dailydeal_on_delete',
                    ['sw_product_sku' => $sw_product_sku, 'status' => 'success']
                );
                $resultRedirect->setPath('sw_dailydeals/*/');
                return $resultRedirect;
            } catch (\Exception $e) {
                $this->_eventManager->dispatch(
                    'adminhtml_sw_dailydeals_dailydeal_on_delete',
                    ['sw_product_sku' => $sw_product_sku, 'status' => 'fail']
                );
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                $resultRedirect->setPath('sw_dailydeals/*/edit', ['dailydeal_id' => $id]);
                return $resultRedirect;
            }
        }
        // display error message
        $this->messageManager->addError(__('Dailydeal to delete was not found.'));
        // go to grid
        $resultRedirect->setPath('sw_dailydeals/*/');
        return $resultRedirect;
    }
}
