<?php

namespace PostFinanceCheckout\Entity;

/**
 * This entity holds data about a transaction on the gateway.
 *
 * @method int getId()
 * @method void setTransactionId(int $id)
 * @method int getTransactionId()
 * @method void SetSpaceId(int $id)
 * @method int getSpaceId()
 * @method void setTaxClassId(int $id)
 * @method int getTaxClassId()
 * @method void setCost(float $cost)
 * @method float getCost()
 *
 */
class ShippingInfo extends AbstractEntity {
	
	protected static function getFieldDefinition(){
		return array(
			'transaction_id' => ResourceType::INTEGER,
			'space_id' => ResourceType::INTEGER,
			'cost' => ResourceType::DECIMAL,
			'tax_class_id' => ResourceType::INTEGER 
		);
	}

	protected static function getTableName(){
		return 'postfinancecheckout_shipping_info';
	}

	/**
	 * 
	 * @param \Registry $registry
	 * @param int $space_id
	 * @param int $transaction_id
	 * @return \PostFinanceCheckout\Entity\ShippingInfo
	 */
	public static function loadByTransaction(\Registry $registry, $space_id, $transaction_id){
		$db = $registry->get('db');
		
		$table = DB_PREFIX . self::getTableName();
		$space_id = $db->escape($space_id);
		$transaction_id = $db->escape($transaction_id);
		$query = "SELECT * FROM $table WHERE space_id='$space_id' AND transaction_id='$transaction_id';";
		
		$db_result = self::query($query, $db);
		if (isset($db_result->row) && !empty($db_result->row)) {
			return new self($registry, $db_result->row);
		}
		return new self($registry);
	}
}