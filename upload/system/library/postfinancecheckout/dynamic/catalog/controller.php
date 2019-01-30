<?php
require_once (DIR_SYSTEM . "library/postfinancecheckout/helper.php");
use \PostFinanceCheckout\Controller\AbstractController;

abstract class ControllerExtensionPaymentPostFinanceCheckoutBase extends AbstractController {

	public function index(){
		if (!$this->config->get('postfinancecheckout_status')) {
			return '';
		}
		$this->load->language('extension/payment/postfinancecheckout');
		
		$data['configuration_id'] = substr($this->getCode(), strlen('postfinancecheckout_'));
		
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
			$result['message'] = $e->getMessage();
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($result));
	}

	private function confirmTransaction(){
		$transaction = PostFinanceCheckout\Service\Transaction::instance($this->registry)->getTransaction(array(), false,
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

	protected function getRequiredPermission(){
		return '';
	}

	protected abstract function getCode();
}