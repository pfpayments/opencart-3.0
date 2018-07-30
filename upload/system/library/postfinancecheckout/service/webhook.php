<?php

namespace PostFinanceCheckout\Service;

use PostFinanceCheckout\Webhook\Entity;

/**
 * This service handles webhooks.
 */
class Webhook extends AbstractService {
	
	/**
	 * The webhook listener API service.
	 *
	 * @var \Wallee\Sdk\Service\WebhookListenerService
	 */
	private $webhook_listener_service;
	
	/**
	 * The webhook url API service.
	 *
	 * @var \Wallee\Sdk\Service\WebhookUrlService
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
					\Wallee\Sdk\Model\ManualTaskState::DONE,
					\Wallee\Sdk\Model\ManualTaskState::EXPIRED,
					\Wallee\Sdk\Model\ManualTaskState::OPEN 
				), 'PostFinanceCheckout\Webhook\ManualTask');
		$this->webhook_entities[1472041857405] = new Entity(1472041857405, 'Payment Method Configuration',
				array(
					\Wallee\Sdk\Model\CreationEntityState::ACTIVE,
					\Wallee\Sdk\Model\CreationEntityState::DELETED,
					\Wallee\Sdk\Model\CreationEntityState::DELETING,
					\Wallee\Sdk\Model\CreationEntityState::INACTIVE 
				), 'PostFinanceCheckout\Webhook\MethodConfiguration', true);
		$this->webhook_entities[1472041829003] = new Entity(1472041829003, 'Transaction',
				array(
					\Wallee\Sdk\Model\TransactionState::CONFIRMED,
					\Wallee\Sdk\Model\TransactionState::AUTHORIZED,
					\Wallee\Sdk\Model\TransactionState::DECLINE,
					\Wallee\Sdk\Model\TransactionState::FAILED,
					\Wallee\Sdk\Model\TransactionState::FULFILL,
					\Wallee\Sdk\Model\TransactionState::VOIDED,
					\Wallee\Sdk\Model\TransactionState::COMPLETED,
					\Wallee\Sdk\Model\TransactionState::PROCESSING 
				), 'PostFinanceCheckout\Webhook\Transaction');
		$this->webhook_entities[1472041819799] = new Entity(1472041819799, 'Delivery Indication',
				array(
					\Wallee\Sdk\Model\DeliveryIndicationState::MANUAL_CHECK_REQUIRED 
				), 'PostFinanceCheckout\Webhook\DeliveryIndication');
		
		$this->webhook_entities[1472041831364] = new Entity(1472041831364, 'Transaction Completion',
				array(
					\Wallee\Sdk\Model\TransactionCompletionState::FAILED,
					\Wallee\Sdk\Model\TransactionCompletionState::SUCCESSFUL 
				), 'PostFinanceCheckout\Webhook\TransactionCompletion');
		
		$this->webhook_entities[1472041867364] = new Entity(1472041867364, 'Transaction Void',
				array(
					\Wallee\Sdk\Model\TransactionVoidState::FAILED,
					\Wallee\Sdk\Model\TransactionVoidState::SUCCESSFUL 
				), 'PostFinanceCheckout\Webhook\TransactionVoid');
		
		$this->webhook_entities[1472041839405] = new Entity(1472041839405, 'Refund',
				array(
					\Wallee\Sdk\Model\RefundState::FAILED,
					\Wallee\Sdk\Model\RefundState::SUCCESSFUL 
				), 'PostFinanceCheckout\Webhook\TransactionRefund');
		$this->webhook_entities[1472041806455] = new Entity(1472041806455, 'Token',
				array(
					\Wallee\Sdk\Model\CreationEntityState::ACTIVE,
					\Wallee\Sdk\Model\CreationEntityState::DELETED,
					\Wallee\Sdk\Model\CreationEntityState::DELETING,
					\Wallee\Sdk\Model\CreationEntityState::INACTIVE 
				), 'PostFinanceCheckout\Webhook\Token');
		$this->webhook_entities[1472041811051] = new Entity(1472041811051, 'Token Version',
				array(
					\Wallee\Sdk\Model\TokenVersionState::ACTIVE,
					\Wallee\Sdk\Model\TokenVersionState::OBSOLETE 
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
	 * @param \Wallee\Sdk\Model\WebhookUrl $webhook_url
	 * @return \Wallee\Sdk\Model\WebhookListenerCreate
	 */
	protected function createWebhookListener(Entity $entity, $space_id, \Wallee\Sdk\Model\WebhookUrl $webhook_url){
		$webhook_listener = new \Wallee\Sdk\Model\WebhookListenerCreate();
		$webhook_listener->setEntity($entity->getId());
		$webhook_listener->setEntityStates($entity->getStates());
		$webhook_listener->setName('Opencart ' . $entity->getName());
		$webhook_listener->setState(\Wallee\Sdk\Model\CreationEntityState::ACTIVE);
		$webhook_listener->setUrl($webhook_url->getId());
		$webhook_listener->setNotifyEveryChange($entity->isNotifyEveryChange());
		return $this->getWebhookListenerService()->create($space_id, $webhook_listener);
	}

