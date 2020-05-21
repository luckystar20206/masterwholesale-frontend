<?php
namespace Smartwave\Dailydeals\Controller\Adminhtml\Dailydeal;

class Save extends \Smartwave\Dailydeals\Controller\Adminhtml\Dailydeal
{
    /**
     * Backend session
     *
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * Date filter
     *
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    protected $dateFilter;

    protected $productFactory;
    /**
     * constructor
     *
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     * @param \Smartwave\Dailydeals\Model\DailydealFactory $dailydealFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        \Smartwave\Dailydeals\Model\DailydealFactory $dailydealFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
    
        parent::__construct($dailydealFactory, $registry, $context);

        $this->backendSession = $context->getSession();
        $this->resultRedirectFactory=$context->getResultRedirectFactory();
        $this->productFactory = $productFactory;
        $this->dateFilter     = $dateFilter;
    }

    /**
     * run the action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $data = $this->getRequest()->getPost('dailydeal');
        if (isset($data["dailydeal_id"])) {
            $dailydealId=$data["dailydeal_id"];
        }
        // Store the date from and to in to varaible
        $fromdate=$data["sw_date_from"];
        $todate= $data["sw_date_to"];

        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $data = $this->filterData($data);
           
            $dailydeal = $this->initDailydeal();
            $dailydeal->setData($data);
            
            $this->_eventManager->dispatch(
                'sw_dailydeals_dailydeal_prepare_save',
                [
                    'dailydeal' => $dailydeal,
                    'request' => $this->getRequest()
                ]
            );
            try {
                $dailydealCollection=$this->dailydealFactory->create()->getCollection();
       
                $dailydealCollection->addFieldToSelect('*');
                $dailydealCollection->addFieldToFilter('sw_product_sku', ['eq'=>$data["sw_product_sku"]]);
                if (isset($dailydealId)) {
                    $dailydealCollection->addFieldToFilter('dailydeal_id', ['eq'=>$dailydealId]);
                    if ($dailydealCollection->getSize()==1) {
                        $editaction=1;
                    }
                }
              
                if ($dailydealCollection->getSize()== 0 || isset($editaction)) {
                    if ($data["sw_deal_enable"] == 1) {
                        $productCollection=$this->productFactory->create()->getCollection();
                        $product=$productCollection->addAttributeToSelect('*');
                        $product=$productCollection->addAttributeToFilter('sku', ['eq'=>$data["sw_product_sku"]]);

                        $finalproductprice=$product->getFirstItem()->getFinalPrice();
                        if ($product->getFirstItem()->getTypeId() != "bundle") {
                            if ($data["sw_discount_type"] == 1) { // For Fixed
                          
                                $dailydeal->setSwProductPrice($finalproductprice - $data["sw_discount_amount"]);
                            } elseif ($data["sw_discount_type"] == 2) { // For Percentage
                                $dailydeal->setSwProductPrice($finalproductprice  - (($finalproductprice * $data["sw_discount_amount"])/100));
                            }
                        } else {
                            $dailydeal->setSwProductPrice(1);
                        }
                    }

                    $dailydeal->setSwDateFrom($fromdate);
                    $dailydeal->setSwDateTo($todate);

                    $dailydeal->save();
    
                    $this->messageManager->addSuccess(__('The Dailydeal has been saved.'));
                } else {
                    $this->messageManager->addError("Already set dailydeal for this Product.");
                }
 
                $this->backendSession->setSwDailydealsDailydealData(false);
                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath(
                        'sw_dailydeals/*/edit',
                        [
                            'dailydeal_id' => $dailydeal->getId(),
                            '_current' => true
                        ]
                    );
                    return $resultRedirect;
                }
                $resultRedirect->setPath('sw_dailydeals/*/');
                return $resultRedirect;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Dailydeal.'));
            }
            $this->_getSession()->setSwDailydealsDailydealData($data);
            $resultRedirect->setPath(
                'sw_dailydeals/*/edit',
                [
                    'dailydeal_id' => $dailydeal->getId(),
                    '_current' => true
                ]
            );
            return $resultRedirect;
        }
        $resultRedirect->setPath('sw_dailydeals/*/');
        return $resultRedirect;
    }

    /**
     * filter values
     *
     * @param array $data
     * @return array
     */
    protected function filterData($data)
    {
        $inputFilter = new \Zend_Filter_Input(
            [
                'sw_date_from' => $this->dateFilter,
                'sw_date_to' => $this->dateFilter,
            ],
            [],
            $data
        );
        $data = $inputFilter->getUnescaped();
        return $data;
    }
}
