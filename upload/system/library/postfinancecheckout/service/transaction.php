<?php

namespace PostFinanceCheckout\Service;

use PostFinanceCheckout\Sdk\Service\ChargeAttemptService;
use PostFinanceCheckout\Sdk\Service\TransactionService;
use PostFinanceCheckout\Sdk\Model\TransactionPending;

/**
 * This service provides functions to deal with PostFinanceCheckout transactions.
 *
 * It generally provides three ways of creating & updating transactions.
 * 1) With total & address, for filtering active payment methods.
 * 2) With session data, before the order has been persisted in the database. For getting javascript url & confirming the order.
 * 3) With the order information, after the order has been completed. For backend operations.
 */
class Transaction extends AbstractService {

	public function getPaymentMethods(array $order_info){
		$cart_id = \PostFinanceCheckoutVersionHelper::getCurrentCartId($this->registry);
		if (!$cart_id || !array_key_exists($cart_id, self::$possible_payment_method_cache)) {
			$transaction = $this->update($order_info, false);
			try {
				$payment_methods = $this->getTransactionService()->fetchPossiblePaymentMethods($transaction->getLinkedSpaceId(), $transaction->getId());
				foreach ($payment_methods as $payment_method) {
					MethodConfiguration::instance($this->registry)->updateData($payment_method);
				}
				self::$possible_payment_method_cache[$cart_id] = $payment_methods;
			}
			catch (\Exception $e) {
				self::$possible_payment_method_cache[$cart_id] = array();
				throw $e;
			}
		}
		return self::$possible_payment_method_cache[$cart_id];
	}

	public function getJavascriptUrl(){
		$transaction = $this->getTransaction(array(), false, array(
			\PostFinanceCheckout\Sdk\Model\TransactionState::PENDING 
		));
		return $this->getTransactionService()->buildJavaScriptUrl($transaction->getLinkedSpaceId(), $transaction->getId());
	}

	public function getPaymentPageUrl(\PostFinanceCheckout\Sdk\Model\Transaction $transaction, $paymentCode){
		$paymentMethodId = substr($paymentCode, strlen('postfinancecheckout_'));
		return $this->getTransactionService()->buildPaymentPageUrl($transaction->getLinkedSpaceId(), $transaction->getId()) .
				 '&paymentMethodConfigurationId=' . $paymentMethodId;
	}

	public function update(array $order_info, $confirm = false){
		$last = null;
		try {
			for ($i = 0; $i < 5; $i++) {
				$transaction = $this->getTransaction($order_info, false);
				if ($transaction->getState() !== \PostFinanceCheckout\Sdk\Model\TransactionState::PENDING) {
					if ($confirm) {
						throw new \Exception('No pending transaction available to be confirmed.');
					}
					return $this->create($order_info);
				}
				
				$pending_transaction = new \PostFinanceCheckout\Sdk\Model\TransactionPending();
				$pending_transaction->setId($transaction->getId());
				$pending_transaction->setVersion($transaction->getVersion());
				$this->assembleTransaction($pending_transaction, $order_info);
				
				if ($confirm) {
					$transaction = $this->getTransactionService()->confirm($transaction->getLinkedSpaceId(), $pending_transaction);
					$this->clearTransactionInSession();
				}
				else {
					$transaction = $this->getTransactionService()->update($transaction->getLinkedSpaceId(), $pending_transaction);
				}
				
				$this->persist($transaction, $order_info);
				
				return $transaction;
			}
		}
		catch (\PostFinanceCheckout\Sdk\ApiException $e) {
			$last = $e;
			if ($e->getCode() != 409) {
				throw $e;
			}
		}
		
		throw $last;
	}

	/**
	 * Wait for the order to reach a given state.
	 *
	 * @param $order_id
	 * @param array $states
	 * @param int $maxWaitTime
	 * @return boolean
	 */
	public function waitForStates($order_id, array $states, $maxWaitTime = 10){
		$startTime = microtime(true);
		while (true) {
			$transactionInfo = \PostFinanceCheckout\Entity\TransactionInfo::loadByOrderId($this->registry, $order_id);
			if (in_array($transactionInfo->getState(), $states)) {
				return true;
			}
			
			if (microtime(true) - $startTime >= $maxWaitTime) {
				return false;
			}
			sleep(1);
		}
	}

