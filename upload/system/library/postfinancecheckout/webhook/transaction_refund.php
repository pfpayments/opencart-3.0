<?php

namespace PostFinanceCheckout\Webhook;

/**
 * Webhook processor to handle refund state transitions.
 */
class TransactionRefund extends AbstractOrderRelated {

	/**
	 *
	 * @see AbstractOrderRelated::load_entity()
	 * @return \PostFinanceCheckout\Sdk\Model\Refund
	 */
	protected function loadEntity(Request $request){
		$refund_service = new \PostFinanceCheckout\Sdk\Service\RefundService(\PostFinanceCheckoutHelper::instance($this->registry)->getApiClient());
		return $refund_service->read($request->getSpaceId(), $request->getEntityId());
	}

	protected function getOrderId($refund){
		/* @var \PostFinanceCheckout\Sdk\Model\Refund $refund */
		return $refund->getTransaction()->getMerchantReference();
	}
	
	protected function getTransactionId($entity){
		/* @var $entity \PostFinanceCheckout\Sdk\Model\Refund */
		return $entity->getTransaction()->getId();
	}

	protected function processOrderRelatedInner(array $order_info, $refund){
		/* @var \PostFinanceCheckout\Sdk\Model\Refund $refund */
		switch ($refund->getState()) {
			case \PostFinanceCheckout\Sdk\Model\RefundState::FAILED:
				$this->failed($refund, $order_info);
				break;
			case \PostFinanceCheckout\Sdk\Model\RefundState::SUCCESSFUL:
				$this->refunded($refund, $order_info);
			default:
				// Nothing to do.
				break;
		}
	}

	protected function failed(\PostFinanceCheckout\Sdk\Model\Refund $refund, array $order_info){
		$refund_job = \PostFinanceCheckout\Entity\RefundJob::loadByExternalId($this->registry, $refund->getLinkedSpaceId(), $refund->getExternalId());
		
		if ($refund_job->getId()) {
			if ($refund->getFailureReason() != null) {
				$refund_job->setFailureReason($refund->getFailureReason()->getDescription());
			}
			
			$refund_job->setState(\PostFinanceCheckout\Entity\RefundJob::STATE_FAILED_CHECK);
			\PostFinanceCheckout\Entity\Alert::loadFailedJobs($this->registry)->modifyCount(1);
			
			$refund_job->save();
		}
	}

	protected function refunded(\PostFinanceCheckout\Sdk\Model\Refund $refund, array $order_info){
		$refund_job = \PostFinanceCheckout\Entity\RefundJob::loadByExternalId($this->registry, $refund->getLinkedSpaceId(), $refund->getExternalId());
		if ($refund_job->getId()) {
			$refund_job->setState(\PostFinanceCheckout\Entity\RefundJob::STATE_SUCCESS);
			$already_refunded = \PostFinanceCheckout\Entity\RefundJob::sumRefundedAmount($this->registry, $order_info['order_id']);
			
			if (\PostFinanceCheckoutHelper::instance($this->registry)->areAmountsEqual($already_refunded + $refund->getAmount(), $order_info['total'],
					$order_info['currency_code'])) {
				$status = 'postfinancecheckout_refund_status_id';
			}
			else {
				$status = $order_info['order_status_id'];
			}
			
			\PostFinanceCheckoutHelper::instance($this->registry)->addOrderHistory($order_info['order_id'], $status,
					sprintf(\PostFinanceCheckoutHelper::instance($this->registry)->getTranslation('message_refund_successful'), $refund->getId(),
							$refund->getAmount()));
			
			if ($refund_job->getRestock()) {
				$this->restock($refund);
			}
			
			$refund_job->save();
		}
	}

	protected function restock(\PostFinanceCheckout\Sdk\Model\Refund $refund){
		$db = $this->registry->get('db');
		$table = DB_PREFIX . 'product';
		foreach ($refund->getLineItems() as $line_item) {
			if ($line_item->getType() == \PostFinanceCheckout\Sdk\Model\LineItemType::PRODUCT) {
				$quantity = $db->escape($line_item->getQuantity());
				$id = $db->escape($line_item->getUniqueId());
				$query = "UPDATE $table SET quantity=quantity+$quantity WHERE product_id='$id';";
				$db->query($query);
			}
		}
	}
}