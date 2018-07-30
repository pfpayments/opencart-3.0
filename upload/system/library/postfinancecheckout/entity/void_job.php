<?php

namespace PostFinanceCheckout\Entity;

/**
 *
 */
class VoidJob extends AbstractJob {

	protected static function getTableName(){
		return 'postfinancecheckout_void_job';
	}
}