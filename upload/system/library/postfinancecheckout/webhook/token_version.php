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
 * Webhook processor to handle token version state transitions.
 */
class TokenVersion extends AbstractWebhook {

	public function process(Request $request){
		$token_service = \PostFinanceCheckout\Service\Token::instance($this->registry);
		$token_service->updateTokenVersion($request->getSpaceId(), $request->getEntityId());
	}
}