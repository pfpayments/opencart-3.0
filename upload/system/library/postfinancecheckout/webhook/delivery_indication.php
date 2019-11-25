<?php

namespace PostFinanceCheckout\Webhook;

/**
 * Webhook processor to handle delivery indication state transitions.
 */
class DeliveryIndication extends AbstractOrderRelated {

	/**
	 *
	 * @see AbstractOrderRelated::load_entity()
	 * @return \PostFinanceCheckout\Sdk\Model\DeliveryIndication
	 */
	protected function loadEntity(Request $request){
		$delivery_indication_service = new \PostFinanceCheckout\Sdk\Service\DeliveryIndicationService(\PostFinanceCheckoutHelper::instance($this->registry)->getApiClient());
		return $delivery_indication_service->read($request->getSpaceId(), $request->getEntityId());
	}

	protected function getOrderId($delivery_indication){
		/* @var \PostFinanceCheckout\Sdk\Model\DeliveryIndication $delivery_indication */
		return $delivery_indication->getTransaction()->getMerchantReference();
	}

	protected function getTransactionId($delivery_indication){
		/* @var $delivery_indication \PostFinanceCheckout\Sdk\Model\DeliveryIndication */
		return $delivery_indication->getLinkedTransaction();
	}

	protected function processOrderRelatedInner(array $order_info, $delivery_indication){
		/* @var \PostFinanceCheckout\Sdk\Model\DeliveryIndication $delivery_indication */
		switch ($delivery_indication->getState()) {
			case \PostFinanceCheckout\Sdk\Model\DeliveryIndicationState::MANUAL_CHECK_REQUIRED:
				$this->review($order_info);
				break;
			default:
				// Nothing to do.
				break;
		}
	}

	protected function review(array $order_info){
		\PostFinanceCheckoutHelper::instance($this->registry)->addOrderHistory($order_info['order_id'], $order_info['order_status_id'],
				\PostFinanceCheckoutHelper::instance($this->registry)->getTranslation('message_webhook_manual'), true);
	}
}