	/**
	 * Reads or creates a new transaction.
	 *
	 * @param array $order_info
	 * @return \PostFinanceCheckout\Sdk\Model\Transaction
	 */
	public function getTransaction($order_info = array(), $cache = true, $allowed_states = array()){
		$cart_id = \PostFinanceCheckoutVersionHelper::getCurrentCartId($this->registry);
		
		// guest has $cart_id = 0
		if ($cart_id && isset(self::$transaction_cache[$cart_id]) && $cache) {
			return self::$transaction_cache[$cart_id];
		}
		
		$create = true;
		
		// attempt to load via session variables
		if ($this->hasTransactionInSession()) {
			self::$transaction_cache[$cart_id] = $this->getTransactionService()->read($this->getSessionSpaceId(), $this->getSessionTransactionId());
			// check if the status is expected
			$create = empty($allowed_states) ? false : !in_array(self::$transaction_cache[$cart_id]->getState(), $allowed_states);
		}
		
		// attempt to load via order id (existing transaction_info
		if (isset($order_info['order_id']) && $create) {
			$transaction_info = \PostFinanceCheckout\Entity\TransactionInfo::loadByOrderId($this->registry, $order_info['order_id']);
			if ($transaction_info->getId() && $transaction_info->getState() === 'PENDING') {
				self::$transaction_cache[$cart_id] = $this->getTransactionService()->read($transaction_info->getSpaceId(),
						$transaction_info->getTransactionId());
				$create = empty($allowed_states) ? false : !in_array(self::$transaction_cache[$cart_id]->getState(), $allowed_states);
			}
			if ($create) {
				unset($order_info['order_id']);
			}
		}
		
		// no applicable transaction found, create new one.
		if ($create) {
			self::$transaction_cache[$cart_id] = $this->create($order_info);
		}
		
		return self::$transaction_cache[$cart_id];
	}

	private function persist($transaction, array $order_info){
		if (isset($order_info['order_id'])) {
			$this->updateTransactionInfo($transaction, $order_info['order_id']);
		}
		$this->storeTransactionIdsInSession($transaction);
		$this->storeCoupon($transaction);
		$this->storeShipping($transaction);
	}

	private function create(array $order_info){
		$create_transaction = new \PostFinanceCheckout\Sdk\Model\TransactionCreate();
		
		$create_transaction->setCustomersPresence(\PostFinanceCheckout\Sdk\Model\CustomersPresence::VIRTUAL_PRESENT);
		if (isset($this->registry->get('request')->cookie['postfinancecheckout_device_id'])) {
			$create_transaction->setDeviceSessionIdentifier($this->registry->get('request')->cookie['postfinancecheckout_device_id']);
		}
		
		$create_transaction->setAutoConfirmationEnabled(false);
		$this->assembleTransaction($create_transaction, $order_info);
		$transaction = $this->getTransactionService()->create($this->registry->get('config')->get('postfinancecheckout_space_id'),
				$create_transaction);
		
		$this->persist($transaction, $order_info);
		
		return $transaction;
	}

	private function assembleTransaction(\PostFinanceCheckout\Sdk\Model\AbstractTransactionPending $transaction, array $order_info){
		$order_id = isset($order_info['order_id']) ? $order_info['order_id'] : null;
		$data = $this->registry->get('session')->data;
		
		if (isset($data['currency'])) {
			$transaction->setCurrency($data['currency']);
		}
		else {
			throw new \Exception('Session currency not set.');
		}
		
		$transaction->setBillingAddress(
				$this->assembleAddress(\PostFinanceCheckoutHelper::instance($this->registry)->getAddress('payment', $order_info)));
		$transaction->setShippingAddress(
				$this->assembleAddress(\PostFinanceCheckoutHelper::instance($this->registry)->getAddress('shipping', $order_info)));
		
		$customer = \PostFinanceCheckoutHelper::instance($this->registry)->getCustomer();
		if (isset($customer['customer_id'])) {
			$transaction->setCustomerId($customer['customer_id']);
		}
		if (isset($customer['customer_email'])) {
			$transaction->setCustomerEmailAddress($this->getFixedSource($customer, 'customer_email', 150));
		}
		
		$transaction->setLanguage(\PostFinanceCheckoutHelper::instance($this->registry)->getCleanLanguageCode());
		if (isset($data['shipping_method'])) {
			$transaction->setShippingMethod($this->fixLength($data['shipping_method']['title'], 200));
		}
		
		$transaction->setLineItems(LineItem::instance($this->registry)->getItemsFromSession());
		$transaction->setSuccessUrl(\PostFinanceCheckoutHelper::instance($this->registry)->getSuccessUrl());
		
		if ($order_id) {
			$transaction->setMerchantReference($order_id);
			$transaction->setFailedUrl(\PostFinanceCheckoutHelper::instance($this->registry)->getFailedUrl($order_id));
		}
	}
	
