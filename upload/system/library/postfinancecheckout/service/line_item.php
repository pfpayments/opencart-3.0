<?php

namespace PostFinanceCheckout\Service;

use PostFinanceCheckout\Sdk\Model\LineItemCreate;
use PostFinanceCheckout\Sdk\Model\LineItemType;
use PostFinanceCheckout\Sdk\Model\TaxCreate;

/**
 * This service provides methods to handle manual tasks.
 */
class LineItem extends AbstractService {
	private $tax;
	private $fixed_taxes = array();
	private $sub_total;
	private $products;
	private $shipping;
	private $coupon;
	private $coupon_total;
	private $voucher;
	private $total;
	private $xfeepro;

	public static function instance(\Registry $registry){
		return new self($registry);
	}

	/**
	 * Gets the current order items, with all succesfull refunds applied.
	 *
	 * @param array $order_info
	 * @param int $transaction_id
	 * @param int $space_id
	 * @return \PostFinanceCheckout\Sdk\Model\LineItemCreate[]
	 */
	public function getReducedItemsFromOrder(array $order_info, $transaction_id, $space_id){
		$this->tax = \PostFinanceCheckoutVersionHelper::newTax($this->registry);
		$this->tax->setShippingAddress($order_info['shipping_country_id'], $order_info['shipping_zone_id']);
		$this->tax->setPaymentAddress($order_info['payment_country_id'], $order_info['payment_zone_id']);
		$this->coupon_total = 0;
		
		\PostFinanceCheckoutHelper::instance($this->registry)->xfeeproDisableIncVat();
		$line_items = $this->getItemsFromOrder($order_info, $transaction_id, $space_id);
		\PostFinanceCheckoutHelper::instance($this->registry)->xfeeproRestoreIncVat();
		
		// get all succesfully reduced items
		$refund_jobs = \PostFinanceCheckout\Entity\RefundJob::loadByOrder($this->registry, $order_info['order_id']);
		$reduction_items = array();
		foreach ($refund_jobs as $refund) {
			if ($refund->getState() != \PostFinanceCheckout\Entity\RefundJob::STATE_FAILED_CHECK &&
					 $refund->getState() != \PostFinanceCheckout\Entity\RefundJob::STATE_FAILED_DONE) {
				foreach ($refund->getReductionItems() as $already_reduced) {
					if (!isset($reduction_items[$already_reduced->getLineItemUniqueId()])) {
						$reduction_items[$already_reduced->getLineItemUniqueId()] = array(
							'quantity' => 0,
							'unit_price' => 0 
						);
					}
					$reduction_items[$already_reduced->getLineItemUniqueId()]['quantity'] += $already_reduced->getQuantityReduction();
					$reduction_items[$already_reduced->getLineItemUniqueId()]['unit_price'] += $already_reduced->getUnitPriceReduction();
				}
			}
		}
		
		// remove them from available items
		foreach ($line_items as $key => $line_item) {
			if (isset($reduction_items[$line_item->getUniqueId()])) {
				if ($reduction_items[$line_item->getUniqueId()]['quantity'] == $line_item->getQuantity()) {
					unset($line_items[$key]);
				}
				else {
					$unit_price = $line_item->getAmountIncludingTax() / $line_item->getQuantity();
					$unit_price -= $reduction_items[$line_item->getUniqueId()]['unit_price'];
					$line_item->setQuantity($line_item->getQuantity() - $reduction_items[$line_item->getUniqueId()]['quantity']);
					$line_item->setAmountIncludingTax($unit_price * $line_item->getQuantity());
				}
			}
		}
		return $line_items;
	}