	/**
	 * Returns the existing webhook listeners.
	 *
	 * @param int $space_id
	 * @param \Wallee\Sdk\Model\WebhookUrl $webhook_url
	 * @return \Wallee\Sdk\Model\WebhookListener[]
	 */
	protected function getWebhookListeners($space_id, \Wallee\Sdk\Model\WebhookUrl $webhook_url){
		$query = new \Wallee\Sdk\Model\EntityQuery();
		$filter = new \Wallee\Sdk\Model\EntityQueryFilter();
		$filter->setType(\Wallee\Sdk\Model\EntityQueryFilterType::_AND);
		$filter->setChildren(
				array(
					$this->createEntityFilter('state', \Wallee\Sdk\Model\CreationEntityState::ACTIVE),
					$this->createEntityFilter('url.id', $webhook_url->getId()) 
				));
		$query->setFilter($filter);
		return $this->getWebhookListenerService()->search($space_id, $query);
	}

	/**
	 * Creates a webhook url.
	 *
	 * @param int $space_id
	 * @return \Wallee\Sdk\Model\WebhookUrlCreate
	 */
	protected function createWebhookUrl($space_id){
		$webhook_url = new \Wallee\Sdk\Model\WebhookUrlCreate();
		$webhook_url->setUrl($this->getUrl());
		$webhook_url->setState(\Wallee\Sdk\Model\CreationEntityState::ACTIVE);
		$webhook_url->setName('Opencart');
		return $this->getWebhookUrlService()->create($space_id, $webhook_url);
	}

	/**
	 * Returns the existing webhook url if there is one.
	 *
	 * @param int $space_id
	 * @return \Wallee\Sdk\Model\WebhookUrl
	 */
	protected function getWebhookUrl($space_id, $url){
		$query = new \Wallee\Sdk\Model\EntityQuery();
		$query->setNumberOfEntities(1);
		$filter = new \Wallee\Sdk\Model\EntityQueryFilter();
		$filter->setType(\Wallee\Sdk\Model\EntityQueryFilterType::_AND);
		$filter->setChildren(
				array(
					$this->createEntityFilter('state', \Wallee\Sdk\Model\CreationEntityState::ACTIVE),
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
	 * @return \Wallee\Sdk\Service\WebhookListenerService
	 */
	protected function getWebhookListenerService(){
		if ($this->webhook_listener_service == null) {
			$this->webhook_listener_service = new \Wallee\Sdk\Service\WebhookListenerService(\PostFinanceCheckoutHelper::instance($this->registry)->getApiClient());
		}
		return $this->webhook_listener_service;
	}

	/**
	 * Returns the webhook url API service.
	 *
	 * @return \Wallee\Sdk\Service\WebhookUrlService
	 */
	protected function getWebhookUrlService(){
		if ($this->webhook_url_service == null) {
			$this->webhook_url_service = new \Wallee\Sdk\Service\WebhookUrlService(\PostFinanceCheckoutHelper::instance($this->registry)->getApiClient());
		}
		return $this->webhook_url_service;
	}
}