	/**
	 * Cache for cart transactions.
	 *
	 * @var \PostFinanceCheckout\Sdk\Model\Transaction[]
	 */
	private static $transaction_cache = array();
	
	/**
	 * Cache for possible payment methods by cart.
	 *
	 * @var \PostFinanceCheckout\Sdk\Model\PaymentMethodConfiguration[]
	 */
	private static $possible_payment_method_cache = array();
	
	/**
	 * The transaction API service.
	 *
	 * @var \PostFinanceCheckout\Sdk\Service\TransactionService
	 */
	private $transaction_service;
	
	/**
	 * The charge attempt API service.
	 *
	 * @var \PostFinanceCheckout\Sdk\Service\ChargeAttemptService
	 */
	private $charge_attempt_service;

	/**
	 * Returns the transaction API service.
	 *
	 * @return \PostFinanceCheckout\Sdk\Service\TransactionService
	 */
	private function getTransactionService(){
		if ($this->transaction_service === null) {
			$this->transaction_service = new TransactionService(\PostFinanceCheckoutHelper::instance($this->registry)->getApiClient());
		}
		return $this->transaction_service;
	}

	/**
	 * Returns the charge attempt API service.
	 *
	 * @return \PostFinanceCheckout\Sdk\Service\ChargeAttemptService
	 */
	private function getChargeAttemptService(){
		if ($this->charge_attempt_service === null) {
			$this->charge_attempt_service = new ChargeAttemptService(\PostFinanceCheckoutHelper::instance($this->registry)->getApiClient());
		}
		return $this->charge_attempt_service;
	}

	/**
	 * Updates the line items to be in line with the current order.
	 *
	 * @param string $order_id
	 * @return \PostFinanceCheckout\Sdk\Model\TransactionLineItemVersion
	 */
	public function updateLineItemsFromOrder($order_id){
		$order_info = \PostFinanceCheckoutHelper::instance($this->registry)->getOrder($order_id);
		$transaction_info = \PostFinanceCheckout\Entity\TransactionInfo::loadByOrderId($this->registry, $order_id);
		
		$line_items = \PostFinanceCheckout\Service\LineItem::instance($this->registry)->getItemsFromOrder($order_info,
				$transaction_info->getTransactionId(), $transaction_info->getSpaceId());
		
		$update_request = new \PostFinanceCheckout\Sdk\Model\TransactionLineItemUpdateRequest();
		$update_request->setTransactionId($transaction_info->getTransactionId());
		$update_request->setNewLineItems($line_items);
		return $this->getTransactionService()->updateTransactionLineItems($transaction_info->getSpaceId(), $update_request);
	}

	/**
	 * Stores the transaction data in the database.
	 *
	 * @param \PostFinanceCheckout\Sdk\Model\Transaction $transaction
	 * @param array $order_info
	 * @return \PostFinanceCheckout\Entity\TransactionInfo
	 */
	public function updateTransactionInfo(\PostFinanceCheckout\Sdk\Model\Transaction $transaction, $order_id){
		$info = \PostFinanceCheckout\Entity\TransactionInfo::loadByTransaction($this->registry, $transaction->getLinkedSpaceId(),
				$transaction->getId());
		$info->setTransactionId($transaction->getId());
		$info->setAuthorizationAmount($transaction->getAuthorizationAmount());
		$info->setOrderId($order_id);
		$info->setState($transaction->getState());
		$info->setSpaceId($transaction->getLinkedSpaceId());
		$info->setSpaceViewId($transaction->getSpaceViewId());
		$info->setLanguage($transaction->getLanguage());
		$info->setCurrency($transaction->getCurrency());
		$info->setConnectorId(
				$transaction->getPaymentConnectorConfiguration() != null ? $transaction->getPaymentConnectorConfiguration()->getConnector() : null);
		$info->setPaymentMethodId(
				$transaction->getPaymentConnectorConfiguration() != null && $transaction->getPaymentConnectorConfiguration()->getPaymentMethodConfiguration() !=
						 null ? $transaction->getPaymentConnectorConfiguration()->getPaymentMethodConfiguration()->getPaymentMethod() : null);
		$info->setImage($this->getPaymentMethodImage($transaction));
		$info->setLabels($this->getTransactionLabels($transaction));
		if ($transaction->getState() == \PostFinanceCheckout\Sdk\Model\TransactionState::FAILED ||
				 $transaction->getState() == \PostFinanceCheckout\Sdk\Model\TransactionState::DECLINE) {
			$failed_charge_attempt = $this->getFailedChargeAttempt($transaction->getLinkedSpaceId(), $transaction->getId());
			if ($failed_charge_attempt && $failed_charge_attempt->getFailureReason() != null) {
				$info->setFailureReason($failed_charge_attempt->getFailureReason()->getDescription());
			}
			else if ($transaction->getFailureReason()) {
				$info->setFailureReason($transaction->getFailureReason()->getDescription());
			}
		}
		$info->save();
		return $info;
	}

