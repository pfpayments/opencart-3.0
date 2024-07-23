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
require_once (DIR_SYSTEM . 'library/postfinancecheckout/autoload.php');
require_once DIR_SYSTEM . '/library/postfinancecheckout/version_helper.php';

class PostFinanceCheckoutHelper {
	
	
	const SHOP_SYSTEM = 'x-meta-shop-system';
	const SHOP_SYSTEM_VERSION = 'x-meta-shop-system-version';
	const SHOP_SYSTEM_AND_VERSION = 'x-meta-shop-system-and-version';

	const FALLBACK_LANGUAGE = 'en-US';
	/**
	 *
	 * @var PostFinanceCheckout\Sdk\ApiClient
	 */
	private $apiClient;
	/**
	 *
	 * @var Registry
	 */
	private $registry;
	private $xfeepro;
	private static $instance;
	private $catalog_url;
	const LOG_INFO = 2;
	const LOG_DEBUG = 1;
	const LOG_ERROR = 0;
	private $loggers;

	private function __construct(Registry $registry){
		if ($registry instanceof Registry && $registry->has('session') && $registry->has('config') && $registry->has('db')) {
			$this->registry = $registry;
			$this->loggers = array(
				self::LOG_ERROR => $registry->get('log'),
				self::LOG_DEBUG => new Log('postfinancecheckout_debug.log'),
				self::LOG_INFO => new Log('postfinancecheckout_info.log')
			);
		}
		else {
			throw new \Exception("Could not instatiate PostFinanceCheckoutHelper, invalid registry supplied.");
		}
	}

	/**
	 * Create a customer identifier to verify that the session.
	 * Either the customer id,
	 * a concat of given values for guest (hashed),
	 * the user id,
	 * a hash of the current cart key,
	 * a hash of the current token,
	 * or the current order id.
	 *
	 * If not enough information exists to create an identifier null is returned.
	 *
	 * @return string | null
	 */
	public function getCustomerSessionIdentifier(){
		$customer = $this->getCustomer();
		if (isset($customer['customer_id']) && $this->registry->get('customer') && $this->registry->get('customer')->isLogged()) {
			return "customer_" . $customer['customer_id'];
		}
		$guestId = $this->buildGuestSessionIdentifier($customer);
		if ($guestId) {
			return $guestId;
		}
		$data = $this->registry->get('session')->data;
		if (isset($data['user_id'])) {
			return "user_" . $data['user_id'];
		}
		$cartId = $this->buildCartSessionIdentifier($data);
		if ($cartId) {
			return $cartId;
		}
		if (isset($data['user_token'])) {
			return "token_" . hash('sha512', $data['user_token']);
		}
		return null;
	}

	private function buildCartSessionIdentifier(array $data){
		if (isset($data['cart']) && is_array($data['cart']) && count($data['cart']) == 1) {
			$cartKeys = array_keys($data['cart']);
			return "cart_" . hash('sha512', $cartKeys[0]);
		}
		return null;
	}

	private function buildGuestSessionIdentifier(array $customer){
		$id = '';
		if (isset($customer['firstname'])) {
			$id .= $customer['firstname'];
		}
		if (isset($customer['lastname'])) {
			$id .= $customer['lastname'];
		}
		if (isset($customer['email'])) {
			$id .= $customer['email'];
		}
		if (isset($customer['telephone'])) {
			$id .= $customer['telephone'];
		}
		if ($id) {
			return "guest_" . hash('sha512', $id);
		}
		return null;
	}

