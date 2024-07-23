<?php
/**
 * PostFinanceCheckout OpenCart
 *
 * This OpenCart module enables to process payments with PostFinanceCheckout (https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html).
 *
 * @package Whitelabelshortcut\PostFinanceCheckout
 * @author wallee AG (https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html)
 * @license http://www.apache.org/licenses/LICENSE-2.0  Apache Software License (ASL 2.0)
 */

namespace PostFinanceCheckout\Provider;

/**
 * Provider of payment connector information from the gateway.
 */
class PaymentConnector extends AbstractProvider {

	protected function __construct(\Registry $registry){
		parent::__construct($registry, 'oc_postfinancecheckout_payment_connectors');
	}

	/**
	 * Returns the payment connector by the given id.
	 *
	 * @param int $id
	 * @return \PostFinanceCheckout\Sdk\Model\PaymentConnector
	 */
	public function find($id){
		return parent::find($id);
	}

	/**
	 * Returns a list of payment connectors.
	 *
	 * @return \PostFinanceCheckout\Sdk\Model\PaymentConnector[]
	 */
	public function getAll(){
		return parent::getAll();
	}

	protected function fetchData(){
		$connector_service = new \PostFinanceCheckout\Sdk\Service\PaymentConnectorService(\PostFinanceCheckoutHelper::instance($this->registry)->getApiClient());
		return $connector_service->all();
	}

	protected function getId($entry){
		/* @var \PostFinanceCheckout\Sdk\Model\PaymentConnector $entry */
		return $entry->getId();
	}
}