	/**
	 * Returns the last failed charge attempt of the transaction.
	 *
	 * @param int $space_id
	 * @param int $transaction_id
	 * @return \PostFinanceCheckout\Sdk\Model\ChargeAttempt
	 */
	private function getFailedChargeAttempt($space_id, $transaction_id){
		$charge_attempt_service = $this->getChargeAttemptService();
		$query = new \PostFinanceCheckout\Sdk\Model\EntityQuery();
		$filter = new \PostFinanceCheckout\Sdk\Model\EntityQueryFilter();
		$filter->setType(\PostFinanceCheckout\Sdk\Model\EntityQueryFilterType::_AND);
		$filter->setChildren(
				array(
					$this->createEntityFilter('charge.transaction.id', $transaction_id),
					$this->createEntityFilter('state', \PostFinanceCheckout\Sdk\Model\ChargeAttemptState::FAILED) 
				));
		$query->setFilter($filter);
		$query->setOrderBys(array(
			$this->createEntityOrderBy('failedOn') 
		));
		$query->setNumberOfEntities(1);
		$result = $charge_attempt_service->search($space_id, $query);
		if ($result != null && !empty($result)) {
			return current($result);
		}
		else {
			return null;
		}
	}

	/**
	 * Returns an array of the transaction's labels.
	 *
	 * @param \PostFinanceCheckout\Sdk\Model\Transaction $transaction
	 * @return string[]
	 */
	private function getTransactionLabels(\PostFinanceCheckout\Sdk\Model\Transaction $transaction){
		$charge_attempt = $this->getChargeAttempt($transaction);
		if ($charge_attempt != null) {
			$labels = array();
			foreach ($charge_attempt->getLabels() as $label) {
				$labels[$label->getDescriptor()->getId()] = $label->getContentAsString();
			}
			return $labels;
		}
		else {
			return array();
		}
	}

	/**
	 * Returns the successful charge attempt of the transaction.
	 *
	 * @param \PostFinanceCheckout\Sdk\Model\Transaction $transaction
	 * @return \PostFinanceCheckout\Sdk\Model\ChargeAttempt
	 */
	private function getChargeAttempt(\PostFinanceCheckout\Sdk\Model\Transaction $transaction){
		$charge_attempt_service = $this->getChargeAttemptService();
		$query = new \PostFinanceCheckout\Sdk\Model\EntityQuery();
		$filter = new \PostFinanceCheckout\Sdk\Model\EntityQueryFilter();
		$filter->setType(\PostFinanceCheckout\Sdk\Model\EntityQueryFilterType::_AND);
		$filter->setChildren(
				array(
					$this->createEntityFilter('charge.transaction.id', $transaction->getId()),
					$this->createEntityFilter('state', \PostFinanceCheckout\Sdk\Model\ChargeAttemptState::SUCCESSFUL) 
				));
		$query->setFilter($filter);
		$query->setNumberOfEntities(1);
		$result = $charge_attempt_service->search($transaction->getLinkedSpaceId(), $query);
		if ($result != null && !empty($result)) {
			return current($result);
		}
		else {
			return null;
		}
	}

	/**
	 * Returns the payment method's image.
	 *
	 * @param \PostFinanceCheckout\Sdk\Model\Transaction $transaction
	 * @return string
	 */
	private function getPaymentMethodImage(\PostFinanceCheckout\Sdk\Model\Transaction $transaction){
		if ($transaction->getPaymentConnectorConfiguration() == null ||
				 $transaction->getPaymentConnectorConfiguration()->getPaymentMethodConfiguration() == null) {
			return null;
		}
		return $transaction->getPaymentConnectorConfiguration()->getPaymentMethodConfiguration()->getResolvedImageUrl();
	}

