<?php
require_once modification(DIR_SYSTEM . 'library/postfinancecheckout/helper.php');

class ControllerExtensionPostFinanceCheckoutCompletion extends \PostFinanceCheckout\Controller\AbstractController {

	public function index(){
		$this->response->addHeader('Content-Type: application/json');
		try {
			$this->validate();
			
			$completion_job = \PostFinanceCheckout\Entity\CompletionJob::loadRunningForOrder($this->registry, $this->request->get['order_id']);
			
			if ($completion_job->getId() !== null) {
				throw new Exception($this->language->get('error_already_running'));
			}
			
			$transaction_info = \PostFinanceCheckout\Entity\TransactionInfo::loadByOrderId($this->registry, $this->request->get['order_id']);
			
			if (!\PostFinanceCheckoutHelper::instance($this->registry)->isCompletionPossible($transaction_info)) {
				throw new \Exception($this->language->get('error_cannot_create_job'));
			}
			
			// ensure line items are current (e.g. events were skipped when order is edited)
			\PostFinanceCheckout\Service\Transaction::instance($this->registry)->updateLineItemsFromOrder($this->request->get['order_id']);
			
			$job = \PostFinanceCheckout\Service\Completion::instance($this->registry)->create($transaction_info);
			\PostFinanceCheckout\Service\Completion::instance($this->registry)->send($job);
			
			$this->load->model('extension/postfinancecheckout/order');
			$new_buttons = $this->model_extension_postfinancecheckout_order->getButtons($this->request->get['order_id']);
			
			$this->response->setOutput(
					json_encode(
							array(
								'success' => sprintf($this->language->get('message_completion_success'), $transaction_info->getTransactionId()),
								'buttons' => $new_buttons 
							)));
		}
		catch (Exception $e) {
			$this->response->setOutput(json_encode(array(
				'error' => $e->getMessage() 
			)));
		}
	}

	protected function getRequiredPermission(){
		return 'extension/postfinancecheckout/completion';
	}
}