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

class ControllerExtensionPostFinanceCheckoutPdf extends PostFinanceCheckout\Controller\AbstractPdf {

	public function packingSlip(){
		$this->validate();
		$this->downloadPackingSlip($this->request->get['order_id']);
	}

	public function invoice(){
		$this->validate();
		$this->downloadInvoice($this->request->get['order_id']);
	}

	protected function getRequiredPermission(){
		return 'extension/postfinancecheckout/pdf';
	}
}