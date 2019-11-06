<?php

namespace PostFinanceCheckout\Webhook;

/**
 * Webhook processor to handle transaction state transitions.
 */
class Transaction extends AbstractOrderRelated {

	/**
	 *
	 * @see AbstractOrderRelated::load_entity()
	 * @return \PostFinanceCheckout\Sdk\Model\Transaction
	 */
	protected function loadEntity(Request $request){
		$transaction_service = new \PostFinanceCheckout\Sdk\Service\TransactionService(\PostFinanceCheckoutHelper::instance($this->registry)->getApiClient());
		return $transaction_service->read($request->getSpaceId(), $request->getEntityId());
	}

	protected function getOrderId($transaction){
		/* @var \PostFinanceCheckout\Sdk\Model\Transaction $transaction */
		return $transaction->getMerchantReference();
	}

	protected function getTransactionId($transaction){
		/* @var \PostFinanceCheckout\Sdk\Model\Transaction $transaction */
		return $transaction->getId();
	}

	protected function processOrderRelatedInner(array $order_info, $transaction){
		/* @var \PostFinanceCheckout\Sdk\Model\Transaction $transaction */
		$transaction_info = \PostFinanceCheckout\Entity\TransactionInfo::loadByOrderId($this->registry, $order_info['order_id']);
		\PostFinanceCheckoutHelper::instance($this->registry)->ensurePaymentCode($order_info, $transaction);
		if ($transaction->getState() != $transaction_info->getState()) {
			switch ($transaction->getState()) {
				case \PostFinanceCheckout\Sdk\Model\TransactionState::CONFIRMED:
					$this->processing($transaction, $order_info);
					break;
				case \PostFinanceCheckout\Sdk\Model\TransactionState::PROCESSING:
					$this->confirm($transaction, $order_info);
					break;
				case \PostFinanceCheckout\Sdk\Model\TransactionState::AUTHORIZED:
					$this->authorize($transaction, $order_info);
					break;
				case \PostFinanceCheckout\Sdk\Model\TransactionState::DECLINE:
					$this->decline($transaction, $order_info);
					break;
				case \PostFinanceCheckout\Sdk\Model\TransactionState::FAILED:
					$this->failed($transaction, $order_info);
					break;
				case \PostFinanceCheckout\Sdk\Model\TransactionState::FULFILL:
					if ($transaction_info->getState() != 'AUTHORIZED' && $transaction_info->getState() != 'COMPLETED') {
						$this->authorize($transaction, $order_info);
					}
					$this->fulfill($transaction, $order_info);
					break;
				case \PostFinanceCheckout\Sdk\Model\TransactionState::VOIDED:
					$this->voided($transaction, $order_info);
					break;
				case \PostFinanceCheckout\Sdk\Model\TransactionState::COMPLETED:
					$this->waiting($transaction, $order_info);
					break;
				default:
					// Nothing to do.
					break;
			}
		}
		
		\PostFinanceCheckout\Service\Transaction::instance($this->registry)->updateTransactionInfo($transaction, $order_info['order_id']);
	}

	protected function processing(\PostFinanceCheckout\Sdk\Model\Transaction $transaction, array $order_info){
		\PostFinanceCheckoutHelper::instance($this->registry)->addOrderHistory($order_info['order_id'], 'postfinancecheckout_processing_status_id',
				\PostFinanceCheckoutHelper::instance($this->registry)->getTranslation('message_webhook_processing'));
	}

	protected function confirm(\PostFinanceCheckout\Sdk\Model\Transaction $transaction, array $order_info){
		\PostFinanceCheckoutHelper::instance($this->registry)->addOrderHistory($order_info['order_id'], 'postfinancecheckout_processing_status_id',
				\PostFinanceCheckoutHelper::instance($this->registry)->getTranslation('message_webhook_confirm'));
	}

	protected function authorize(\PostFinanceCheckout\Sdk\Model\Transaction $transaction, array $order_info){
		\PostFinanceCheckoutHelper::instance($this->registry)->addOrderHistory($order_info['order_id'], 'postfinancecheckout_authorized_status_id',
				\PostFinanceCheckoutHelper::instance($this->registry)->getTranslation('message_webhook_authorize'));
	}

	protected function waiting(\PostFinanceCheckout\Sdk\Model\Transaction $transaction, array $order_info){
		\PostFinanceCheckoutHelper::instance($this->registry)->addOrderHistory($order_info['order_id'], 'postfinancecheckout_completed_status_id',
				\PostFinanceCheckoutHelper::instance($this->registry)->getTranslation('message_webhook_waiting'));
	}

	protected function decline(\PostFinanceCheckout\Sdk\Model\Transaction $transaction, array $order_info){
		\PostFinanceCheckoutHelper::instance($this->registry)->addOrderHistory($order_info['order_id'], 'postfinancecheckout_decline_status_id',
				\PostFinanceCheckoutHelper::instance($this->registry)->getTranslation('message_webhook_decline'));
	}

	protected function failed(\PostFinanceCheckout\Sdk\Model\Transaction $transaction, array $order_info){
		\PostFinanceCheckoutHelper::instance($this->registry)->addOrderHistory($order_info['order_id'], 'postfinancecheckout_failed_status_id',
				\PostFinanceCheckoutHelper::instance($this->registry)->getTranslation('message_webhook_failed'));
	}

	protected function fulfill(\PostFinanceCheckout\Sdk\Model\Transaction $transaction, array $order_info){
		\PostFinanceCheckoutHelper::instance($this->registry)->addOrderHistory($order_info['order_id'], 'postfinancecheckout_fulfill_status_id',
				\PostFinanceCheckoutHelper::instance($this->registry)->getTranslation('message_webhook_fulfill'));
	}

	protected function voided(\PostFinanceCheckout\Sdk\Model\Transaction $transaction, array $order_info){
		\PostFinanceCheckoutHelper::instance($this->registry)->addOrderHistory($order_info['order_id'], 'postfinancecheckout_voided_status_id',
				\PostFinanceCheckoutHelper::instance($this->registry)->getTranslation('message_webhook_voided'));
	}
}