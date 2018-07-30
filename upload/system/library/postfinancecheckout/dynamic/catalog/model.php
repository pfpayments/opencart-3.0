<?php
require_once (DIR_SYSTEM . "library/postfinancecheckout/helper.php");

abstract class ModelExtensionPaymentPostFinanceCheckoutBase extends Model {
	private static $paymentMethods;

	public abstract function getTitle();

	protected abstract function getCode();

	protected abstract function getSortOrder();

	protected abstract function getTerms();

	public function getMethod($address, $total){
		if (!$this->config->get('postfinancecheckout_status')) {
			return array();
		}
		
		// check if transaction can be saved to the session.
		if (\PostFinanceCheckoutHelper::instance($this->registry)->getCustomerSessionIdentifier() === null) {
			return array();
		}
		
		$order_info = array(
			'payment_address' => $address 
		);
		$billing = \PostFinanceCheckoutHelper::instance($this->registry)->getAddress('billing');
		$shipping = \PostFinanceCheckoutHelper::instance($this->registry)->getAddress('payment', $order_info);
		if (empty($billing) && empty($shipping)) {
			return array();
		}
		
		try {
			$available_methods = \PostFinanceCheckout\Service\Transaction::instance($this->registry)->getPaymentMethods($order_info);
			$configuration_id = substr($this->getCode(), strlen('postfinancecheckout_'));
			
			foreach ($available_methods as $method) {
				if ($method->getId() == $configuration_id) {
					return [
						'title' => $this->getTitle(),
						'code' => $this->getCode(),
						'terms' => $this->getTerms(),
						'sort_order' => $this->getSortOrder() 
					];
				}
			}
		}
		catch (Exception $e) {
		}
		return array();
	}
}