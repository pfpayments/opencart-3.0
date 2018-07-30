<?php
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