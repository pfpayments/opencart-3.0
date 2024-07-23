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

namespace PostFinanceCheckout\Controller;

/**
 * Base controller class offering a validate method and wrappers for functions which may differ between versions (redirect, link etc.)
 *
 * The validate method checks if permissions are set (if in admin)
 * If the order id is set in the request->get[] array,
 * If the order id is part of a postfinancecheckout transaction,
 * If the user (non admin) is the owner of the given order.
 */
abstract class AbstractController extends \Controller {

	protected function loadView($template, $data = array()){
	    $template = \PostFinanceCheckoutVersionHelper::getTemplate($this->config->get('config_template'), $template);
	    return $this->load->view($template, $data);
	}

	protected function validate(){
		$this->language->load('extension/payment/postfinancecheckout');
		$this->validatePermission();
		$this->validateOrder();
	}

	protected function validatePermission(){
		if (\PostFinanceCheckoutHelper::instance($this->registry)->isAdmin()) {
			if (!$this->user->hasPermission('access', $this->getRequiredPermission())) {
				throw new \Exception($this->language->get('error_permission'));
			}
		}
	}

	protected function displayError($message){
		$variables = $this->getAdminSurroundingTemplates();
		$variables['text_error'] = $message;
		$this->response->setOutput($this->loadView("extension/postfinancecheckout/error", $variables));
	}

	protected function getAdminSurroundingTemplates(){
		return array(
			'header' => $this->load->controller("common/header"),
			'column_left' => $this->load->controller("common/column_left"),
			'footer' => $this->load->controller("common/footer") 
		);
	}

	protected function validateOrder(){
		if (!isset($this->request->get['order_id'])) {
			throw new \Exception($this->language->get('error_order_id'));
		}
		if (!\PostFinanceCheckoutHelper::instance($this->registry)->isValidOrder($this->request->get['order_id'])) {
			throw new \Exception($this->language->get('error_not_postfinancecheckout'));
		}
	}

	protected function createUrl($route, $query){
		return \PostFinanceCheckoutVersionHelper::createUrl($this->url, $route, $query, $this->config->get('config_secure'));
	}

	protected abstract function getRequiredPermission();
}