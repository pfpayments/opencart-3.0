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
 * Provider of label descriptor group information from the gateway.
 */
class LabelDescriptionGroup extends AbstractProvider {

	protected function __construct(\Registry $registry){
		parent::__construct($registry, 'oc_postfinancecheckout_label_descriptor_group');
	}

	/**
	 * Returns the label descriptor group by the given code.
	 *
	 * @param int $id
	 * @return \PostFinanceCheckout\Sdk\Model\LabelDescriptorGroup
	 */
	public function find($id){
		return parent::find($id);
	}

	/**
	 * Returns a list of label descriptor groups.
	 *
	 * @return \PostFinanceCheckout\Sdk\Model\LabelDescriptorGroup[]
	 */
	public function getAll(){
		return parent::getAll();
	}

	protected function fetchData(){
		$label_descriptor_group_service = new \PostFinanceCheckout\Sdk\Service\LabelDescriptionGroupService(
				\PostFinanceCheckoutHelper::instance($this->registry)->getApiClient());
		return $label_descriptor_group_service->all();
	}

	protected function getId($entry){
		/* @var \PostFinanceCheckout\Sdk\Model\LabelDescriptorGroup $entry */
		return $entry->getId();
	}
}