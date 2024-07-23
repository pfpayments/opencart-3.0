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
namespace PostFinanceCheckout\Webhook;

/**
 * Webhook processor to handle payment method configuration state transitions.
 */
class MethodConfiguration extends AbstractWebhook {

	/**
	 * Synchronizes the payment method configurations on state transition.
	 *
	 * @param Request $request
	 */
	public function process(Request $request){
		$payment_method_configuration_service = \PostFinanceCheckout\Service\MethodConfiguration::instance($this->registry);
		$space_id = $this->registry->get('config')->get('postfinancecheckout_space_id');
		$payment_method_configuration_service->synchronize($space_id);
	}
}