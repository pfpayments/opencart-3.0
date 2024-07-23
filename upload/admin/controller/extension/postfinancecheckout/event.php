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

class ControllerExtensionPostFinanceCheckoutEvent extends PostFinanceCheckout\Controller\AbstractEvent {

	/**
	 * Re-Creates required files for display of payment methods.
	 */
	public function createMethodConfigurationFiles(){
		try {
			$this->validate();
			$this->load->model('extension/postfinancecheckout/dynamic');
			$this->model_extension_postfinancecheckout_dynamic->install();
		}
		catch (Exception $e) {
			// ensure that permissions etc. do not cause page loads to fail
			return;
		}
	}

	protected function getRequiredPermission(){
		return 'extension/postfinancecheckout/event';
	}
}