	private function assembleAddress($source, $prefix = ''){
		$address = new \PostFinanceCheckout\Sdk\Model\AddressCreate();
		
		if (isset($source[$prefix . 'city'])) {
			$address->setCity($this->getFixedSource($source, $prefix . 'city', 100));
		}
		if (isset($source[$prefix . 'iso_code_2'])) {
			$address->setCountry($source[$prefix . 'iso_code_2']);
		}
		if (isset($source[$prefix . 'lastname'])) {
			$address->setFamilyName($this->getFixedSource($source, $prefix . 'lastname', 100));
		}
		if (isset($source[$prefix . 'firstname'])) {
			$address->setGivenName($this->getFixedSource($source, $prefix . 'firstname', 100));
		}
		if (isset($source[$prefix . 'company'])) {
			$address->setOrganizationName($this->getFixedSource($source, $prefix . 'company', 100));
		}
		if (isset($source[$prefix . 'postcode'])) {
			$address->setPostCode($this->getFixedSource($source, $prefix . 'postcode', 40));
		}
		if (isset($source[$prefix . 'address_1'])) {
			$address->setStreet($this->fixLength(trim($source[$prefix . 'address_1'] . "\n" . $source[$prefix . 'address_2']), 300));
		}
		
		// state is 2-part
		if (isset($source[$prefix . 'zone_code']) && isset($source[$prefix . 'iso_code_2'])) {
			$address->setPostalState($source[$prefix . 'iso_code_2'] . '_' . $source[$prefix . 'zone_code']);
		}
		
		return $address;
	}

	private function getFixedSource(array $source_array, $key, $max_length = null, $is_ascii = true, $new_lines = false){
		$value = null;
		if (isset($source_array[$key])) {
			$value = $source_array[$key];
			if ($max_length) {
				$value = $this->fixLength($value, $max_length);
			}
			if ($is_ascii) {
				$value = $this->removeNonAscii($value);
			}
			if (!$new_lines) {
				$value = str_replace("\n", "", $value);
			}
		}
		return $value;
	}

	private function hasTransactionInSession(){
		$data = $this->registry->get('session')->data;
		return isset($data['postfinancecheckout_transaction_id']) && isset($data['postfinancecheckout_space_id']) &&
				 $data['postfinancecheckout_space_id'] == $this->registry->get('config')->get('postfinancecheckout_space_id') &&
				 array_key_exists('postfinancecheckout_customer', $data) &&
				 $data['postfinancecheckout_customer'] === \PostFinanceCheckoutHelper::instance($this->registry)->getCustomerSessionIdentifier() &&
				 $data['postfinancecheckout_customer'];
	}

	private function clearTransactionInSession(){
		if ($this->hasTransactionInSession()) {
			unset($this->registry->get('session')->data['postfinancecheckout_transaction_id']);
			unset($this->registry->get('session')->data['postfinancecheckout_customer']);
			unset($this->registry->get('session')->data['postfinancecheckout_space_id']);
		}
	}

	private function getSessionTransactionId(){
		return $this->registry->get('session')->data['postfinancecheckout_transaction_id'];
	}

	private function getSessionSpaceId(){
		return $this->registry->get('session')->data['postfinancecheckout_space_id'];
	}

	private function storeTransactionIdsInSession(\PostFinanceCheckout\Sdk\Model\Transaction $transaction){
		$this->registry->get('session')->data['postfinancecheckout_customer'] = \PostFinanceCheckoutHelper::instance($this->registry)->getCustomerSessionIdentifier();
		$this->registry->get('session')->data['postfinancecheckout_transaction_id'] = $transaction->getId();
		$this->registry->get('session')->data['postfinancecheckout_space_id'] = $transaction->getLinkedSpaceId();
	}

	private function storeShipping(\PostFinanceCheckout\Sdk\Model\Transaction $transaction){
		if (isset($this->registry->get('session')->data['shipping_method'])) {
			$shipping_info = \PostFinanceCheckout\Entity\ShippingInfo::loadByTransaction($this->registry, $transaction->getLinkedSpaceId(),
					$transaction->getId());
			$shipping_info->setTransactionId($transaction->getId());
			$shipping_info->setSpaceId($transaction->getLinkedSpaceId());
			$shipping_info->setCost($this->registry->get('session')->data['shipping_method']['cost']);
			$shipping_info->setTaxClassId($this->registry->get('session')->data['shipping_method']['tax_class_id']);
			$shipping_info->save();
		}
	}

	private function storeCoupon(\PostFinanceCheckout\Sdk\Model\Transaction $transaction){
		if (isset($this->registry->get('session')->data['coupon']) && isset($this->registry->get('session')->data['order_id'])) {
			$transaction_info = \PostFinanceCheckout\Entity\TransactionInfo::loadByTransaction($this->registry,
					$this->registry->get('session')->data['order_id']);
			$transaction_info->setTransactionId($transaction->getId());
			$transaction_info->setSpaceId($transaction->getLinkedSpaceId());
			$transaction_info->setOrderId($this->registry->get('session')->data['order_id']);
			$transaction_info->setCouponCode($this->registry->get('session')->data['coupon']);
			$transaction_info->setState(\PostFinanceCheckout\Sdk\Model\TransactionState::CREATE);
			$transaction_info->save();
		}
	}
}