	public function compareStoredCustomerSessionIdentifier(){
		$data = $this->registry->get('session')->data;
		if (isset($data['postfinancecheckout_customer']) && !empty($data['postfinancecheckout_customer'])) {
			$id = $data['postfinancecheckout_customer'];
		}
		else {
			return false;
		}

		$parts = explode('_', $id);
		$customer = $this->getCustomer();
		switch ($parts[0]) {
			case 'customer':
				return isset($customer['customer_id']) && 'customer_' . $customer['customer_id'] == $id;
			case 'user':
				return (isset($customer['user_id']) && 'user_' . $customer['user_id'] == $id) ||
						(isset($data['user_id']) && 'user_' . $data['user_id'] == $id);
			case 'guest':
				return $this->buildGuestSessionIdentifier($customer) == $id;
			case 'cart':
				return $this->buildCartSessionIdentifier($data) == $id;
			case 'token':
				return isset($data['user_token']) && 'token_' . hash('sha512', $data['user_token']) == $id;
			default:
				$this->log("Unkown comparison on {$parts[0]} with {$id}");
		}
		return false;
	}

	/**
	 * Attempt to read the current active address from different sources.
	 *
	 * @param string $key 'payment' or 'shipping' depending on which address is desired.
	 * @param array $order_info Optional order_info as additional address source
	 * @return array
	 */
	public function getAddress($key, $order_info = array()){
		$customer = $this->registry->get('customer');
		$session = $this->registry->get('session')->data;
		$address_model = $this->registry->get('model_account_address');
		$address = array();

		if (isset($order_info[$key . '_address'])) {
			$address = \PostFinanceCheckoutHelper::mergeArray($address, $order_info[$key . '_address']);
		}
		if (isset($order_info[$key . '_address_id'])) {
			$address = \PostFinanceCheckoutHelper::mergeArray($address, $address_model->getAddress($$order_info[$key . '_address_id']));
		}
		if (empty($address) && $key != 'payment') {
			$address = $this->getAddress('payment', $order_info);
		}
		if (empty($address)) {
			if ($customer && $customer->isLogged() && isset($session[$key . '_address_id'])) {
				$address = $address_model->getAddress($session[$key . '_address_id']);
			}
			if (isset($session['guest'][$key]) && is_array($session['guest'][$key])) { // billing only
				$address = \PostFinanceCheckoutHelper::mergeArray($address, $session['guest'][$key]);
			}
			if (isset($session[$key][$key . '_address'])) { // shipping only
				$address = \PostFinanceCheckoutHelper::mergeArray($address, $session[$key][$key . '_address']);
			}
			if (isset($session[$key . '_address']) && is_array($session[$key . '_address'])) {
				$address = \PostFinanceCheckoutHelper::mergeArray($address, $session[$key . '_address']);
			}
		}
		return $address;
	}

	public function refreshWebhook(){
		$db = $this->registry->get('db');
		$config = DB_PREFIX . 'setting';

		$generated = $this->getWebhookUrl();
		$saved = $this->registry->get('config')->get('postfinancecheckout_notification_url');
		if ($generated == $saved) {
			return;
		}
		$space_id = $this->registry->get('config')->get('postfinancecheckout_space_id');
		\PostFinanceCheckout\Service\Webhook::instance($this->registry)->uninstall($space_id, $saved);
		\PostFinanceCheckout\Service\Webhook::instance($this->registry)->install($space_id, $generated);

		$store_id = $this->registry->get('config')->get('config_store_id');
		if ($store_id === null) {
			$store_id = 0;
		}
		$store_id = $db->escape($store_id);
		$query = "UPDATE `$config` SET `value`='$generated' WHERE `store_id`='$store_id' AND `key`='postfinancecheckout_notification_url';";
		$db->query($query);
		$this->registry->get('config')->set('postfinancecheckout_notification_url', $generated);
	}

	public function log($message, $level = self::LOG_DEBUG){
		if ($this->registry->get('config')->get('postfinancecheckout_log_level') >= $level) {
			$this->loggers[$level]->write($message);
		}
	}

	public function getSpaceId($store_id){
		$store_id = (int) $store_id;
		$table = DB_PREFIX . 'setting';
		$query = "SELECT value FROM $table WHERE `key`='postfinancecheckout_space_id' AND `store_id`='$store_id'";
		$result = $this->registry->get('db')->query($query);
		if ($result->num_rows) {
			return $result->row['value'];
		}
		throw new Exception('No space id found for store id ' . $store_id);
	}

