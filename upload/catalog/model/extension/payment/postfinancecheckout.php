<?php

/**
 * Prevents loading inexisting model, but allow plugin status to be correct
 */
class ModelExtensionPaymentPostFinanceCheckout extends Model {

	public function getMethod($address, $total){
		return array();
	}
}