	public function getItemsFromOrder(array $order_info){
		$this->tax = \PostFinanceCheckoutVersionHelper::newTax($this->registry);
		$this->tax->setShippingAddress($order_info['shipping_country_id'], $order_info['shipping_zone_id']);
		$this->tax->setPaymentAddress($order_info['payment_country_id'], $order_info['payment_zone_id']);
		
		$transaction_info = \PostFinanceCheckout\Entity\TransactionInfo::loadByOrderId($this->registry, $order_info['order_id']);
		$order_model = \PostFinanceCheckoutHelper::instance($this->registry)->getOrderModel();
		
		$order_total = 0;
		$items = array();
		
		$this->coupon_total = 0;
		$this->fixed_taxes = array();
		$this->products = $order_model->getOrderProducts($order_info['order_id']);
		$voucher = $order_model->getOrderVouchers($order_info['order_id']);
		// only one voucher possible (see extension total voucher)
		if (!empty($voucher)) {
			$this->voucher = $voucher[0];
		}
		else {
			$this->voucher = false;
		}
		$shipping_info = \PostFinanceCheckout\Entity\ShippingInfo::loadByTransaction($this->registry, $transaction_info->getSpaceId(),
				$transaction_info->getTransactionId());
		if ($shipping_info->getId()) {
			$this->shipping = array(
				'title' => $order_info['shipping_method'],
				'code' => $order_info['shipping_code'],
				'cost' => $shipping_info->getCost(),
				'tax_class_id' => $shipping_info->getTaxClassId() 
			);
		}
		else {
			$this->shipping = false;
		}
		$this->total = $order_model->getOrderTotals($order_info['order_id']);
		
		$sub_total = 0;
		foreach ($this->total as $total) {
			if ($total['code'] == 'sub_total') {
				$sub_total = $total['value'];
				break;
			}
		}
		$this->sub_total = $sub_total;
				
		$this->coupon = $this->getCoupon($transaction_info->getCouponCode(), $sub_total, $order_info['customer_id']);
		
		return $this->createLineItems($order_info['currency_code']);
	}

	public function getItemsFromSession(){
		$this->tax = $this->registry->get('tax');
		
		$session = $this->registry->get('session');
		if (isset($session->data['shipping_country_id']) && isset($session->data['shipping_country_id'])) {
			$this->tax->setShippingAddress($session->data['shipping_country_id'], $session->data['shipping_zone_id']);
		}
		if (isset($session->data['payment_country_id']) && isset($session->data['payment_zone_id'])) {
			$this->tax->setPaymentAddress($session->data['payment_country_id'], $session->data['payment_zone_id']);
		}
		$this->products = $this->registry->get('cart')->getProducts();
		
		if (!empty($this->registry->get('session')->data['vouchers'])) {
			$voucher = current($this->registry->get('session')->data['vouchers']);
		}
		if (!empty($voucher)) {
			$this->voucher = $voucher[0];
		}
		else {
			$this->voucher = false;
		}
		
		if (!empty($this->registry->get('session')->data['shipping_method'])) {
			$this->shipping = $this->registry->get('session')->data['shipping_method'];
		}
		else {
			$this->shipping = false;
		}
		
		\PostFinanceCheckoutHelper::instance($this->registry)->xfeeproDisableIncVat();
		$this->total = \PostFinanceCheckoutVersionHelper::getSessionTotals($this->registry);
		\PostFinanceCheckoutHelper::instance($this->registry)->xfeeProRestoreIncVat();
		
		$sub_total = 0;
		foreach ($this->total as $total) {
			if ($total['code'] == 'sub_total') {
				$sub_total = $total['value'];
				break;
			}
		}
		$this->sub_total = $sub_total;
		
		if (isset($this->registry->get('session')->data['coupon']) && isset($this->registry->get('session')->data['customer_id'])) {
			$this->coupon = $this->getCoupon($this->registry->get('session')->data['coupon'], $sub_total,
					$this->registry->get('session')->data['customer_id']);
		}
		else {
			$this->coupon = false;
		}
		
		return $this->createLineItems(\PostFinanceCheckoutHelper::instance($this->registry)->getCurrency());
	}

