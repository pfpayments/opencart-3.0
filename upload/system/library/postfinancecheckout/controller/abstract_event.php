<?php

namespace PostFinanceCheckout\Controller;

abstract class AbstractEvent extends AbstractController {

	protected function validate(){
		$this->language->load('extension/payment/postfinancecheckout');
		$this->validatePermission();
		// skip valdiating order.
	}
}