<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Osc
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Osc\Controller\Adminhtml\Field;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Mageplaza\Osc\Helper\Data as OscHelper;

/**
 * Class Save
 * @package Mageplaza\Osc\Controller\Adminhtml\Field
 */
class Save extends Action
{
    /**
     * @var Config
     */
    private $resourceConfig;

    /**
     * @var ScopeConfigInterface
     */
    private $_appConfig;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * Save constructor.
     *
     * @param Context $context
     * @param Config $resourceConfig
     * @param ReinitableConfigInterface $config
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        Config $resourceConfig,
        ReinitableConfigInterface $config,
        JsonFactory $resultJsonFactory
    ) {
        $this->resourceConfig    = $resourceConfig;
        $this->_appConfig        = $config;
        $this->resultJsonFactory = $resultJsonFactory;

        parent::__construct($context);
    }

    /**
     * Save position to config
     *
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        /** @var Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        $result = [
            'message' => (string) __('Error during save field position.'),
            'type'    => 'error',
        ];

        $fields   = $this->getRequest()->getParam('fields', false);
        $oaFields = $this->getRequest()->getParam('oaFields', false);
        if ($fields || $oaFields) {
            try {
                $this->resourceConfig->saveConfig(
                    OscHelper::SORTED_FIELD_POSITION,
                    $fields,
                    ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                    0
                );

                $this->resourceConfig->saveConfig(
                    OscHelper::OA_FIELD_POSITION,
                    $oaFields,
                    ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                    0
                );
            } catch (Exception $e) {
                $result['message'] = $e->getMessage();

                return $resultJson->setData($result);
            }

            // re-init configuration
            $this->_appConfig->reinit();

            $result['message'] = (string) __('All fields have been saved.');
            $result['type']    = 'success';
        }

        return $resultJson->setData($result);
    }
}
