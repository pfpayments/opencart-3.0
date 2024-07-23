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
require_once modification(DIR_SYSTEM . 'library/postfinancecheckout/helper.php');

class ControllerExtensionPostFinanceCheckoutUpdate extends \PostFinanceCheckout\Controller\AbstractController {

	public function index(){
		$this->response->addHeader('Content-Type: application/json');
		
		try {
			$this->validate();
			
			$message = $this->language->get('message_refresh_success');
			
			$transaction_info = \PostFinanceCheckout\Entity\TransactionInfo::loadByOrderId($this->registry, $this->request->get['order_id']);
			if ($transaction_info->getId() === null) {
				throw new Exception($this->language->get('error_not_postfinancecheckout'));
			}
			
			$completion_job = \PostFinanceCheckout\Entity\CompletionJob::loadNotSentForOrder($this->registry, $this->request->get['order_id']);
			if ($completion_job->getId()) {
				\PostFinanceCheckout\Service\Completion::instance($this->registry)->send($completion_job);
				$message .= '<br/>' . sprintf($this->language->get('message_resend_completion'), $completion_job->getId());
			}
			
			$void_job = \PostFinanceCheckout\Entity\VoidJob::loadNotSentForOrder($this->registry, $this->request->get['order_id']);
			if ($void_job->getId()) {
				\PostFinanceCheckout\Service\VoidJob::instance($this->registry)->send($void_job);
				$message .= '<br/>' . sprintf($this->language->get('message_resend_void'), $void_job->getId());
			}
			
			$refund_job = \PostFinanceCheckout\Entity\RefundJob::loadNotSentForOrder($this->registry, $this->request->get['order_id']);
			if ($refund_job->getId()) {
				\PostFinanceCheckout\Service\Refund::instance($this->registry)->send($refund_job);
				$message .= '<br/>' . sprintf($this->language->get('message_resend_refund'), $refund_job->getId());
			}
			
			$this->load->model('extension/postfinancecheckout/order');
			$new_buttons = $this->model_extension_postfinancecheckout_order->getButtons($this->request->get['order_id']);
			
			$this->response->setOutput(json_encode([
				'success' => $message,
				'buttons' => $new_buttons 
			]));
			return;
		}
		catch (Exception $e) {
			$this->response->setOutput(json_encode([
				'error' => $e->getMessage() 
			]));
		}
	}

	protected function getRequiredPermission(){
		return 'extension/postfinancecheckout/update';
	}
}