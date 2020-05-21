<?php
namespace Smartwave\Dailydeals\Controller\Adminhtml\Dailydeal;

abstract class InlineEdit extends \Magento\Backend\App\Action
{
    /**
     * JSON Factory
     *
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;

    /**
     * Dailydeal Factory
     *
     * @var \Smartwave\Dailydeals\Model\DailydealFactory
     */
    protected $dailydealFactory;

    /**
     * constructor
     *
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Smartwave\Dailydeals\Model\DailydealFactory $dailydealFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Smartwave\Dailydeals\Model\DailydealFactory $dailydealFactory,
        \Magento\Backend\App\Action\Context $context
    ) {
    
        $this->jsonFactory      = $jsonFactory;
        $this->dailydealFactory = $dailydealFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];
        $postItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }
        foreach (array_keys($postItems) as $dailydealId) {
            /** @var \Smartwave\Dailydeals\Model\Dailydeal $dailydeal */
            $dailydeal = $this->dailydealFactory->create()->load($dailydealId);
            try {
                $dailydealData = $postItems[$dailydealId];//todo: handle dates
                $dailydeal->addData($dailydealData);
                $dailydeal->save();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $messages[] = $this->getErrorWithDailydealId($dailydeal, $e->getMessage());
                $error = true;
            } catch (\RuntimeException $e) {
                $messages[] = $this->getErrorWithDailydealId($dailydeal, $e->getMessage());
                $error = true;
            } catch (\Exception $e) {
                $messages[] = $this->getErrorWithDailydealId(
                    $dailydeal,
                    __('Something went wrong while saving the Dailydeal.')
                );
                $error = true;
            }
        }
        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * Add Dailydeal id to error message
     *
     * @param \Smartwave\Dailydeals\Model\Dailydeal $dailydeal
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithDailydealId(\Smartwave\Dailydeals\Model\Dailydeal $dailydeal, $errorText)
    {
        return '[Dailydeal ID: ' . $dailydeal->getId() . '] ' . $errorText;
    }
}
