<?php

namespace PostFinanceCheckout\Service;

/**
 * This service provides functions to deal with PostFinanceCheckout refunds.
 */
class Refund extends AbstractJob {

	private function getExternalRefundId(\PostFinanceCheckout\Entity\TransactionInfo $transaction_info){
		$count = \PostFinanceCheckout\Entity\RefundJob::countForOrder($this->registry, $transaction_info->getOrderId());
		return 'r-' . $transaction_info->getOrderId() . '-' . ($count + 1);
	}

	public function create(\PostFinanceCheckout\Entity\TransactionInfo $transaction_info, array $reductions, $restock){
		try {
			\PostFinanceCheckoutHelper::instance($this->registry)->dbTransactionStart();
			\PostFinanceCheckoutHelper::instance($this->registry)->dbTransactionLock($transaction_info->getSpaceId(), $transaction_info->getTransactionId());
			
			$job = \PostFinanceCheckout\Entity\RefundJob::loadNotSentForOrder($this->registry, $transaction_info->getOrderId());
			$reduction_line_items = $this->getLineItemReductions($reductions);
			/* @var $job \PostFinanceCheckout\Entity\RefundJob */
			if (!$job->getId()) {
				$job = $this->createBase($transaction_info, $job);
				$job->setReductionItems($reduction_line_items);
				$job->setRestock($restock);
				$job->setExternalId($this->getExternalRefundId($transaction_info));
				$job->save();
			}
			else if ($job->getReductionItems() != $reduction_line_items) {
				throw new \Exception(\PostFinanceCheckoutHelper::instance($this->registry)->getTranslation('error_already_running'));
			}
			
			\PostFinanceCheckoutHelper::instance($this->registry)->dbTransactionCommit();
		}
		catch (\Exception $e) {
			\PostFinanceCheckoutHelper::instance($this->registry)->dbTransactionRollback();
			throw $e;
		}
		
		return $job;
	}

	public function send(\PostFinanceCheckout\Entity\RefundJob $job){
		try {
			\PostFinanceCheckoutHelper::instance($this->registry)->dbTransactionStart();
			\PostFinanceCheckoutHelper::instance($this->registry)->dbTransactionLock($job->getSpaceId(), $job->getTransactionId());
			
			$service = new \Wallee\Sdk\Service\RefundService(\PostFinanceCheckoutHelper::instance($this->registry)->getApiClient());
			$operation = $service->refund($job->getSpaceId(), $this->createRefund($job));
			
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
			$job->setAmount($operation->getAmount());
			$job->save();
			
			\PostFinanceCheckoutHelper::instance($this->registry)->dbTransactionCommit();
			return $job;
		}
		catch (\Wallee\Sdk\ApiException $api_exception) {
		}
		catch (\Exception $e) {
			\PostFinanceCheckoutHelper::instance($this->registry)->dbTransactionRollback();
			throw $e;
		}
		
		return $this->handleApiException($job, $api_exception);
	}

	private function createRefund(\PostFinanceCheckout\Entity\RefundJob $job){
		$refund_create = new \Wallee\Sdk\Model\RefundCreate();
		$refund_create->setReductions($job->getReductionItems());
		$refund_create->setExternalId($job->getExternalId());
		$refund_create->setTransaction($job->getTransactionId());
		$refund_create->setType(\Wallee\Sdk\Model\RefundType::MERCHANT_INITIATED_ONLINE);
		return $refund_create;
	}

	private function getLineItemReductions(array $reductions){
		$reduction_line_items = array();
		foreach ($reductions as $reduction) {
			if ($reduction['quantity'] || $reduction['unit_price']) {
				$line_item = new \Wallee\Sdk\Model\LineItemReductionCreate();
				$line_item->setLineItemUniqueId($reduction['id']);
				$line_item->setQuantityReduction(floatval($reduction['quantity']));
				$line_item->setUnitPriceReduction(floatval($reduction['unit_price']));
				$reduction_line_items[] = $line_item;
			}
		}
		return $reduction_line_items;
	}
}