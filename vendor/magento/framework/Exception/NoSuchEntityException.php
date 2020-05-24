<?php
/**
 * No such entity service exception
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Exception;

use Magento\Framework\Phrase;

/**
 * @api
 * @since 100.0.2
 */
class NoSuchEntityException extends LocalizedException
{
    /**
     * @deprecated
     */
    const MESSAGE_SINGLE_FIELD = 'No such entity with %fieldName = %fieldValue';

    /**
     * @deprecated
     */
    const MESSAGE_DOUBLE_FIELDS = 'No such entity with %fieldName = %fieldValue, %field2Name = %field2Value';

    /**
     * @param \Magento\Framework\Phrase $phrase
     * @param \Exception $cause
     * @param int $code
     */
    public function __construct(Phrase $phrase = null, \Exception $cause = null, $code = 0)
    {
        if ($phrase === null) {
            $phrase = new Phrase('No such entity.');
        }
        parent::__construct($phrase, $cause, $code);
    }

    /**
     * Helper function for creating an exception when a single field is responsible for finding an entity.
     *
     * @param string $fieldName
     * @param string|int $fieldValue
     * @return \Magento\Framework\Exception\NoSuchEntityException
     */
    public static function singleField($fieldName, $fieldValue)
    {
		/**
		 * 2020-05-22 Dmitry Fedyuk https://www.upwork.com/fl/mage2pro
		 * "«No such entity with id = 0» at vendor/magento/framework/Exception/NoSuchEntityException.php:50":
		 * https://github.com/masterwholesale-com/site/issues/3
		 * 2020-05-24
		 * The exception in the @see \Magento\Braintree\Block\ApplePay\Shortcut\Button::getQuoteId() method
		 * is expected and should not be logged:
		 *	try {
		 *		$config = $this->defaultConfigProvider->getConfig();
		 *		if (!empty($config['quoteData']['entity_id'])) {
		 *			return $config['quoteData']['entity_id'];
		 *		}
		 *	}
		 *	catch (NoSuchEntityException $e) {
		 *		if ($e->getMessage() !== 'No such entity with cartId = ') {
		 *			throw $e;
		 *		}
		 *	}
		 * https://github.com/genecommerce/module-braintree-magento2/blob/3.4.1/Block/ApplePay/Shortcut/Button.php#L81-L90
		 * 2020-05-25
		 * @see \Magento\Checkout\Model\Session::loadCustomerQuote():
		 *	try {
		 *		$customerQuote = $this->quoteRepository->getForCustomer(
		 * 			$this->_customerSession->getCustomerId()
		 * 		);
		 *	}
		 *	catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
		 *		$customerQuote = $this->quoteFactory->create();
		 *	}
		 * https://github.com/magento/magento2/blob/2.3.2/app/code/Magento/Checkout/Model/Session.php#L362-L366
		 */
		if (
			('cartId' !== $fieldName || !is_null($fieldValue))
			&& !df_bt_has('Magento\Checkout\Model\Session::loadCustomerQuote')
		) {
			df_log_l(__CLASS__, ['fieldName' => $fieldName, 'fieldValue' => $fieldValue]);
		}
        return new self(
            new Phrase(
                'No such entity with %fieldName = %fieldValue',
                [
                    'fieldName' => $fieldName,
                    'fieldValue' => $fieldValue
                ]
            )
        );
    }

    /**
     * Helper function for creating an exception when two fields are responsible for finding an entity.
     *
     * @param string $fieldName
     * @param string|int $fieldValue
     * @param string $secondFieldName
     * @param string|int $secondFieldValue
     * @return \Magento\Framework\Exception\NoSuchEntityException
     */
    public static function doubleField($fieldName, $fieldValue, $secondFieldName, $secondFieldValue)
    {
        return new self(
            new Phrase(
                'No such entity with %fieldName = %fieldValue, %field2Name = %field2Value',
                [
                    'fieldName' => $fieldName,
                    'fieldValue' => $fieldValue,
                    'field2Name' => $secondFieldName,
                    'field2Value' => $secondFieldValue,
                ]
            )
        );
    }
}
