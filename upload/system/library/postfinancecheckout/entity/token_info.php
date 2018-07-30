<?php

namespace PostFinanceCheckout\Entity;

/**
 * This entity holds data about a token on the gateway.
 *
 * @method int getid()
 * @method int gettokenid()
 * @method void settokenid(int $id)
 * @method string getstate()
 * @method void setstate(string $state)
 * @method int getspaceid()
 * @method void setspaceid(int $id)
 * @method string getname()
 * @method void setname(string $name)
 * @method int getcustomerid()
 * @method void setcustomerid(int $id)
 * @method int getpaymentmethodid()
 * @method void setpaymentmethodid(int $id)
 * @method int getconnectorid()
 * @method void setconnectorid(int $id)
 *
 */
class TokenInfo extends AbstractEntity {

	protected static function getFieldDefinition(){
		return array(
			'token_id' => ResourceType::INTEGER,
			'state' => ResourceType::STRING,
			'space_id' => ResourceType::INTEGER,
			'name' => ResourceType::STRING,
			'customer_id' => ResourceType::INTEGER,
			'payment_method_id' => ResourceType::INTEGER,
			'connector_id' => ResourceType::INTEGER 
		);
	}

	protected static function getTableName(){
		return 'postfinancecheckout_token_info';
	}

	public static function loadByToken(\Registry $registry, $space_id, $token_id){
		$db = $registry->get('db');
		
		$table = DB_PREFIX . self::getTableName();
		$space_id = $db->escape($space_id);
		$token_id = $db->escape($token_id);
		$query = "SELECT * FROM $table WHERE space_id = $space_id AND token_id = $token_id;";
		
		$db_result = $db->query($query);
		
		if (isset($db_result->row) && !empty($db_result->row)) {
			return new self($registry, $db_result->row);
		}
		return new self($registry);
	}
}