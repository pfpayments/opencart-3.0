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
require_once (DIR_SYSTEM . "library/postfinancecheckout/helper.php");
use \PostFinanceCheckout\Controller\AbstractController;

abstract class ControllerExtensionPaymentPostFinanceCheckoutBase extends AbstractController {

	public function index(){
		if (!$this->config->get('postfinancecheckout_status')) {
			return '';
		}
		$this->load->language('extension/payment/postfinancecheckout');
		$data = array();
		
		$data['configuration_id'] = \PostFinanceCheckoutHelper::extractPaymentMethodId($this->getCode());
		
		$data['button_confirm'] = $this->language->get('button_confirm');
		$data['text_loading'] = $this->language->get('text_loading');
		
		$this->load->model('extension/payment/' . $this->getCode());
		$data['text_payment_title'] = $this->{"model_extension_payment_{$this->getCode()}"}->getTitle();
		$data['text_further_details'] = $this->language->get('text_further_details');
		
		$data['opencart_js'] = 'catalog/view/javascript/postfinancecheckout.js';
		$data['external_js'] = PostFinanceCheckout\Service\Transaction::instance($this->registry)->getJavascriptUrl();
		
		return $this->loadView('extension/payment/postfinancecheckout/iframe', $data);
	}

	public function confirm(){
		if (!$this->config->get('postfinancecheckout_status')) {
			return '';
		}
		$result = array(
			'status' => false 
		);
		try {
			$transaction = $this->confirmTransaction();
			$result['status'] = true;
			$result['redirect'] = PostFinanceCheckout\Service\Transaction::instance($this->registry)->getPaymentPageUrl($transaction, $this->getCode());
		}
		catch (Exception $e) {
			\PostFinanceCheckoutHelper::instance($this->registry)->dbTransactionRollback();
			\PostFinanceCheckoutHelper::instance($this->registry)->log($e->getMessage(), \PostFinanceCheckoutHelper::LOG_ERROR);
			$this->load->language('extension/payment/postfinancecheckout');
			$result['message'] = $this->language->get('error_confirmation'); 
			unset($this->session->data['order_id']); // this order number cannot be used anymore
			PostFinanceCheckout\Service\Transaction::instance($this->registry)->clearTransactionInSession();
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($result));
	}

	private function confirmTransaction(){
		$transaction = PostFinanceCheckout\Service\Transaction::instance($this->registry)->getTransaction($this->getOrderInfo(), false,
				array(
					\PostFinanceCheckout\Sdk\Model\TransactionState::PENDING 
				));
		if ($transaction->getState() == \PostFinanceCheckout\Sdk\Model\TransactionState::PENDING) {
			\PostFinanceCheckoutHelper::instance($this->registry)->dbTransactionStart();
			\PostFinanceCheckoutHelper::instance($this->registry)->dbTransactionLock($transaction->getLinkedSpaceId(), $transaction->getId());
			PostFinanceCheckout\Service\Transaction::instance($this->registry)->update($this->session->data, true);
			\PostFinanceCheckoutHelper::instance($this->registry)->dbTransactionCommit();
			return $transaction;
		}
		
		throw new Exception('Transaction is not pending.');
	}
	
	private function getOrderInfo() {
		if(!isset($this->session->data['order_id'])) {
			throw new Exception("No order_id to confirm.");
		}
		$this->load->model('checkout/order');
		return $this->model_checkout_order->getOrder($this->session->data['order_id']);
	}

	protected function getRequiredPermission(){
		return '';
	}

	protected abstract function getCode();
}