	private function createLineItems($currency_code){
		$calculated_total = 0;
		foreach ($this->products as $product) {
			$items[] = $item = $this->createLineItemFromProduct($product);
			$calculated_total += $item->getAmountIncludingTax();
		}
		
		if ($this->voucher) {
			$items[] = $item = $this->createLineItemFromVoucher();
			$calculated_total += $item->getAmountIncludingTax();
		}
		
		if($this->coupon) {
			$items[] = $item  = $this->createLineItemFromCoupon();
			$calculated_total += $item->getAmountIncludingTax();
		}
		
		if ($this->shipping) {
			$items[] = $item = $this->createLineItemFromShipping();
			$calculated_total += $item->getAmountIncludingTax();
		}
		
		$expected_total = 0;
		// attempt to add 3rd party totals
		foreach ($this->total as $total) {
			
			if (strncmp($total['code'], 'xfee', strlen('xfee')) === 0) {
				$items[] = $item = $this->createXFeeLineItem($total);
				$calculated_total += $item->getAmountIncludingTax();
			}
			else if (!in_array($total['code'], array(
				'total',
				'shipping',
				'sub_total',
				'coupon',
				'tax' 
			))) {
				if ($total['value'] != 0) {
					$items[] = $item = $this->createLineItemFromTotal($total);
					$calculated_total += $item->getAmountIncludingTax();
				}
			}
			else if ($total['code'] == 'total') {
				$expected_total = $total['value'];
			}
		}
		
		foreach ($this->fixed_taxes as $key => $tax) {
			$items[] = $item = $this->createLineItemFromFee($tax, $key);
			$calculated_total += $item->getAmountIncludingTax();
		}
		
		// 		only check amount if currency is base currency. Otherwise, rounding errors are expected to occur due to Opencart standard
		if ($this->registry->get('currency')->getValue($currency_code) == 1) {
			$expected_total = \PostFinanceCheckoutHelper::instance($this->registry)->formatAmount($expected_total);
			
			if (!\PostFinanceCheckoutHelper::instance($this->registry)->areAmountsEqual($calculated_total, $expected_total, $currency_code)) {
				\PostFinanceCheckoutHelper::instance($this->registry)->log(
						"Invalid order total calculated. Calculated total: $calculated_total, Expected total: $expected_total.",
						\PostFinanceCheckoutHelper::LOG_ERROR);
				\PostFinanceCheckoutHelper::instance($this->registry)->log(array(
					'Products' => $this->products 
				), \PostFinanceCheckoutHelper::LOG_ERROR);
				\PostFinanceCheckoutHelper::instance($this->registry)->log(array(
					'Voucher' => $this->voucher 
				), \PostFinanceCheckoutHelper::LOG_ERROR);
				\PostFinanceCheckoutHelper::instance($this->registry)->log(array(
					'Coupon' => $this->coupon 
				), \PostFinanceCheckoutHelper::LOG_ERROR);
				\PostFinanceCheckoutHelper::instance($this->registry)->log(array(
					'Totals' => $this->total 
				), \PostFinanceCheckoutHelper::LOG_ERROR);
				\PostFinanceCheckoutHelper::instance($this->registry)->log(array(
					'Fixed taxes' => $this->fixed_taxes 
				), \PostFinanceCheckoutHelper::LOG_ERROR);
				\PostFinanceCheckoutHelper::instance($this->registry)->log(array(
					'Shipping' => $this->shipping 
				), \PostFinanceCheckoutHelper::LOG_ERROR);
				\PostFinanceCheckoutHelper::instance($this->registry)->log(array(
					'postfinancecheckout Items' => $items 
				), \PostFinanceCheckoutHelper::LOG_ERROR);
				
				throw new \Exception("Invalid order total.");
			}
		}
		
		return $items;
	}

	private function createLineItemFromFee($fee, $id){
		$line_item = new LineItemCreate();
		
		$line_item->setName($fee['name']);
		$line_item->setSku($fee['code']);
		$line_item->setUniqueId($id);
		$line_item->setQuantity($fee['quantity']);
		$line_item->setType(LineItemType::FEE);
		$line_item->setAmountIncludingTax(\PostFinanceCheckoutHelper::instance($this->registry)->formatAmount($fee['amount']));
		
		return $this->cleanLineItem($line_item);
	}

	private function createLineItemFromTotal($total){
		$line_item = new LineItemCreate();
		
		$line_item->setName($total['title']);
		$line_item->setSku($total['code']);
		$line_item->setUniqueId($total['code']);
		$line_item->setQuantity(1);
		$line_item->setType(LineItemType::FEE);
		$line_item->setAmountIncludingTax(\PostFinanceCheckoutHelper::instance($this->registry)->formatAmount($total['value']));
		
		return $this->cleanLineItem($line_item);
	}

	private function createXFeeLineItem($total){
		$config = $this->registry->get('config');
		$line_item = new LineItemCreate();
		$line_item->setName($total['title']);
		$line_item->setSku($total['code']);
		$line_item->setUniqueId($this->createUniqueIdFromXfee($total));
		$line_item->setQuantity(1);
		$line_item->setType(LineItemType::FEE);
		if ($total['value'] < 0) {
			$line_item->setType(LineItemType::DISCOUNT);
		}
		$line_item->setAmountIncludingTax(
				\PostFinanceCheckoutHelper::instance($this->registry)->formatAmount(
						\PostFinanceCheckoutHelper::instance($this->registry)->roundXfeeAmount($total['value'])));
		$taxClass = $this->getXfeeTaxClass($total);
		if ($taxClass) {
			$tax_amount = $this->addTaxesToLineItem($line_item, $total['value'], $taxClass);
			$line_item->setAmountIncludingTax(\PostFinanceCheckoutHelper::instance($this->registry)->formatAmount($total['value'] + $tax_amount));
		}
		return $this->cleanLineItem($line_item);
	}

