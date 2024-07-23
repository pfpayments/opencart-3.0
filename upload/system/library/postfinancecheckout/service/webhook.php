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

use PostFinanceCheckout\Webhook\Entity;

/**
 * This service handles webhooks.
 */
class Webhook extends AbstractService {
	
	/**
	 * The webhook listener API service.
	 *
	 * @var \PostFinanceCheckout\Sdk\Service\WebhookListenerService
	 */
	private $webhook_listener_service;
	
	/**
	 * The webhook url API service.
	 *
	 * @var \PostFinanceCheckout\Sdk\Service\WebhookUrlService
	 */
	private $webhook_url_service;
	private $webhook_entities = array();

	/**
	 * Constructor to register the webhook entites.
	 */
	protected function __construct(\Registry $registry){
		parent::__construct($registry);
		$this->webhook_entities[1487165678181] = new Entity(1487165678181, 'Manual Task',
				array(
					\PostFinanceCheckout\Sdk\Model\ManualTaskState::DONE,
					\PostFinanceCheckout\Sdk\Model\ManualTaskState::EXPIRED,
					\PostFinanceCheckout\Sdk\Model\ManualTaskState::OPEN 
				), 'PostFinanceCheckout\Webhook\ManualTask');
		$this->webhook_entities[1472041857405] = new Entity(1472041857405, 'Payment Method Configuration',
				array(
					\PostFinanceCheckout\Sdk\Model\CreationEntityState::ACTIVE,
					\PostFinanceCheckout\Sdk\Model\CreationEntityState::DELETED,
					\PostFinanceCheckout\Sdk\Model\CreationEntityState::DELETING,
					\PostFinanceCheckout\Sdk\Model\CreationEntityState::INACTIVE 
				), 'PostFinanceCheckout\Webhook\MethodConfiguration', true);
		$this->webhook_entities[1472041829003] = new Entity(1472041829003, 'Transaction',
				array(
					\PostFinanceCheckout\Sdk\Model\TransactionState::CONFIRMED,
					\PostFinanceCheckout\Sdk\Model\TransactionState::AUTHORIZED,
					\PostFinanceCheckout\Sdk\Model\TransactionState::DECLINE,
					\PostFinanceCheckout\Sdk\Model\TransactionState::FAILED,
					\PostFinanceCheckout\Sdk\Model\TransactionState::FULFILL,
					\PostFinanceCheckout\Sdk\Model\TransactionState::VOIDED,
					\PostFinanceCheckout\Sdk\Model\TransactionState::COMPLETED,
					\PostFinanceCheckout\Sdk\Model\TransactionState::PROCESSING 
				), 'PostFinanceCheckout\Webhook\Transaction');
		$this->webhook_entities[1472041819799] = new Entity(1472041819799, 'Delivery Indication',
				array(
					\PostFinanceCheckout\Sdk\Model\DeliveryIndicationState::MANUAL_CHECK_REQUIRED 
				), 'PostFinanceCheckout\Webhook\DeliveryIndication');
		
		$this->webhook_entities[1472041831364] = new Entity(1472041831364, 'Transaction Completion',
				array(
					\PostFinanceCheckout\Sdk\Model\TransactionCompletionState::FAILED,
					\PostFinanceCheckout\Sdk\Model\TransactionCompletionState::SUCCESSFUL 
				), 'PostFinanceCheckout\Webhook\TransactionCompletion');
		
		$this->webhook_entities[1472041867364] = new Entity(1472041867364, 'Transaction Void',
				array(
					\PostFinanceCheckout\Sdk\Model\TransactionVoidState::FAILED,
					\PostFinanceCheckout\Sdk\Model\TransactionVoidState::SUCCESSFUL 
				), 'PostFinanceCheckout\Webhook\TransactionVoid');
		
		$this->webhook_entities[1472041839405] = new Entity(1472041839405, 'Refund',
				array(
					\PostFinanceCheckout\Sdk\Model\RefundState::FAILED,
					\PostFinanceCheckout\Sdk\Model\RefundState::SUCCESSFUL 
				), 'PostFinanceCheckout\Webhook\TransactionRefund');
		$this->webhook_entities[1472041806455] = new Entity(1472041806455, 'Token',
				array(
					\PostFinanceCheckout\Sdk\Model\CreationEntityState::ACTIVE,
					\PostFinanceCheckout\Sdk\Model\CreationEntityState::DELETED,
					\PostFinanceCheckout\Sdk\Model\CreationEntityState::DELETING,
					\PostFinanceCheckout\Sdk\Model\CreationEntityState::INACTIVE 
				), 'PostFinanceCheckout\Webhook\Token');
		$this->webhook_entities[1472041811051] = new Entity(1472041811051, 'Token Version',
				array(
					\PostFinanceCheckout\Sdk\Model\TokenVersionState::ACTIVE,
					\PostFinanceCheckout\Sdk\Model\TokenVersionState::OBSOLETE 
				), 'PostFinanceCheckout\Webhook\TokenVersion');
	}

