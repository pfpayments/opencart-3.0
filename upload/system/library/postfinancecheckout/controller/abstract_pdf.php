<?php

namespace PostFinanceCheckout\Controller;

abstract class AbstractPdf extends AbstractController {

	protected function downloadPackingSlip($order_id){
		$transaction_info = \PostFinanceCheckout\Entity\TransactionInfo::loadByOrderId($this->registry, $order_id);
		if ($transaction_info->getId() != null && $transaction_info->getState() == \PostFinanceCheckout\Sdk\Model\TransactionState::FULFILL) {
			$service = new \PostFinanceCheckout\Sdk\Service\TransactionService(\PostFinanceCheckoutHelper::instance($this->registry)->getApiClient());
			$document = $service->getPackingSlip($transaction_info->getSpaceId(), $transaction_info->getTransactionId());
			$this->download($document);
		}
	}

	protected function downloadInvoice($order_id){
		$transaction_info = \PostFinanceCheckout\Entity\TransactionInfo::loadByOrderId($this->registry, $order_id);
		if ($transaction_info->getId() != null && in_array($transaction_info->getState(),
				array(
					\PostFinanceCheckout\Sdk\Model\TransactionState::COMPLETED,
					\PostFinanceCheckout\Sdk\Model\TransactionState::FULFILL,
					\PostFinanceCheckout\Sdk\Model\TransactionState::DECLINE 
				))) {
					$service = new \PostFinanceCheckout\Sdk\Service\TransactionService(\PostFinanceCheckoutHelper::instance($this->registry)->getApiClient());
			$document = $service->getInvoiceDocument($transaction_info->getSpaceId(), $transaction_info->getTransactionId());
			$this->download($document);
		}
	}

	/**
	 * Sends the data received by calling the given path to the browser and ends the execution of the script
	 *
	 * @param string $path
	 */
	private function download(\PostFinanceCheckout\Sdk\Model\RenderedDocument $document){
		header('Pragma: public');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-type: application/pdf');
		header('Content-Disposition: attachment; filename="' . $document->getTitle() . '.pdf"');
		header('Content-Description: ' . $document->getTitle());
		echo base64_decode($document->getData());
		exit();
	}
}