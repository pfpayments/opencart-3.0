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
use PostFinanceCheckout\Model\AbstractModel;

/**
 * Handles the customer order info.
 */
class ModelExtensionPostFinanceCheckoutOrder extends AbstractModel {

	public function getButtons($order_id){
		if (!\PostFinanceCheckoutHelper::instance($this->registry)->isValidOrder($order_id)) {
			return array();
		}
		
		$this->language->load('extension/payment/postfinancecheckout');
		$transaction_info = \PostFinanceCheckout\Entity\TransactionInfo::loadByOrderId($this->registry, $order_id);
		
		$buttons = array();
		
		if ($this->config->get('postfinancecheckout_download_packaging') && $transaction_info->getState() == \PostFinanceCheckout\Sdk\Model\TransactionState::FULFILL) {
			$buttons[] = $this->getPackagingButton();
		}
		
		if ($this->config->get('postfinancecheckout_download_invoice') && in_array($transaction_info->getState(),
				array(
					\PostFinanceCheckout\Sdk\Model\TransactionState::FULFILL,
					\PostFinanceCheckout\Sdk\Model\TransactionState::COMPLETED,
					\PostFinanceCheckout\Sdk\Model\TransactionState::DECLINE 
				))) {
			$buttons[] = $this->getInvoiceButton();
		}
		
		return $buttons;
	}

	private function getInvoiceButton(){
		return array(
			'text' => $this->language->get('button_invoice'),
			'icon' => 'download',
			'url' => $this->createUrl('extension/postfinancecheckout/pdf/invoice', array(
				'order_id' => $this->request->get['order_id'] 
			)) 
		);
	}

	private function getPackagingButton(){
		return array(
			'text' => $this->language->get('button_packing_slip'),
			'icon' => 'download',
			'url' => $this->createUrl('extension/postfinancecheckout/pdf/packingSlip', array(
				'order_id' => $this->request->get['order_id'] 
			)) 
		);
	}
}