	public function areAmountsEqual($amount1, $amount2, $currency_code){
		$currency = $this->registry->get('currency');
		if (!$currency->has($currency_code)) {
			throw new Exception("Unknown currency $currency_code");
		}
		return $currency->format($amount1, $currency_code) == $currency->format($amount2, $currency_code);
	}

	public function hasRunningJobs(\PostFinanceCheckout\Entity\TransactionInfo $transaction_info){
		return \PostFinanceCheckout\Entity\CompletionJob::countRunningForOrder($this->registry, $transaction_info->getOrderId()) +
				\PostFinanceCheckout\Entity\VoidJob::countRunningForOrder($this->registry, $transaction_info->getOrderId()) +
				\PostFinanceCheckout\Entity\RefundJob::countRunningForOrder($this->registry, $transaction_info->getOrderId()) > 0;
	}

	public function isCompletionPossible(\PostFinanceCheckout\Entity\TransactionInfo $transaction_info){
		return $transaction_info->getState() == \PostFinanceCheckout\Sdk\Model\TransactionState::AUTHORIZED &&
				(\PostFinanceCheckout\Entity\CompletionJob::countRunningForOrder($this->registry, $transaction_info->getOrderId()) == 0) &&
				(\PostFinanceCheckout\Entity\VoidJob::countRunningForOrder($this->registry, $transaction_info->getOrderId()) == 0);
	}

	public function isRefundPossible(\PostFinanceCheckout\Entity\TransactionInfo $transaction_info){
		if (!in_array($transaction_info->getState(),
				array(
					\PostFinanceCheckout\Sdk\Model\TransactionState::COMPLETED,
					\PostFinanceCheckout\Sdk\Model\TransactionState::FULFILL,
					\PostFinanceCheckout\Sdk\Model\TransactionState::DECLINE
				))) {
			return false;
		}
		$refunded_amount = 0;
		foreach (\PostFinanceCheckout\Entity\RefundJob::loadByOrder($this->registry, $transaction_info->getOrderId()) as $refund_job) {
			switch ($refund_job->getState()) {
				case \PostFinanceCheckout\Entity\RefundJob::STATE_SUCCESS:
					$refunded_amount += $refund_job->getAmount();
					break;
				case \PostFinanceCheckout\Entity\RefundJob::STATE_SENT:
				case \PostFinanceCheckout\Entity\RefundJob::STATE_PENDING:
				case \PostFinanceCheckout\Entity\RefundJob::STATE_MANUAL_CHECK:
					return false;
			}
		}
		return $transaction_info->getAuthorizationAmount() > $refunded_amount;
	}

	/**
	 * Returns a single translated string from the localization file.
	 *
	 * @param string $key
	 * @return string
	 */
	public function getTranslation($key){
		if ($this->registry->get('language')->get($key) == $key) {
			$this->registry->get('language')->load('extension/payment/postfinancecheckout');
		}
		return $this->registry->get('language')->get($key);
	}

	/**
	 * Retrieves order information from front and backend.
	 *
	 * @param int $order_id
	 * @return array
	 */
	public function getOrder($order_id){
		if ($this->isAdmin()) {
			$this->registry->get('load')->model('sale/order');
			return $this->registry->get('model_sale_order')->getOrder($order_id);
		}
		$this->registry->get('load')->model('checkout/order');
		return $this->registry->get('model_checkout_order')->getOrder($order_id);
	}

	/**
	 * Returns the order model which offers methods to retrieve order information - not to add or edit.
	 *
	 * @return Model
	 */
	public function getOrderModel(){
		if ($this->isAdmin()) {
			$this->registry->get('load')->model('sale/order');
			return $this->registry->get('model_sale_order');
		}
		else {
			$this->registry->get('load')->model('account/order');
			return $this->registry->get('model_account_order');
		}
	}

	public function getCustomer(){
		$data = $this->registry->get('session')->data;
		if ($this->registry->get('customer') && $this->registry->get('customer')->isLogged()) {
			$customer_id = $this->registry->get('session')->data['customer_id'];
			$this->registry->get('load')->model('account/customer');
			$customer = $this->registry->get('model_account_customer')->getCustomer($customer_id);
			return $customer;
		}
		else if (isset($data['guest'])) {
			return $data['guest'];
		}
		return array();
	}

