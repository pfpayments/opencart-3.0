<?php

namespace PostFinanceCheckout\Service;

/**
 * This service provides functions to deal with PostFinanceCheckout voids.
 */
class VoidJob extends AbstractJob {

	public function create(\PostFinanceCheckout\Entity\TransactionInfo $transaction_info){
		try {
			\PostFinanceCheckoutHelper::instance($this->registry)->dbTransactionStart();
			\PostFinanceCheckoutHelper::instance($this->registry)->dbTransactionLock($transaction_info->getSpaceId(), $transaction_info->getTransactionId());
			
			$job = \PostFinanceCheckout\Entity\VoidJob::loadNotSentForOrder($this->registry, $transaction_info->getOrderId());
			if (!$job->getId()) {
				$job = $this->createBase($transaction_info, $job);
				$job->save();
			}
			
			\PostFinanceCheckoutHelper::instance($this->registry)->dbTransactionCommit();
			return $job;
		}
		catch (\Exception $e) {
			\PostFinanceCheckoutHelper::instance($this->registry)->dbTransactionRollback();
			throw $e;
		}
	}

	public function send(\PostFinanceCheckout\Entity\VoidJob $job){
		try {
			\PostFinanceCheckoutHelper::instance($this->registry)->dbTransactionStart();
			\PostFinanceCheckoutHelper::instance($this->registry)->dbTransactionLock($job->getSpaceId(), $job->getTransactionId());
			
			$service = new \PostFinanceCheckout\Sdk\Service\TransactionVoidService(\PostFinanceCheckoutHelper::instance($this->registry)->getApiClient());
			$operation = $service->voidOnline($job->getSpaceId(), $job->getTransactionId());
			
			if ($operation->getFailureReason() != null) {
				$job->setFailureReason($operation->getFailureReason()->getDescription());
			}
			
			$labels = array();
			foreach ($operation->getLabels() as $label) {
				$labels[$label->getDescriptor()->getId()] = $label->getContentAsString();
			}
			$job->setLabels($labels);
			
			$job->setJobId($operation->getId());
			$job->setState(\PostFinanceCheckout\Entity\AbstractJob::STATE_SENT);
			$job->save();
			
			\PostFinanceCheckoutHelper::instance($this->registry)->dbTransactionCommit();
			return $job;
		}
		catch (\PostFinanceCheckout\Sdk\ApiException $api_exception) {
		}
		catch (\Exception $e) {
			\PostFinanceCheckoutHelper::instance($this->registry)->dbTransactionRollback();
			throw $e;
		}
		return $this->handleApiException($hob, $api_exception);
	}
}