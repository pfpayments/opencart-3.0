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
 * Provider of currency information from the gateway.
 */
class Currency extends AbstractProvider {

	protected function __construct(\Registry $registry){
		parent::__construct($registry, 'oc_postfinancecheckout_currencies');
	}

	/**
	 * Returns the currency by the given code.
	 *
	 * @param string $code
	 * @return \PostFinanceCheckout\Sdk\Model\RestCurrency
	 */
	public function find($code){
		return parent::find($code);
	}

	/**
	 * Returns a list of currencies.
	 *
	 * @return \PostFinanceCheckout\Sdk\Model\RestCurrency[]
	 */
	public function getAll(){
		return parent::getAll();
	}

	protected function fetchData(){
		$currency_service = new \PostFinanceCheckout\Sdk\Service\CurrencyService(\PostFinanceCheckoutHelper::instance($this->registry)->getApiClient());
		return $currency_service->all();
	}

	protected function getId($entry){
		/* @var \PostFinanceCheckout\Sdk\Model\RestCurrency $entry */
		return $entry->getCurrencyCode();
	}
}