	/**
	 * Installs the necessary webhooks in PostFinanceCheckout.
	 */
	public function install($space_id, $url){
		if ($space_id !== null && !empty($url)) {
			$webhook_url = $this->getWebhookUrl($space_id, $url);
			if ($webhook_url == null) {
				$webhook_url = $this->createWebhookUrl($space_id, $url);
			}
			$existing_listeners = $this->getWebhookListeners($space_id, $webhook_url);
			foreach ($this->webhook_entities as $webhook_entity) {
				/* @var WC_PostFinanceCheckout_Webhook_Entity $webhook_entity */
				$exists = false;
				foreach ($existing_listeners as $existing_listener) {
					if ($existing_listener->getEntity() == $webhook_entity->getId()) {
						$exists = true;
					}
				}
				if (!$exists) {
					$this->createWebhookListener($webhook_entity, $space_id, $webhook_url);
				}
			}
		}
	}
	
	public function uninstall($space_id, $url) {
		if($space_id !== null && !empty($url)) {
			$webhook_url = $this->getWebhookUrl($space_id, $url);
			if($webhook_url == null) {
				\PostFinanceCheckoutHelper::instance($this->registry)->log("Attempted to uninstall webhooks with URL $url, but was not found");
				return;
			}
			foreach($this->getWebhookListeners($space_id, $webhook_url) as $listener) {
				$this->getWebhookListenerService()->delete($space_id, $listener->getId());
			}
			
			$this->getWebhookUrlService()->delete($space_id, $webhook_url->getId());
		}
	}

	/**
	 *
	 * @param int|string $id
	 * @return Entity
	 */
	public function getWebhookEntityForId($id){
		if (isset($this->webhook_entities[$id])) {
			return $this->webhook_entities[$id];
		}
		return null;
	}

	/**
	 * Create a webhook listener.
	 *
	 * @param Entity $entity
	 * @param int $space_id
	 * @param \PostFinanceCheckout\Sdk\Model\WebhookUrl $webhook_url
	 * @return \PostFinanceCheckout\Sdk\Model\WebhookListenerCreate
	 */
	protected function createWebhookListener(Entity $entity, $space_id, \PostFinanceCheckout\Sdk\Model\WebhookUrl $webhook_url){
		$webhook_listener = new \PostFinanceCheckout\Sdk\Model\WebhookListenerCreate();
		$webhook_listener->setEntity($entity->getId());
		$webhook_listener->setEntityStates($entity->getStates());
		$webhook_listener->setName('Opencart ' . $entity->getName());
		$webhook_listener->setState(\PostFinanceCheckout\Sdk\Model\CreationEntityState::ACTIVE);
		$webhook_listener->setUrl($webhook_url->getId());
		$webhook_listener->setNotifyEveryChange($entity->isNotifyEveryChange());
		return $this->getWebhookListenerService()->create($space_id, $webhook_listener);
	}

