<?php

namespace PostFinanceCheckout\Service;

/**
 * This service provides functions to deal with jobs, including locking and setting states.
 */
abstract class AbstractJob extends AbstractService {

	/**
	 * Set the state of the given job to failed with the message of the api exception.
	 * Expects a database transaction to be running, and will commit / rollback depending on outcome.
	 * 
	 * @param \PostFinanceCheckout\Entity\AbstractJob $job
	 * @param \PostFinanceCheckout\Sdk\ApiException $api_exception
	 * @throws \Exception
	 * @return \PostFinanceCheckout\Service\AbstractJob
	 */
	protected function handleApiException(\PostFinanceCheckout\Entity\AbstractJob $job, \PostFinanceCheckout\Sdk\ApiException $api_exception){
		try {
			$job->setState(\PostFinanceCheckout\Entity\AbstractJob::STATE_FAILED_CHECK);
			$job->setFailureReason([
				\PostFinanceCheckoutHelper::FALLBACK_LANGUAGE => $api_exception->getMessage() 
			]);
			$job->save();
			\PostFinanceCheckoutHelper::instance($this->registry)->dbTransactionCommit();
			return $job;
		}
		catch (\Exception $e) {
			\PostFinanceCheckoutHelper::instance($this->registry)->dbTransactionRollback();
			throw new \Exception($e->getMessage() . ' | ' . $api_exception->getMessage(), $e->getCode(), $api_exception);
		}
	}

	protected function createBase(\PostFinanceCheckout\Entity\TransactionInfo $transaction_info, \PostFinanceCheckout\Entity\AbstractJob $job){
		$job->setTransactionId($transaction_info->getTransactionId());
		$job->setOrderId($transaction_info->getOrderId());
		$job->setSpaceId($transaction_info->getSpaceId());
		$job->setState(\PostFinanceCheckout\Entity\AbstractJob::STATE_CREATED);
		
		return $job;
	}
}