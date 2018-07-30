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
			$service = \PostFinanceCheckout\Service\Transaction::instance($this->registry);
			
			$transaction = $service->getTransaction(array(), false, array(
				\Wallee\Sdk\Model\TransactionState::PENDING 
			));
			if ($transaction->getState() === \Wallee\Sdk\Model\TransactionState::PENDING) {
				\PostFinanceCheckoutHelper::instance($this->registry)->dbTransactionStart();
				\PostFinanceCheckoutHelper::instance($this->registry)->dbTransactionLock($transaction->getLinkedSpaceId(), $transaction->getId());
				$service->update($this->session->data, true);
				\PostFinanceCheckoutHelper::instance($this->registry)->dbTransactionCommit();
				$result['status'] = true;
			}
			else {
				throw new Exception('Transaction is not pending.');
			}
		}
		catch (Exception $e) {
			\PostFinanceCheckoutHelper::instance($this->registry)->dbTransactionRollback();
			$result['message'] = $e->getMessage();
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($result));
	}

	protected function getRequiredPermission(){
		return '';
	}

	protected abstract function getCode();
}