	/**
	 * Formats the given amount for the given currency.
	 * If no currency is given, the current session currency is used. If that is not set the shop configuration is used.
	 *
	 * @param float $amount
	 * @param string $currency
	 * @return string
	 */
	public function formatAmount($amount, $currency = null){
		if (!$currency) {
			$currency = $this->getCurrency();
		}
		return $this->registry->get('currency')->format($amount, $currency, false, false);
	}

	/**
	 * Rounds the amount like Xfee would
	 *
	 * @param float $amount
	 * @param string $currency
	 * @return string
	 */
	public function roundXfeeAmount($amount, $currency = null){
		if (!$currency) {
			$currency = $this->getCurrency();
		}
		$decimals = $this->registry->get('currency')->getDecimalPlace();
		$mode = PHP_ROUND_HALF_UP;
		if ($amount < 0) {
			$mode = PHP_ROUND_HALF_DOWN;
		}
		return round($amount, $decimals, $mode);
	}

	public function getCurrency(){
		if (isset($this->registry->get('session')->data['currency'])) {
			return $this->registry->get('session')->data['currency'];
		}
		return $this->registry->get('config')->get('config_currency');
	}

	public function dbTransactionStart(){
		$this->registry->get('db')->query('SET autocommit = 0;');
		$this->registry->get('db')->query('START TRANSACTION;');
	}

	public function dbTransactionCommit(){
		$this->registry->get('db')->query('COMMIT;');
		$this->registry->get('db')->query('SET autocommit = 1;');
	}

	public function dbTransactionRollback(){
		$this->registry->get('db')->query('ROLLBACK;');
		$this->registry->get('db')->query('SET autocommit = 1;');
	}

	/**
	 * Create a lock to prevent concurrency.
	 *
	 * @param int $lockType
	 */
	public function dbTransactionLock($space_id, $transaction_id){
		$db = $this->registry->get('db');

		$table = DB_PREFIX . 'postfinancecheckout_transaction_info';
		$locked_at = date('Y-m-d H:i:s');
		$space_id = $db->escape($space_id);
		$transaction_id = $db->escape($transaction_id);

		$db->query("SELECT locked_at FROM $table WHERE transaction_id = '$transaction_id' AND space_id = '$space_id' FOR UPDATE");

		$db->query("UPDATE $table SET locked_at = '$locked_at' WHERE transaction_id = '$transaction_id' AND space_id = '$space_id'");
	}

	public function translate($strings, $language = null){
		$language = $this->getCleanLanguageCode($language);
		if (isset($strings[$language])) {
			return $strings[$language];
		}

		if ($language) {
			try {
				$language_provider = \PostFinanceCheckout\Provider\Language::instance($this->registry);
				$primary_language = $language_provider->findPrimary($language);
				if ($primary_language && isset($strings[$primary_language->getIetfCode()])) {
					return $strings[$primary_language->getIetfCode()];
				}
			}
			catch (Exception $e) {
			}
		}
		if (isset($strings[self::FALLBACK_LANGUAGE])) {
			return $strings[self::FALLBACK_LANGUAGE];
		}
		$this->log("Could not find translation for given string", self::LOG_ERROR);
		$this->log($strings, self::LOG_ERROR);
		$this->log($primary_language, self::LOG_ERROR);
		return array_shift($strings);
	}

	/**
	 * Returns the proper language code, [a-z]{2}-[A-Z]{2}
	 *
	 * @param string $language
	 * @return string
	 */
	public function getCleanLanguageCode($language = null){
		if ($language == null) {
			$config = $this->registry->get('config');
			if (isset($this->registry->get('session')->data['language'])) {
				$language = $this->registry->get('session')->data['language'];
			}
			else if ($config->has('language_code')) {
				$language = $config->get('language_code');
			}
			else if (!$this->isAdmin() && $config->has('config_language')) {
				$language = $config->get('config_language');
			}
			else if ($config->has('language_default')) {
				$language = $config->get('language_default');
			}
			else if ($this->isAdmin() && $config->has('config_admin_language')) {
				$language = $config->get('config_admin_language');
			}
		}

		$prefixWithDash = substr($language, 0, 3);
		$postfix = strtoupper(substr($language, 3));

		return $prefixWithDash . $postfix;
	}

