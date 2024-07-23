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
 * This entity holds data about a transaction on the gateway.
 * Note: these methods are case-sensitive
 *
 * @method int getId()
 * @method int getTransactionId()
 * @method void setTransactionId(int $id)
 * @method string getState()
 * @method void setState(string $state)
 * @method int getSpaceId()
 * @method void setSpaceId(int $id)
 * @method int getSpaceViewId()
 * @method void setSpaceViewId(int $id)
 * @method string getLanguage()
 * @method void setLanguage(string $language)
 * @method string getCurrency()
 * @method void setCurrency(string $currency)
 * @method float getAuthorizationAmount()
 * @method void setAuthorizationAmount(float $amount)
 * @method string getImage()
 * @method void setImage(string $image)
 * @method object getLabels()
 * @method void setLabels(map[string,string] $labels)
 * @method int getPaymentMethodId()
 * @method void setPaymentMethodId(int $id)
 * @method int getConnectorId()
 * @method void setConnectorId(int $id)
 * @method int getOrderId()
 * @method void setOrderId(int $id)
 * @method void setFailureReason(map[string,string] $reasons)
 * @method void setCouponCode(string $coupon_code)
 * @method string getCouponCode()
 *
 */
class TransactionInfo extends AbstractEntity {

	protected static function getFieldDefinition(){
		return array(
			'transaction_id' => ResourceType::INTEGER,
			'state' => ResourceType::STRING,
			'space_id' => ResourceType::INTEGER,
			'space_view_id' => ResourceType::INTEGER,
			'language' => ResourceType::STRING,
			'currency' => ResourceType::STRING,
			'authorization_amount' => ResourceType::DECIMAL,
			'image' => ResourceType::STRING,
			'labels' => ResourceType::OBJECT,
			'payment_method_id' => ResourceType::INTEGER,
			'connector_id' => ResourceType::INTEGER,
			'order_id' => ResourceType::INTEGER,
			'failure_reason' => ResourceType::OBJECT,
			'coupon_code' => ResourceType::STRING,
			'locked_at' => ResourceType::DATETIME 
		);
	}

	protected static function getTableName(){
		return 'postfinancecheckout_transaction_info';
	}

	/**
	 * Returns the translated failure reason.
	 *
	 * @param string $locale
	 * @return string
	 */
	public function getFailureReason($language = null){
		$value = $this->getValue('failure_reason');
		if (empty($value)) {
			return null;
		}
		
		return \PostFinanceCheckoutHelper::instance($this->registry)->translate($value, $language);
	}

	public static function loadByOrderId(\Registry $registry, $order_id){
		$db = $registry->get('db');
		
		$table = DB_PREFIX . self::getTableName();
		$order_id = $db->escape($order_id);
		$query = "SELECT * FROM $table WHERE order_id='$order_id';";
		
		$db_result = self::query($query, $db);
		if (isset($db_result->row) && !empty($db_result->row)) {
			return new self($registry, $db_result->row);
		}
		return new self($registry);
	}

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