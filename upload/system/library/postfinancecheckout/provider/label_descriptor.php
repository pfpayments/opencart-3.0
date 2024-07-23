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
 * Provider of label descriptor information from the gateway.
 */
class LabelDescriptor extends AbstractProvider {

	protected function __construct(\Registry $registry){
		parent::__construct($registry, 'oc_postfinancecheckout_label_descriptor');
	}

	/**
	 * Returns the label descriptor by the given code.
	 *
	 * @param int $id
	 * @return \PostFinanceCheckout\Sdk\Model\LabelDescriptor
	 */
	public function find($id){
		return parent::find($id);
	}

	/**
	 * Returns a list of label descriptors.
	 *
	 * @return \PostFinanceCheckout\Sdk\Model\LabelDescriptor[]
	 */
	public function getAll(){
		return parent::getAll();
	}

	protected function fetchData(){
		$label_descriptor_service = new \PostFinanceCheckout\Sdk\Service\LabelDescriptionService(\PostFinanceCheckoutHelper::instance($this->registry)->getApiClient());
		return $label_descriptor_service->all();
	}

	protected function getId($entry){
		/* @var \PostFinanceCheckout\Sdk\Model\LabelDescriptor $entry */
		return $entry->getId();
	}
}