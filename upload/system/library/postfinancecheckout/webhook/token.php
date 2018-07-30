<?php

namespace PostFinanceCheckout\Webhook;

/**
 * Webhook processor to handle token state transitions.
 */
class Token extends AbstractWebhook {

	public function process(Request $request){
		$token_service = \PostFinanceCheckout\Service\Token::instance($this->registry);
		$token_service->updateToken($request->getSpaceId(), $request->getEntityId());
	}
}