	/**
	 * Returns the existing webhook listeners.
	 *
	 * @param int $space_id
	 * @param \PostFinanceCheckout\Sdk\Model\WebhookUrl $webhook_url
	 * @return \PostFinanceCheckout\Sdk\Model\WebhookListener[]
	 */
	protected function getWebhookListeners($space_id, \PostFinanceCheckout\Sdk\Model\WebhookUrl $webhook_url){
		$query = new \PostFinanceCheckout\Sdk\Model\EntityQuery();
		$filter = new \PostFinanceCheckout\Sdk\Model\EntityQueryFilter();
		$filter->setType(\PostFinanceCheckout\Sdk\Model\EntityQueryFilterType::_AND);
		$filter->setChildren(
				array(
					$this->createEntityFilter('state', \PostFinanceCheckout\Sdk\Model\CreationEntityState::ACTIVE),
					$this->createEntityFilter('url.id', $webhook_url->getId()) 
				));
		$query->setFilter($filter);
		return $this->getWebhookListenerService()->search($space_id, $query);
	}

	/**
	 * Creates a webhook url.
	 *
	 * @param int $space_id
	 * @return \PostFinanceCheckout\Sdk\Model\WebhookUrlCreate
	 */
	protected function createWebhookUrl($space_id){
		$webhook_url = new \PostFinanceCheckout\Sdk\Model\WebhookUrlCreate();
		$webhook_url->setUrl($this->getUrl());
		$webhook_url->setState(\PostFinanceCheckout\Sdk\Model\CreationEntityState::ACTIVE);
		$webhook_url->setName('Opencart');
		return $this->getWebhookUrlService()->create($space_id, $webhook_url);
	}

	/**
	 * Returns the existing webhook url if there is one.
	 *
	 * @param int $space_id
	 * @return \PostFinanceCheckout\Sdk\Model\WebhookUrl
	 */
	protected function getWebhookUrl($space_id, $url){
		$query = new \PostFinanceCheckout\Sdk\Model\EntityQuery();
		$query->setNumberOfEntities(1);
		$filter = new \PostFinanceCheckout\Sdk\Model\EntityQueryFilter();
		$filter->setType(\PostFinanceCheckout\Sdk\Model\EntityQueryFilterType::_AND);
		$filter->setChildren(
				array(
					$this->createEntityFilter('state', \PostFinanceCheckout\Sdk\Model\CreationEntityState::ACTIVE),
					$this->createEntityFilter('url', $url)
				));
		$query->setFilter($filter);
		$result = $this->getWebhookUrlService()->search($space_id, $query);
		if (!empty($result)) {
			return $result[0];
		}
		else {
			return null;
		}
	}

	/**
	 * Returns the webhook endpoint URL.
	 *
	 * @return string
	 */
	protected function getUrl(){
		return \PostFinanceCheckoutHelper::instance($this->registry)->getWebhookUrl();
	}

	/**
	 * Returns the webhook listener API service.
	 *
	 * @return \PostFinanceCheckout\Sdk\Service\WebhookListenerService
	 */
	protected function getWebhookListenerService(){
		if ($this->webhook_listener_service == null) {
			$this->webhook_listener_service = new \PostFinanceCheckout\Sdk\Service\WebhookListenerService(\PostFinanceCheckoutHelper::instance($this->registry)->getApiClient());
		}
		return $this->webhook_listener_service;
	}

	/**
	 * Returns the webhook url API service.
	 *
	 * @return \PostFinanceCheckout\Sdk\Service\WebhookUrlService
	 */
	protected function getWebhookUrlService(){
		if ($this->webhook_url_service == null) {
			$this->webhook_url_service = new \PostFinanceCheckout\Sdk\Service\WebhookUrlService(\PostFinanceCheckoutHelper::instance($this->registry)->getApiClient());
		}
		return $this->webhook_url_service;
	}
}