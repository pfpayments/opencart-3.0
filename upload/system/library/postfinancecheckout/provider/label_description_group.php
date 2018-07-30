<?php

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
	 * @return \Wallee\Sdk\Model\LabelDescriptorGroup
	 */
	public function find($id){
		return parent::find($id);
	}

	/**
	 * Returns a list of label descriptor groups.
	 *
	 * @return \Wallee\Sdk\Model\LabelDescriptorGroup[]
	 */
	public function getAll(){
		return parent::getAll();
	}

	protected function fetchData(){
		$label_descriptor_group_service = new \Wallee\Sdk\Service\LabelDescriptionGroupService(
				\PostFinanceCheckoutHelper::instance($this->registry)->getApiClient());
		return $label_descriptor_group_service->all();
	}

	protected function getId($entry){
		/* @var \Wallee\Sdk\Model\LabelDescriptorGroup $entry */
		return $entry->getId();
	}
}