	/**
	 *
	 * @return PostFinanceCheckout\Sdk\ApiClient
	 */
	public function getApiClient(){
		if ($this->apiClient === null) {
			$this->refreshApiClient();
		}
		return $this->apiClient;
	}

	public function refreshApiClient(){
		$this->apiClient = new PostFinanceCheckout\Sdk\ApiClient($this->registry->get('config')->get('postfinancecheckout_user_id'),
			$this->registry->get('config')->get('postfinancecheckout_application_key'));
		$this->apiClient->setBasePath(self::getBaseUrl() . "/api");
		foreach (self::getDefaultHeaderData() as $key => $value) {
			$this->apiClient->addDefaultHeader($key, $value);
		}
		if ($this->registry->get('config')->get('postfinancecheckout_log_level') >= self::LOG_DEBUG) {
			$this->apiClient->enableDebugging();
			$this->apiClient->setDebugFile(DIR_LOGS . "postfinancecheckout_communication.log");
		}
	}

	public function getCache(){
		return $this->registry->get('cache');
	}

	public function getSuccessUrl(){
		return PostFinanceCheckoutVersionHelper::createUrl($this->getCatalogUrl(), 'checkout/success', array(
			'utm_nooverride' => 1
		), $this->registry->get('config')->get('config_secure'));
	}

	public function getFailedUrl($order_id){
		return str_replace('&amp;', '&',
				PostFinanceCheckoutVersionHelper::createUrl($this->getCatalogUrl(), 'extension/postfinancecheckout/transaction/fail',
						array(
							'order_id' => $order_id,
							'utm_nooverride' => 1
						), $this->registry->get('config')->get('config_secure')));
	}

	public function getWebhookUrl(){
		return PostFinanceCheckoutVersionHelper::createUrl($this->getCatalogUrl(), 'extension/postfinancecheckout/webhook', '',
				$this->registry->get('config')->get('config_secure'));
	}

	/**
	 * Checks if the given order_id exists, is associated with a postfinancecheckout transaction, and the permissions to access it are set.
	 *
	 * @param string $order_id
	 */
	public function isValidOrder($order_id){
		if (!$this->isAdmin()) {
			$order_info = $this->getOrder($order_id);
			if ($this->registry->get('customer') && $this->registry->get('customer')->isLogged() &&
					isset($this->registry->get('session')->data['customer_id'])) {
				if ($this->registry->get('session')->data['customer_id'] != $order_info['customer_id']) {
					return false;
				}
			}
			else {
				return false;
			}
		}
		$transaction_info = \PostFinanceCheckout\Entity\TransactionInfo::loadByOrderId($this->registry, $order_id);
		return $transaction_info->getId() != null;
	}

	/**
	 * "postfinancecheckout_pending_status_id"
	 * "postfinancecheckout_processing_status_id"
	 * "postfinancecheckout_failed_status_id"
	 * "postfinancecheckout_voided_status_id"
	 * "postfinancecheckout_decline_status_id"
	 * "postfinancecheckout_fulfill_status_id"
	 * "postfinancecheckout_confirmed_status_id"
	 * "postfinancecheckout_authorized_status_id"
	 * "postfinancecheckout_completed_status_id"
	 * "postfinancecheckout_refund_status_id"
	 *
	 * @param string $order_id
	 * @param string|int $status Key for postfinancecheckout status mapping, e.g. postfinancecheckout_completed_status_id, or the order status id
	 * which should be applied.
	 * @param string $message
	 * @param boolean $notify
	 * @param boolean $force If the history should be added even if status is still the same.
	 * @throws Exception
	 */
	public function addOrderHistory($order_id, $status, $message = '', $notify = false, $force = false){
		$this->log(__METHOD__ . " (ID: $order_id, Status: $status, Message: $message, Notify: $notify");
		if ($this->isAdmin()) {
			$this->log('Called addOrderHistory from admin context - unsupported.', self::LOG_ERROR);
			throw new Exception("addOrderHistory from admin not supported"); // should never occur. always via webhook
		}
		if (!ctype_digit($status)) {
			$status = $this->registry->get('config')->get($status);
		}
		$this->registry->get('load')->model('checkout/order');
		$model = $this->registry->get('model_checkout_order');
		$order = $model->getOrder($order_id);
		if ($order['order_status_id'] !== $status || $force) {
			$model->addOrderHistory($order_id, $status, $message, $notify);
		}
		else {
			$this->log("Skipped adding order history, same status & !force.");
		}
	}

