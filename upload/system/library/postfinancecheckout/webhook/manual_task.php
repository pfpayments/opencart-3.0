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
 * Webhook processor to handle manual task state transitions.
 */
class ManualTask extends AbstractWebhook {

	/**
	 * Updates the number of open manual tasks.
	 *
	 * @param \PostFinanceCheckout\Webhook\Request $request
	 */
	public function process(Request $request){
		$manual_task_service = \PostFinanceCheckout\service\ManualTask::instance($this->registry);
		$manual_task_service->update();
	}
}