<?php

namespace PostFinanceCheckout\Entity;

/**
 * This entity holds data about a transaction on the gateway.
 *
 * @method int getExternalId()
 * @method void setExternalId(int $id)
 * @method \PostFinanceCheckout\Sdk\Model\LineItemReduction[] getReductionItems()
 * @method void setReductionItems(\PostFinanceCheckout\Sdk\Model\LineItemReduction[] $reductions)
 * @method float getAmount()
 * @method void setAmount(float $amount)
 * @method void setFailureReason(map[string,string] $reasons)
 * @method bool getRestock()
 * @method void setRestock(bool $restock)
 *
 */
class RefundJob extends AbstractJob {
	const STATE_PENDING = 'PENDING';
	const STATE_MANUAL_CHECK = 'MANUAL_CHECK';

	protected static function getFieldDefinition(){
		return array_merge(parent::getFieldDefinition(),
				[
					'external_id' => ResourceType::STRING,
					'restock' => ResourceType::BOOLEAN,
					'reduction_items' => ResourceType::OBJECT,
					'amount' => ResourceType::DECIMAL 
				]);
	}

	protected static function getTableName(){
		return 'postfinancecheckout_refund_job';
	}
	
	public static function sumRefundedAmount(\Registry $registry, $order_id) {
		$db = $registry->get('db');
		
		$table = DB_PREFIX . self::getTableName();
		$order_id = $db->escape($order_id);
		$state = self::STATE_SUCCESS;
		$query = "SELECT SUM(amount) FROM $table WHERE order_id='$order_id' AND state='$state';";
		
		$db_result = $db->query($query);
		
		if (isset($db_result->row['SUM(amount)'])) {
			return $db_result->row['SUM(amount)'];
		}
		return 0;
	}

	/**
	 * Counts transactions with PENDING & MANUAL_CHECK status
	 * @param \Registry $registry
	 * @param unknown $space_id
	 * @return unknown|number
	 */
	public static function countPending(\Registry $registry, $space_id){
		$db = $registry->get('db');
		
		$table = DB_PREFIX . self::getTableName();
		$space_id = $db->escape($space_id);
		$state_pending = self::STATE_PENDING;
		$state_manual = self::STATE_MANUAL_CHECK;
		
		$query = "SELECT COUNT(id) FROM $table WHERE space_id='$space_id' AND state IN ('$state_pending', '$state_manual');";
		
		$db_result = $db->query($query);
		
		if (isset($db_result->row['COUNT(id)'])) {
			return $db_result->row['COUNT(id)'];
		}
		return 0;
	}

	public static function loadByExternalId(\Registry $registry, $space_id, $external_id){
		$db = $registry->get('db');
		
		$table = DB_PREFIX . self::getTableName();
		$space_id = $db->escape($space_id);
		$external_id = $db->escape($external_id);
		$query = "SELECT * FROM $table WHERE space_id='$space_id' AND external_id='$external_id';";
		
		$db_result = $db->query($query);
		if (isset($db_result->row) && !empty($db_result->row)) {
			return new static($registry, $db_result->row);
		}
		return new static($registry);
	}
}