	public function ensurePaymentCode(array $order_info, \PostFinanceCheckout\Sdk\Model\Transaction $transaction){
		$allowed = $transaction->getAllowedPaymentMethodConfigurations();
		$code = null;
		if (count($allowed) === 1) {
			$code = 'postfinancecheckout_' . $allowed[0];
		}
		else if (!empty($transaction->getPaymentConnectorConfiguration()) &&
				!empty($transaction->getPaymentConnectorConfiguration()->getPaymentMethodConfiguration())) {
			$code = 'postfinancecheckout_' . $transaction->getPaymentConnectorConfiguration()->getPaymentMethodConfiguration()->getId();
		}
		else {
			$this->log("No payment method on transaction, skipping payment code setting.", self::LOG_DEBUG);
			$this->log($transaction, self::LOG_DEBUG);
			return;
		}
		if ($order_info['payment_code'] === $code) {
			return;
		}
		$db = $this->registry->get('db');
		$table = DB_PREFIX . 'order';
		$code = $db->escape($code);
		$title = $db->escape($transaction->getPaymentConnectorConfiguration()->getPaymentMethodConfiguration()->getName());
		$order_id = $db->escape($order_info['order_id']);
		$query = "UPDATE `$table` SET `payment_code`='$code', `payment_method`='$title' WHERE `order_id`='$order_id';";
		$this->log("Changing payment method on order: [" . $query . "], was [" . $order_info['payment_code'] . "]", self::LOG_DEBUG);
		$db->query($query);
	}

	/**
	 *
	 * @return Url
	 */
	private function getCatalogUrl(){
		if ($this->catalog_url === null) {
			if ($this->isAdmin()) {
				$config = $this->registry->get('config');
				$this->catalog_url = new Url($this->getStoreUrl(false), $this->getStoreUrl($config->get('config_secure')));
				$this->catalog_url->addRewrite($this);
			}
			else {
				$this->catalog_url = $this->registry->get('url');
			}
		}
		return $this->catalog_url;
	}

	private function getStoreUrl($ssl = true){
		$config = $this->registry->get('config');
		if ($config->get('config_store_id') == 0) { // zero and null!
			if ($this->isAdmin()) {
				if ($ssl) {
					return HTTPS_CATALOG;
				}
				return HTTP_CATALOG;
			}
			if ($ssl) {
				return HTTPS_SERVER;
			}
			return HTTP_SERVER;
		}
		if ($ssl) {
			return $config->get('config_ssl');
		}
		return $config->get('config_url');
	}

	public function rewrite($url){
		return str_replace(array(
			HTTP_SERVER,
			HTTPS_SERVER
		), array(
			HTTP_CATALOG,
			HTTPS_CATALOG
		), $url);
	}

	public function isAdmin(){
		return defined('HTTPS_CATALOG') && defined('HTTP_CATALOG');
	}

	/**
	 * Get the starting value of LIMIT for db queries.
	 * Used for paginated requests.
	 *
	 * @param int $page
	 * @return int
	 */
	public function getLimitStart($page){
		$limit = $this->registry->get('config')->get('config_limit_admin');
		return ($page - 1) * $limit;
	}

