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
 * This service provides functions to deal with PostFinanceCheckout completions.
 */
class Completion extends AbstractJob {

	public function create(\PostFinanceCheckout\Entity\TransactionInfo $transaction_info){
		try {
			\PostFinanceCheckoutHelper::instance($this->registry)->dbTransactionStart();
			\PostFinanceCheckoutHelper::instance($this->registry)->dbTransactionLock($transaction_info->getSpaceId(), $transaction_info->getTransactionId());
			
			$job = \PostFinanceCheckout\Entity\CompletionJob::loadNotSentForOrder($this->registry, $transaction_info->getOrderId());
			if (!$job->getId()) {
				$job = $this->createBase($transaction_info, $job);
				$job->save();
			}
			
			\PostFinanceCheckoutHelper::instance($this->registry)->dbTransactionCommit();
		}
		catch (\Exception $e) {
			\PostFinanceCheckoutHelper::instance($this->registry)->dbTransactionRollback();
			throw $e;
		}
		
		return $job;
	}

	public function send(\PostFinanceCheckout\Entity\CompletionJob $job){
		try {
			\PostFinanceCheckoutHelper::instance($this->registry)->dbTransactionStart();
			\PostFinanceCheckoutHelper::instance($this->registry)->dbTransactionLock($job->getSpaceId(), $job->getTransactionId());
			
			$service = new \PostFinanceCheckout\Sdk\Service\TransactionCompletionService(\PostFinanceCheckoutHelper::instance($this->registry)->getApiClient());
			$operation = $service->completeOnline($job->getSpaceId(), $job->getTransactionId());
			
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
			return $this->handleApiException($job, $api_exception);
		}
		catch (\Exception $e) {
			\PostFinanceCheckoutHelper::instance($this->registry)->dbTransactionRollback();
			throw $e;
		}
	}
}