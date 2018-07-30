<?php

namespace PostFinanceCheckout\Webhook;

/**
 * Webhook processor to handle transaction void state transitions.
 */
class TransactionVoid extends AbstractOrderRelated {

	/**
	 *
	 * @see AbstractOrderRelated::loadEntity()
	 * @return \Wallee\Sdk\Model\TransactionVoid
	 */
	protected function loadEntity(Request $request){
		$void_service = new \Wallee\Sdk\Service\TransactionVoidService(\PostFinanceCheckoutHelper::instance($this->registry)->getApiClient());
		return $void_service->read($request->getSpaceId(), $request->getEntityId());
	}

	protected function getOrderId($void){
		/* @var \Wallee\Sdk\Model\TransactionVoid $void */
		return $void->getTransaction()->getMerchantReference();
	}
	
	protected function getTransactionId($entity){
		/* @var $entity \Wallee\Sdk\Model\TransactionVoid */
		return $entity->getTransaction()->getId();
	}

	protected function processOrderRelatedInner(array $order_info, $void){
		/* @var \Wallee\Sdk\Model\TransactionVoid $void */
		switch ($void->getState()) {
			case \Wallee\Sdk\Model\TransactionVoidState::FAILED:
				$this->failed($void, $order_info);
				break;
			case \Wallee\Sdk\Model\TransactionVoidState::SUCCESSFUL:
				$this->success($void, $order_info);
				break;
			default:
				// Nothing to do.
				break;
		}
	}

	protected function success(\Wallee\Sdk\Model\TransactionVoid $void, array $order_info){
		$void_job = \PostFinanceCheckout\Entity\VoidJob::loadByJob($this->registry, $void->getLinkedSpaceId(), $void->getId());
		if (!$void_job->getId()) {
			//We have no void job with this id -> the server could not store the id of the void after sending the request. (e.g. connection issue or crash)
			//We only have on running void which was not yet processed successfully and use it as it should be the one the webhook is for.
			$void_job = \PostFinanceCheckout\Entity\VoidJob::loadRunningForOrder($this->registry, $order_info['order_id']);
			if (!$void_job->getId()) {
				//void not initated in shop backend ignore
				return;
			}
			$void_job->setJobId($void->getId());
		}
		$void_job->setState(\PostFinanceCheckout\Entity\VoidJob::STATE_SUCCESS);
		
		$void_job->save();
	}

	protected function failed(\Wallee\Sdk\Model\TransactionVoid $void, array $order_info){
		$void_job = \PostFinanceCheckout\Entity\VoidJob::loadByJob($this->registry, $void->getLinkedSpaceId(), $void->getId());
		if (!$void_job->getId()) {
			//We have no void job with this id -> the server could not store the id of the void after sending the request. (e.g. connection issue or crash)
			//We only have on running void which was not yet processed successfully and use it as it should be the one the webhook is for.
			$void_job = \PostFinanceCheckout\Entity\VoidJob::loadRunningForOrder($this->registry, $order_info['order_id']);
			if (!$void_job->getId()) {
				//void not initated in shop backend ignore
				return;
			}
			$void_job->setJobId($void->getId());
		}
		if ($void->getFailureReason() != null) {
			$void_job->setFailureReason($void->getFailureReason()->getDescription());
		}
		$void_job->setState(\PostFinanceCheckout\Entity\VoidJob::STATE_FAILED_CHECK);
		\PostFinanceCheckout\Entity\Alert::loadFailedJobs($this->registry)->modifyCount(1);
		
		$void_job->save();
	}
}