	/**
	 * Get the end value of LIMIT for db queries.
	 * Used for paginated requests.
	 *
	 * @param int $page
	 * @return int
	 */
	public function getLimitEnd($page){
		$limit = $this->registry->get('config')->get('config_limit_admin');
		return $page * $limit;
	}

	/**
	 * Disable inc vat setting in xfeepro.
	 * Necessary to ensure taxes are calculated and transmitted correctly.
	 */
	public function xfeeproDisableIncVat(){
		$config = $this->registry->get('config');
		$xfeepro = $config->get('xfeepro');
		if ($xfeepro) {
			$xfeepro = unserialize(base64_decode($xfeepro));
			$this->xfeepro = $xfeepro;
			if (isset($xfeepro['inc_vat'])) {
				foreach ($xfeepro['inc_vat'] as $i => $value) {
					$xfeepro['inc_vat'][$i] = 0;
				}
			}
			$config->set('xfeepro', base64_encode(serialize($xfeepro)));
		}
	}

	/**
	 * Restore xfeepro settings.
	 */
	public function xfeeProRestoreIncVat(){
		if ($this->xfeepro) {
			$this->registry->get('config')->set('xfeepro', base64_encode(serialize($this->xfeepro)));
		}
	}

	public static function instance(Registry $registry){
		if (self::$instance === null) {
			self::$instance = new self($registry);
		}
		return self::$instance;
	}

	public static function extractPaymentMethodId($code){
		return substr($code, strlen('postfinancecheckout_'));
	}

	public static function exceptionErrorHandler($severity, $message, $file, $line){
		if (!(error_reporting() & $severity)) {
			// This error code is not included in error_reporting
			return false;
		}
		throw new \ErrorException($message, 0, $severity, $file, $line);
	}

	public static function getBaseUrl(){
		return rtrim("https://checkout.postfinance.ch/", '/');
	}

	public static function isEditableState($state){
		$completable_states = array(
			\PostFinanceCheckout\Sdk\Model\TransactionState::AUTHORIZED,
			\PostFinanceCheckout\Sdk\Model\TransactionState::CONFIRMED,
			\PostFinanceCheckout\Sdk\Model\TransactionState::PROCESSING
		);
		return in_array($state, $completable_states);
	}

	public static function generateToken($tokenLength = 10){
		$token = '';
		static $characters;
		if (!$characters) {
			$characters = shuffle(array_merge(range('0', '9'), range('a', 'z'), range('A', 'Z')));
		}
		static $max;
		if (!$max) {
			$max = count($characters);
		}
		for ($i = 0; $i < $tokenLength; $i++) {
			$token .= $characters[mt_rand(0, $max)];
		}
		return $token;
	}

	public static function generateUuid(){
		return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000,
				mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
	}

	public static function mergeArray(array $first, array $second){
		$result = array();
		foreach ($first as $key => $value) {
			if (is_array($value)) {
				if (isset($second[$key]) && is_array($second[$key])) {
					$result[$key] = self::mergeArray($value, $second[$key]);
				}
				else {
					$result[$key] = $value;
				}
			}
			elseif (!($value === null || $value === '')) {
				$result[$key] = $value;
			}
			else {
				if (isset($second[$key])) {
					$secondValue = $second[$key];
					if (!($secondValue === null || $secondValue === '')) {
						$result[$key] = $secondValue;
					}
					else {
						$result[$key] = $value;
					}
				}
				else {
					$result[$key] = $value;
				}
			}
		}
		foreach ($second as $key => $value) {
			if (!isset($result[$key])) {
				$result[$key] = $value;
			}
		}
		return $result;
	}
	
	
	/**
	 * @return array
	 */
	protected static function getDefaultHeaderData()
	{
		$shop_version = VERSION;
		[$major_version, $minor_version, $_] = explode('.', $shop_version, 3);
		return [
			self::SHOP_SYSTEM             => 'opencart-3',
			self::SHOP_SYSTEM_VERSION     => $shop_version,
			self::SHOP_SYSTEM_AND_VERSION => 'opencart-' . $major_version . '.' . $minor_version,
		];
	}
}
