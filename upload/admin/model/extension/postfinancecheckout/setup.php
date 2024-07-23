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

class ModelExtensionPostFinanceCheckoutSetup extends AbstractModel {

	public function install(){
		$this->load->model("extension/postfinancecheckout/migration");
		$this->load->model('extension/postfinancecheckout/modification');
		$this->load->model('extension/postfinancecheckout/dynamic');
		
		$this->model_extension_postfinancecheckout_migration->migrate();
		
		try {
			$this->model_extension_postfinancecheckout_modification->install();
			$this->model_extension_postfinancecheckout_dynamic->install();
		}
		catch (Exception $e) {
		}
		
		$this->addPermissions();
		$this->addEvents();
	}

	public function synchronize($space_id){
		\PostFinanceCheckoutHelper::instance($this->registry)->refreshApiClient();
		\PostFinanceCheckoutHelper::instance($this->registry)->refreshWebhook();
		\PostFinanceCheckout\Service\MethodConfiguration::instance($this->registry)->synchronize($space_id);
	}

	public function uninstall($purge = true){
		$this->load->model("extension/postfinancecheckout/migration");
		$this->load->model('extension/postfinancecheckout/modification');
		$this->load->model('extension/postfinancecheckout/dynamic');
		
		$this->model_extension_postfinancecheckout_dynamic->uninstall();
		if ($purge) {
			$this->model_extension_postfinancecheckout_migration->purge();
		}
		$this->model_extension_postfinancecheckout_modification->uninstall();
		
		$this->removeEvents();
		$this->removePermissions();
	}

	private function addEvents(){
		$this->getEventModel()->addEvent('postfinancecheckout_create_dynamic_files', 'admin/controller/marketplace/modification/after',
				'extension/postfinancecheckout/event/createMethodConfigurationFiles');
		$this->getEventModel()->addEvent('postfinancecheckout_can_save_order', 'catalog/model/checkout/order/editOrder/before',
				'extension/postfinancecheckout/event/canSaveOrder');
		$this->getEventModel()->addEvent('postfinancecheckout_update_items_after_edit', 'catalog/controller/api/order/edit/after', 'extension/postfinancecheckout/event/update');
		$this->getEventModel()->addEvent('postfinancecheckout_include_scripts', 'catalog/controller/common/header/before',
				'extension/postfinancecheckout/event/includeScripts');
	}

	private function removeEvents(){
		$this->getEventModel()->deleteEventByCode('postfinancecheckout_create_dynamic_files');
		$this->getEventModel()->deleteEventByCode('postfinancecheckout_can_save_order');
		$this->getEventModel()->deleteEventByCode('postfinancecheckout_update_items_after_edit');
		$this->getEventModel()->deleteEventByCode('postfinancecheckout_include_scripts');
	}

	/**
	 * Adds basic permissions.
	 * Permissions per payment method are added while creating the dynamic files.
	 */
	private function addPermissions(){
		$this->load->model("user/user_group");
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/postfinancecheckout/event');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/postfinancecheckout/completion');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/postfinancecheckout/void');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/postfinancecheckout/refund');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/postfinancecheckout/update');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/postfinancecheckout/pdf');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/postfinancecheckout/alert');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/postfinancecheckout/transaction');
	}

	private function removePermissions(){
		$this->load->model("user/user_group");
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/postfinancecheckout/event');
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/postfinancecheckout/completion');
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/postfinancecheckout/void');
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/postfinancecheckout/refund');
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/postfinancecheckout/update');
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/postfinancecheckout/pdf');
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/postfinancecheckout/alert');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/postfinancecheckout/transaction');
	}
}