	private function createUniqueIdFromXfee($total){
		if (isset($total['xcode'])) {
			return $total['xcode'];
		}
		else {
			return substr($total['code'] . preg_replace("/\W/", "-", $total['title']), 0, 200);
		}
	}

	private function getXfeeTaxClass($total){
		$config = $this->registry->get('config');
		if ($total['code'] == 'xfee') {
			for ($i = 0; $i < 12; $i++) {
				// TODO value comparison percentages
				if ($config->get('xfee_name' . $i) == $total['title'] /* && $config->get('xfee_value') == $total['value']*/) {
					return $config->get('xfee_tax_class_id' . $i);
				}
			}
		}
		else if ($total['code'] == 'xfeepro') {
			$i = substr($total['xcode'], strlen('xfeepro.xfeepro'));
			$xfeepro = $this->getXfeePro();
			return $xfeepro['tax_class_id'][$i];
		}
		return null;
	}
	
	private function getXfeePro() {
		if($this->xfeepro === null) {
			$config = $this->registry->get('config');
			$this->xfeepro = $xfeepro = unserialize(base64_decode($config->get('xfeepro')));
		}
		return $this->xfeepro;
	}

	private function createLineItemFromProduct($product){
		$line_item = new LineItemCreate();
		$amount_excluding_tax = $product['total'];
		
		$product['tax_class_id'] = $this->getTaxClassByProductId($product['product_id']);
		
		if ($this->coupon && (!$this->coupon['product'] || in_array($product['product_id'], $this->coupon['product']))) {
			if ($this->coupon['type'] == 'F') {
				$discount = $this->coupon['discount'] * ($product['total'] / $this->sub_total);
			}
			elseif ($this->coupon['type'] == 'P') {
				$discount = $product['total'] / 100 * $this->coupon['discount'];
			}
			$this->coupon_total -= $discount;
			$line_item->setAttributes(array(
				"coupon" => new \PostFinanceCheckout\Sdk\Model\LineItemAttributeCreate(array(
					'label' => $this->coupon['name'],
					'value' => $discount
				))
			));
		}
		
		$line_item->setName($product['name']);
		$line_item->setQuantity($product['quantity']);
		$line_item->setShippingRequired(isset($product['shipping']) && $product['shipping']);
		if (isset($product['sku'])) {
			$line_item->setSku($product['sku']);
		}
		else {
			$line_item->setSku($product['model']);
		}
		$line_item->setUniqueId($this->createUniqueIdFromProduct($product));
		$line_item->setType(LineItemType::PRODUCT);
		
		$tax_amount = $this->addTaxesToLineItem($line_item, $amount_excluding_tax, $product['tax_class_id']);
		$line_item->setAmountIncludingTax(\PostFinanceCheckoutHelper::instance($this->registry)->formatAmount($amount_excluding_tax + $tax_amount));
		
		return $this->cleanLineItem($line_item);
	}

	private function createUniqueIdFromProduct($product){
		$id = $product['product_id'];
		if(isset($product['option'])) {
			foreach ($product['option'] as $option) {
				$hasValue = false;
				if (isset($option['product_option_id'])) {
					$id .= '_po-' . $option['product_option_id'];
					if (isset($option['product_option_value_id'])) {
						$id .= '=' . $option['product_option_value_id'];
					}
				}
				if (isset($option['option_id']) && isset($option['option_value_id'])) {
					$id .= '_o-' . $option['option_id'];
					if (isset($option['option_value_id']) && !empty($option['option_value_id'])) {
						$id .= '=' . $option['option_value_id'];
					}
				}
				if (isset($option['value']) && !$hasValue) {
					$id .= '_v=' . $option['value'];
				}
			}
		}
		return $id;
	}
	
	private function createLineItemFromCoupon(){
		$line_item = new LineItemCreate();
		
		$line_item->setName($this->coupon['name']);
		$line_item->setQuantity(1);
		$line_item->setType(LineItemType::DISCOUNT);
		$line_item->setSKU($this->coupon['code']);
		$line_item->setUniqueId($this->coupon['coupon_id']);
		$line_item->setAmountIncludingTax(\PostFinanceCheckoutHelper::instance($this->registry)->formatAmount($this->coupon_total));
		
		return $this->cleanLineItem($line_item);
	}
	
