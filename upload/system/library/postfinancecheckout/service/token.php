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

namespace PostFinanceCheckout\Service;

/**
 * This service provides functions to deal with PostFinanceCheckout tokens.
 */
class Token extends AbstractService {
	
	/**
	 * The token API service.
	 *
	 * @var \PostFinanceCheckout\Sdk\Service\TokenService
	 */
	private $token_service;
	
	/**
	 * The token version API service.
	 *
	 * @var \PostFinanceCheckout\Sdk\Service\TokenVersionService
	 */
	private $token_version_service;

	public function updateTokenVersion($space_id, $token_version_id){
		$token_version = $this->getTokenVersionService()->read($space_id, $token_version_id);
		$this->updateInfo($space_id, $token_version);
	}

	public function updateToken($space_id, $token_id){
		$query = new \PostFinanceCheckout\Sdk\Model\EntityQuery();
		$filter = new \PostFinanceCheckout\Sdk\Model\EntityQueryFilter();
		$filter->setType(\PostFinanceCheckout\Sdk\Model\EntityQueryFilterType::_AND);
		$filter->setChildren(
				array(
					$this->createEntityFilter('token.id', $token_id),
					$this->createEntityFilter('state', \PostFinanceCheckout\Sdk\Model\TokenVersionState::ACTIVE) 
				));
		$query->setFilter($filter);
		$query->setNumberOfEntities(1);
		$token_versions = $this->getTokenVersionService()->search($space_id, $query);
		if (!empty($token_versions)) {
			$this->updateInfo($space_id, current($token_versions));
		}
		else {
			$info = \PostFinanceCheckout\Entity\TokenInfo::loadByToken($this->registry, $space_id, $token_id);
			if ($info->getId()) {
				$info->delete($this->registry);
			}
		}
	}

	protected function updateInfo($space_id, \PostFinanceCheckout\Sdk\Model\TokenVersion $token_version){
		$info = \PostFinanceCheckout\Entity\TokenInfo::loadByToken($this->registry, $space_id, $token_version->getToken()->getId());
		if (!in_array($token_version->getToken()->getState(),
				array(
					\PostFinanceCheckout\Sdk\Model\TokenVersionState::ACTIVE,
					\PostFinanceCheckout\Sdk\Model\TokenVersionState::UNINITIALIZED 
				))) {
			if ($info->getId()) {
				$info->delete($this->registry);
			}
			return;
		}
		
		$info->setCustomerId($token_version->getToken()->getCustomerId());
		$info->setName($token_version->getName());
		
		$payment_method = \PostFinanceCheckout\Entity\MethodConfiguration::loadByConfiguration($this->registry, $space_id,
				$token_version->getPaymentConnectorConfiguration()->getPaymentMethodConfiguration()->getId());
		$info->setPaymentMethodId($payment_method->getId());
		$info->setConnectorId($token_version->getPaymentConnectorConfiguration()->getConnector());
		
		$info->setSpaceId($space_id);
		$info->setState($token_version->getToken()->getState());
		$info->setTokenId($token_version->getToken()->getId());
		$info->save();
	}

	public function deleteToken($space_id, $token_id){
		$this->getTokenService()->delete($space_id, $token_id);
	}

	/**
	 * Returns the token API service.
	 *
	 * @return \PostFinanceCheckout\Sdk\Service\TokenService
	 */
	protected function getTokenService(){
		if ($this->token_service == null) {
			$this->token_service = new \PostFinanceCheckout\Sdk\Service\TokenService(\PostFinanceCheckoutHelper::instance($this->registry)->getApiClient());
		}
		
		return $this->token_service;
	}

	/**
	 * Returns the token version API service.
	 *
	 * @return \PostFinanceCheckout\Sdk\Service\TokenVersionService
	 */
	protected function getTokenVersionService(){
		if ($this->token_version_service == null) {
			$this->token_version_service = new \PostFinanceCheckout\Sdk\Service\TokenVersionService(\PostFinanceCheckoutHelper::instance($this->registry)->getApiClient());
		}
		
		return $this->token_version_service;
	}
}