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

namespace PostFinanceCheckout\Entity;

/**
 *
 * @method string getRoute()
 * @method string getKey()
 * @method string getType()
 * @method integer getCount()
 * @method void setCount(integer $count)
 *
 */
class Alert extends AbstractEntity {
	const KEY_MANUAL_TASK = 'manual_task';
	const KEY_FAILED_JOB = 'failed_jobs';

	protected static function getTableName(){
		return 'postfinancecheckout_alert';
	}

	protected static function getFieldDefinition(){
		return array(
			'key' => ResourceType::STRING,
			'route' => ResourceType::STRING,
			'level' => ResourceType::STRING,
			'count' => ResourceType::INTEGER 
		);
	}

	/**
	 * Modifies the entities count by the given parameter.
	 * The parameter may be negative or positive.
	 *
	 * @param int $count
	 */
	public function modifyCount($count){
		$new_count = $this->getCount() + $count;
		if ($new_count < 0) {
			$new_count = 0;
		}
		$this->setCount($new_count);
		$this->save();
	}

	public static function loadManualTask(\Registry $registry){
		return self::loadByKey($registry, self::KEY_MANUAL_TASK);
	}


	public static function loadFailedJobs(\Registry $registry){
		return self::loadByKey($registry, self::KEY_FAILED_JOB);
	}

	protected static function loadByKey(\Registry $registry, $key){
		$db = $registry->get('db');
		
		$table = DB_PREFIX . self::getTableName();
		$key = $db->escape($key);
		
		$query = "SELECT * FROM $table WHERE `key`='$key';";
		
		$db_result = self::query($query, $db);
		
		if (isset($db_result->row) && !empty($db_result->row)) {
			return new self($registry, $db_result->row);
		}
		return new self($registry);
	}
}