	private function createLineItemFromVoucher(){
		$line_item = new LineItemCreate();
		
		$line_item->setName($this->voucher['name']);
		$line_item->setQuantity(1);
		$line_item->setType(LineItemType::DISCOUNT);
		$line_item->setSKU($this->voucher['code']);
		$line_item->setUniqueId($this->voucher['code']);
		$line_item->setAmountIncludingTax(\PostFinanceCheckoutHelper::instance($this->registry)->formatAmount($this->voucher['amount']));
		
		return $this->cleanLineItem($line_item);
	}

	private function createLineItemFromShipping(){
		$line_item = new LineItemCreate();
		
		$amount_excluding_tax = $this->shipping['cost'];
		
		if ($this->coupon && $this->coupon['shipping']) {
			$amount_excluding_tax = 0;
		}
		
		$line_item->setName($this->shipping['title']);
		$line_item->setSku($this->shipping['code']);
		$line_item->setUniqueId($this->shipping['code']);
		$line_item->setType(LineItemType::SHIPPING);
		$line_item->setQuantity(1);
		
		$tax_amount = $this->addTaxesToLineItem($line_item, $amount_excluding_tax, $this->shipping['tax_class_id']);
		$line_item->setAmountIncludingTax(\PostFinanceCheckoutHelper::instance($this->registry)->formatAmount($amount_excluding_tax + $tax_amount));
		
		return $this->cleanLineItem($line_item);
	}

	/**
	 * Adds taxes to the line item, while fixed taxes are added as attributes.
	 * Call after setting line item quantity.
	 * Returns the total tax amount for the given item.
	 *
	 * @param LineItemCreate $line_item
	 * @param float $total
	 * @param int $tax_class_id
	 * @return float
	 */
	private function addTaxesToLineItem(LineItemCreate $line_item, $total, $tax_class_id){
		$tax_amount = 0;
		$rates = $this->tax->getRates($total, $tax_class_id);
		$taxes = array();
		foreach ($rates as $rate) {
			// P = percentage
			if ($rate['type'] == 'P') {
				$tax_amount += $rate['amount'];
				$taxes[] = new TaxCreate(array(
					'rate' => $rate['rate'],
					'title' => $rate['name'] 
				));
			}
			// F = fixed
			else if ($rate['type'] == 'F') {
				$key = preg_replace("/[^\w_]/", "", $rate['name']);
				$amount = $rate['amount'] * $line_item->getQuantity();
				
				if (isset($this->fixed_taxes[$key])) {
					$this->fixed_taxes[$key]['amount'] += $amount;
					$this->fixed_taxes[$key]['quantity'] += $line_item->getQuantity();
				}
				else {
					$this->fixed_taxes[$key] = array(
						'code' => $key,
						'name' => $rate['name'],
						'amount' => $amount,
						'quantity' => $line_item->getQuantity() 
					);
				}
			}
		}
		$line_item->setTaxes($taxes);
		return $tax_amount;
	}

