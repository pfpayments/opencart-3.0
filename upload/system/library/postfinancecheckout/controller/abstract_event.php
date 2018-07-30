<?php

namespace PostFinanceCheckout\Controller;

abstract class AbstractEvent extends AbstractController {
	
	protected function validateOrder(){
		// no order required
	}
}