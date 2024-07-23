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
require_once modification(DIR_SYSTEM . 'library/postfinancecheckout/helper.php');
use PostFinanceCheckout\Model\AbstractModel;
use PostFinanceCheckout\Entity\TransactionInfo;
use PostFinanceCheckout\Provider\PaymentMethod;

class ModelExtensionPostFinanceCheckoutTransaction extends AbstractModel {
	const DATE_FORMAT = 'Y-m-d H:i:s';

	public function loadList(array $filters){
		$transactionInfoList = TransactionInfo::loadByFilters($this->registry, $filters);
		/* @var $transactionInfoList TransactionInfo[] */
		$transactions = array();
		foreach ($transactionInfoList as $transactionInfo) {
			$paymentMethod = PaymentMethod::instance($this->registry)->find($transactionInfo->getPaymentMethodId());
			if ($paymentMethod) {
				$paymentMethodName = PostFinanceCheckoutHelper::instance($this->registry)->translate($paymentMethod->getName()) . " (" . $transactionInfo->getPaymentMethodId() . ")";
			}
			else {
				$paymentMethodName = $transactionInfo->getPaymentMethodId();
			}
			$transactions[] = array(
				'id' => $transactionInfo->getId(),
				'order_id' => $transactionInfo->getOrderId(),
				'transaction_id' => $transactionInfo->getTransactionId(),
				'space_id' => $transactionInfo->getSpaceId(),
				'space_view_id' => $transactionInfo->getSpaceViewId(),
				'state' => $transactionInfo->getState(),
				'authorization_amount' => $transactionInfo->getAuthorizationAmount(),
				'created_at' => $transactionInfo->getCreatedAt()->format(self::DATE_FORMAT),
				'updated_at' => $transactionInfo->getUpdatedAt()->format(self::DATE_FORMAT),
				'payment_method' => $paymentMethodName,
				'view' => PostFinanceCheckoutVersionHelper::createUrl($this->url, 'sale/order/info',
						array(
							'user_token' => $this->session->data['user_token'],
							'order_id' => $transactionInfo->getOrderId() 
						), true) 
			);
		}
		return $transactions;
	}
	
	public function getOrderStatuses() {
		return array(
			'',
			PostFinanceCheckout\Sdk\Model\TransactionState::AUTHORIZED,
			PostFinanceCheckout\Sdk\Model\TransactionState::COMPLETED,
			PostFinanceCheckout\Sdk\Model\TransactionState::CONFIRMED,
			PostFinanceCheckout\Sdk\Model\TransactionState::CREATE,
			PostFinanceCheckout\Sdk\Model\TransactionState::DECLINE,
			PostFinanceCheckout\Sdk\Model\TransactionState::FULFILL,
			PostFinanceCheckout\Sdk\Model\TransactionState::FAILED,
			PostFinanceCheckout\Sdk\Model\TransactionState::PENDING,
			PostFinanceCheckout\Sdk\Model\TransactionState::PROCESSING,
			PostFinanceCheckout\Sdk\Model\TransactionState::AUTHORIZED,
			PostFinanceCheckout\Sdk\Model\TransactionState::VOIDED,
		);
	}
	
	public function countRows() {
		return TransactionInfo::countRows($this->registry);
	}
}