	/**
	 * Near-Duplicate code from model/extension/total/coupon/getCoupon
	 * Expects sub_total instead of calculating based on cart.
	 * Expects customer_id instead of retrieving from session.
	 *
	 * @param unknown $code
	 * @param unknown $sub_total
	 * @param unknown $customer_id
	 * @return NULL[]|unknown[][]|array
	 */
	private function getCoupon($code, $sub_total, $customer_id){
		$db = $this->registry->get('db');
		$status = true;
		
		$coupon_query = $db->query(
				"SELECT * FROM `" . DB_PREFIX . "coupon` WHERE code = '" . $db->escape($code) .
						 "' AND ((date_start = '0000-00-00' OR date_start < NOW()) AND (date_end = '0000-00-00' OR date_end > NOW())) AND status = '1'");
		
		if ($coupon_query->num_rows) {
			if ($coupon_query->row['total'] > $sub_total) {
				$status = false;
			}
			
			$coupon_total = $this->getTotalCouponHistoriesByCoupon($code);
			
			if ($coupon_query->row['uses_total'] > 0 && ($coupon_total >= $coupon_query->row['uses_total'])) {
				$status = false;
			}
			
			if ($coupon_query->row['logged'] && !$customer_id) {
				$status = false;
			}
			
			if ($customer_id) {
				$customer_total = $this->getTotalCouponHistoriesByCustomerId($code, $customer_id);
				
				if ($coupon_query->row['uses_customer'] > 0 && ($customer_total >= $coupon_query->row['uses_customer'])) {
					$status = false;
				}
			}
			
			// Products
			$coupon_product_data = array();
			
			$coupon_product_query = $db->query(
					"SELECT * FROM `" . DB_PREFIX . "coupon_product` WHERE coupon_id = '" . (int) $coupon_query->row['coupon_id'] . "'");
			
			foreach ($coupon_product_query->rows as $product) {
				$coupon_product_data[] = $product['product_id'];
			}
			
			// Categories
			$coupon_category_data = array();
			
			$coupon_category_query = $db->query(
					"SELECT * FROM `" . DB_PREFIX . "coupon_category` cc LEFT JOIN `" . DB_PREFIX .
							 "category_path` cp ON (cc.category_id = cp.path_id) WHERE cc.coupon_id = '" . (int) $coupon_query->row['coupon_id'] . "'");
			
			foreach ($coupon_category_query->rows as $category) {
				$coupon_category_data[] = $category['category_id'];
			}
			
			$product_data = array();
			
			if ($coupon_product_data || $coupon_category_data) {
				foreach ($this->products as $product) {
					if (in_array($product['product_id'], $coupon_product_data)) {
						$product_data[] = $product['product_id'];
						
						continue;
					}
					
					foreach ($coupon_category_data as $category_id) {
						$coupon_category_query = $db->query(
								"SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "product_to_category` WHERE `product_id` = '" .
										 (int) $product['product_id'] . "' AND category_id = '" . (int) $category_id . "'");
						
						if ($coupon_category_query->row['total']) {
							$product_data[] = $product['product_id'];
							
							continue;
						}
					}
				}
				
				if (!$product_data) {
					$status = false;
				}
			}
		}
		else {
			$status = false;
		}
		
		if ($status) {
			return array(
				'coupon_id' => $coupon_query->row['coupon_id'],
				'code' => $coupon_query->row['code'],
				'name' => $coupon_query->row['name'],
				'type' => $coupon_query->row['type'],
				'discount' => $coupon_query->row['discount'],
				'shipping' => $coupon_query->row['shipping'],
				'total' => $coupon_query->row['total'],
				'product' => $product_data,
				'date_start' => $coupon_query->row['date_start'],
				'date_end' => $coupon_query->row['date_end'],
				'uses_total' => $coupon_query->row['uses_total'],
				'uses_customer' => $coupon_query->row['uses_customer'],
				'status' => $coupon_query->row['status'],
				'date_added' => $coupon_query->row['date_added'] 
			);
		}
		else {
			return array();
		}
	}

	private function getTotalCouponHistoriesByCoupon($coupon){
		$query = $this->registry->get('db')->query(
				"SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "coupon_history` ch LEFT JOIN `" . DB_PREFIX .
						 "coupon` c ON (ch.coupon_id = c.coupon_id) WHERE c.code = '" . $this->registry->get('db')->escape($coupon) . "'");
		
		return $query->row['total'];
	}

	private function getTotalCouponHistoriesByCustomerId($coupon, $customer_id){
		$query = $this->registry->get('db')->query(
				"SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "coupon_history` ch LEFT JOIN `" . DB_PREFIX .
						 "coupon` c ON (ch.coupon_id = c.coupon_id) WHERE c.code = '" . $this->registry->get('db')->escape($coupon) .
						 "' AND ch.customer_id = '" . (int) $customer_id . "'");
		
		return $query->row['total'];
	}

	/**
	 * Cleans the given line item for it to meet the API's requirements.
	 *
	 * @param \PostFinanceCheckout\Sdk\Model\LineItemCreate $lineItem
	 * @return \PostFinanceCheckout\Sdk\Model\LineItemCreate
	 */
	private function cleanLineItem(LineItemCreate $line_item){
		$line_item->setSku($this->fixLength($line_item->getSku(), 200));
		$line_item->setName($this->fixLength($line_item->getName(), 40));
		return $line_item;
	}

	private function getTaxClassByProductId($product_id){
		$table = DB_PREFIX . 'product';
		$product_id = $this->registry->get('db')->escape($product_id);
		$query = "SELECT tax_class_id FROM $table WHERE product_id='$product_id';";
		$result = $this->registry->get('db')->query($query);
		return $result->row['tax_class_id'];
	}
}