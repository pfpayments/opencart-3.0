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
use PostFinanceCheckout\Controller\AbstractController;

class ControllerExtensionPostFinanceCheckoutTransaction extends AbstractController {

	public function fail(){
		if (isset($this->request->get['order_id']) &&
				 \PostFinanceCheckout\Service\Transaction::instance($this->registry)->waitForStates($this->request->get['order_id'],
						array(
							\PostFinanceCheckout\Sdk\Model\TransactionState::FAILED 
						), 5)) {
			$transaction_info = \PostFinanceCheckout\Entity\TransactionInfo::loadByOrderId($this->registry, $this->request->get['order_id']);
			unset($this->registry->get('session')->data['order_id']);
			$this->session->data['error'] = $transaction_info->getFailureReason();
		}
		else {
			$this->session->data['error'] = $this->language->get('error'); //TODO error text
		}
		$this->response->redirect($this->createUrl('checkout/checkout', ''));
	}

	protected function getRequiredPermission(){
		return '';
	}
}