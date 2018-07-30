<?php

namespace PostFinanceCheckout\Webhook;

/**
 * Webhook request.
 */
class Request {
	private $event_id;
	private $entity_id;
	private $listener_entity_id;
	private $listener_entity_technical_name;
	private $space_id;
	private $webhook_listener_id;
	private $timestamp;

	/**
	 * Constructor.
	 *
	 * @param \stdClass $model
	 */
	public function __construct($model){
		$this->event_id = $model->eventId;
		$this->entity_id = $model->entityId;
		$this->listener_entity_id = $model->listenerEntityId;
		$this->listener_entity_technical_name = $model->listenerEntityTechnicalName;
		$this->space_id = $model->spaceId;
		$this->webhook_listener_id = $model->webhookListenerId;
		$this->timestamp = $model->timestamp;
	}

	/**
	 * Returns the webhook event's id.
	 *
	 * @return int
	 */
	public function getEventId(){
		return $this->event_id;
	}

	/**
	 * Returns the id of the webhook event's entity.
	 *
	 * @return int
	 */
	public function getEntityId(){
		return $this->entity_id;
	}

	/**
	 * Returns the id of the webhook's listener entity.
	 *
	 * @return int
	 */
	public function getListenerEntityId(){
		return $this->listener_entity_id;
	}

	/**
	 * Returns the technical name of the webhook's listener entity.
	 *
	 * @return string
	 */
	public function getListenerEntityTechnicalName(){
		return $this->listener_entity_technical_name;
	}

	/**
	 * Returns the space id.
	 *
	 * @return int
	 */
	public function getSpaceId(){
		return $this->space_id;
	}

	/**
	 * Returns the id of the webhook listener.
	 *
	 * @return int
	 */
	public function getWebhookListenerId(){
		return $this->webhook_listener_id;
	}

	/**
	 * Returns the webhook's timestamp.
	 *
	 * @return string
	 */
	public function getTimestamp(){
		return $this->timestamp;
	}
}