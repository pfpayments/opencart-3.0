<?php
require_once DIR_SYSTEM . '/library/postfinancecheckout/helper.php';
use PostFinanceCheckout\Controller\AbstractController;

class ControllerExtensionPaymentPostFinanceCheckout extends AbstractController {
	
	// Initialize var(s)
	protected $error = array();
	
	// Holds multistore configs
	protected $data = array();
	
	/**
	 * This method is executed by OpenCart when the Payment module is installed from the admin.
	 * It will create the
	 * required tables.
	 *
	 * @return void
	 */
	public function install(){
		$this->load->model("extension/postfinancecheckout/setup");
		$this->model_extension_postfinancecheckout_setup->install();
	}
	
	/**
	 *
	 * @param bool $purge Set to false to skip purgin database.
	 * @return void
	 */
	public function uninstall($purge = true){
		$this->load->model("extension/postfinancecheckout/setup");
		$this->model_extension_postfinancecheckout_setup->uninstall($purge);
	}
	
	/**
	 * Render the payment method's settings page.
	 */
	public function index(){
		// Load essential models
		$this->load->language("extension/payment/postfinancecheckout");
		$this->load->model("setting/setting");
		$this->load->model("setting/store");
		$this->load->model("localisation/order_status");
		$this->load->model("extension/postfinancecheckout/setup");
		
		\PostFinanceCheckoutHelper::instance($this->registry);
		
		$this->document->setTitle($this->language->get("heading_title"));
		
		$shops = $this->getMultiStores();
		$this->processPostData($shops);
		
		$storeConfigs = $this->retrieveMultiStoreConfigs($shops);
		
		$pageVariables = $this->getSettingPageVariables($shops);
		
		$this->response->setOutput($this->loadView("extension/payment/postfinancecheckout", array_merge($storeConfigs, $pageVariables)));
	}
	
	/**
	 * Synchronizes webhooks and payment methods for the given space id.
	 *
	 * @param int $space_id
	 */
	private function synchronize($space_id){
		try {
			$this->load->model("extension/postfinancecheckout/setup");
			$this->model_extension_postfinancecheckout_setup->synchronize($space_id);
		}
		catch (Exception $e) {
			$this->error['warning'] = $e->getMessage();
		}
	}
	
	private function validateGlobalSettings(array $global){
		if (!isset($global['postfinancecheckout_application_key']) || empty($global['postfinancecheckout_application_key'])) {
			throw new Exception($this->language->get('error_application_key_unset'));
		}
		if (isset($global['postfinancecheckout_user_id']) && !empty($global['postfinancecheckout_user_id'])) {
			if (!ctype_digit($global['postfinancecheckout_user_id'])) {
				throw new Exception($this->language->get('error_user_id_numeric'));
			}
		}
		else {
			throw new Exception($this->language->get('error_user_id_unset'));
		}
	}
	
	private function validateStoreSettings(array $store){
		if (isset($store['postfinancecheckout_space_id']) && !empty($store['postfinancecheckout_space_id'])) {
			if (!ctype_digit($store['postfinancecheckout_space_id'])) {
				throw new Exception($this->language->get('error_space_id_numeric'));
			}
		}
		else {
			throw new Exception($this->language->get('error_space_id_unset'));
		}
		if (isset($store['postfinancecheckout_space_view_id']) && !empty($store['postfinancecheckout_space_view_id'])) {
			if (!ctype_digit($store['postfinancecheckout_space_view_id'])) {
				throw new Exception($this->language->get('error_space_view_id_numeric'));
			}
		}
	}
	
	private function persistStoreSettings(array $global, array $store){
		$newSettings = array_merge($global, $store);
		
		// preserve migration state
		if($this->config->has('postfinancecheckout_migration_version')) {
			$newSettings['postfinancecheckout_migration_version'] = $this->config->get('postfinancecheckout_migration_version');
			$newSettings['postfinancecheckout_migration_name'] = $this->config->get('postfinancecheckout_migration_name');
		}
		
		// preserve manual tasks
		$newSettings[\PostFinanceCheckout\Service\ManualTask::CONFIG_KEY] = PostFinanceCheckoutVersionHelper::getPersistableSetting(
				$this->model_setting_setting->getSetting(\PostFinanceCheckout\Service\ManualTask::CONFIG_KEY, $store['id']), 0);
		// preserve notification url
		$newSettings['postfinancecheckout_notification_url'] = PostFinanceCheckoutVersionHelper::getPersistableSetting(
				$this->model_setting_setting->getSetting('postfinancecheckout_notification_url', $store['id']), null);
		
		// set directly accessible settings required for synchronization, reload according to new settings
		if ($store['postfinancecheckout_status']) {
			$this->config->set('postfinancecheckout_application_key', $global['postfinancecheckout_application_key']);
			$this->config->set('postfinancecheckout_user_id', $global['postfinancecheckout_user_id']);
			$this->synchronize($store['postfinancecheckout_space_id']);
		}
		
		$newSettings['postfinancecheckout_download_invoice'] = isset($store['postfinancecheckout_download_invoice']);
		$newSettings['postfinancecheckout_download_packaging'] = isset($store['postfinancecheckout_download_packaging']);
		
		$newSettings['postfinancecheckout_rounding_adjustment'] = isset($store['postfinancecheckout_rounding_adjustment']);
		
		PostFinanceCheckoutVersionHelper::persistPluginStatus($this->registry, $newSettings);
		
		$this->model_setting_setting->editSetting('postfinancecheckout', $newSettings, $store['id']);
		
		return true;
	}
	
	/**
	 * Processes post data to settings.
	 *
	 * @param array $shops
	 */
	private function processPostData($shops){
		if ($this->request->server['REQUEST_METHOD'] !== "POST") {
			return;
		}
		
		try {
			$this->validateGlobalSettings($this->request->post);
		}
		catch (Exception $e) {
			$this->error['warning'] = $e->getMessage();
			return;
		}
		
		$this->model_extension_postfinancecheckout_setup->uninstall(false);
		
		foreach ($shops as $store) {
			$storeSettings = $this->request->post['stores'][$store['id']];
			$storeSettings['id'] = $store['id'];
			if ($this->validateStore($store['id'])) {
				if (isset($storeSettings['postfinancecheckout_status']) && $storeSettings['postfinancecheckout_status']) {
					try {
						$this->validateStoreSettings($storeSettings);
					}
					catch (Exception $e) {
						$this->error['warning'] = $e->getMessage();
						continue;
					}
				}
				$this->persistStoreSettings($this->request->post, $storeSettings);
			}
		}
		
		$this->install();
		
		if (!isset($this->error['warning'])) {
			$this->session->data['success'] = $this->language->get("message_saved_settings");
			
			$this->response->redirect(
					$this->createUrl("marketplace/extension",
							array(
								\PostFinanceCheckoutVersionHelper::TOKEN => $this->session->data[\PostFinanceCheckoutVersionHelper::TOKEN]
							)));
		}
	}
	
	/**
	 * Returns all variables used in the settigns page template.
	 *
	 * @param array $shops
	 * @return array
	 */
	private function getSettingPageVariables($shops){
		$data = array();
		
		$data['shops'] = $shops;
		
		// Form action url
		$data['action'] = $this->createUrl("extension/payment/postfinancecheckout",
				array(
					\PostFinanceCheckoutVersionHelper::TOKEN => $this->session->data[\PostFinanceCheckoutVersionHelper::TOKEN]
				));
		$data['cancel'] = $this->createUrl("marketplace/extension",
				array(
					\PostFinanceCheckoutVersionHelper::TOKEN => $this->session->data[\PostFinanceCheckoutVersionHelper::TOKEN]
				));
		
		return array_merge($this->getSettingsPageTranslatedVariables(), $data, $this->getAlertTemplateVariables(), $this->getSettingsPageBreadcrumbs(),
				$this->getSettingPageStoreVariables($shops), $this->getAdminSurroundingTemplates());
	}
	
	private function getSettingsPageTranslatedVariables(){
		$data = array();
		// Set data for template
		$data['heading_title'] = $this->language->get("heading_title");
		$data['title_payment_status'] = $this->language->get("title_payment_status");
		$data['title_modifications'] = $this->language->get("title_modifications");
		$data['footer_text'] = $this->language->get("footer_text");
		
		$data['title_global_settings'] = $this->language->get("title_global_settings");
		$data['title_store_settings'] = $this->language->get("title_store_settings");
		$data['title_space_view_id'] = $this->language->get("title_space_view_id");
		$data['entry_user_id'] = $this->language->get("entry_user_id");
		$data['help_user_id'] = $this->language->get("help_user_id");
		$data['entry_application_key'] = $this->language->get("entry_application_key");
		$data['help_application_key'] = $this->language->get("help_application_key");
		
		$data['entry_space_id'] = $this->language->get("entry_space_id");
		$data['help_space_id'] = $this->language->get("help_space_id");
		$data['entry_space_view_id'] = $this->language->get("entry_space_view_id");
		$data['help_space_view_id'] = $this->language->get("help_space_view_id");
		
		$orderStatuses = $this->model_localisation_order_status->getOrderStatuses();
		array_unshift($orderStatuses, array('order_status_id' => 0, 'name' => $this->language->get('text_none')));
		$data['order_statuses'] = $orderStatuses;
		$data['description_none_status'] = $this->language->get('description_none_status');
		
		$data['postfinancecheckout_statuses'] = $this->getOrderStatusTemplateVariables();
		
		$data['title_debug'] = $this->language->get('title_debug');
		$data['entry_log_level'] = $this->language->get('entry_log_level');
		$data['help_log_level'] = $this->language->get('help_log_level');
		$data['log_levels'] = $this->getLogLevels();
		
		$data['title_rounding_adjustment'] = $this->language->get('title_rounding_adjustment');
		$data['entry_rounding_adjustment'] = $this->language->get('entry_rounding_adjustment');
		$data['description_rounding_adjustment'] = $this->language->get('description_rounding_adjustment');
		
		$data['entry_email'] = $this->language->get("entry_email");
		$data['description_email'] = $this->language->get("description_email");
		$data['entry_alerts'] = $this->language->get("entry_alerts");
		$data['description_alerts'] = $this->language->get("description_alerts");
		$data['entry_core'] = $this->language->get("entry_core");
		$data['description_core'] = $this->language->get("description_core");
		$data['entry_administration'] = $this->language->get("entry_administration");
		$data['description_administration'] = $this->language->get("description_administration");
		$data['entry_pdf'] = $this->language->get("entry_pdf");
		$data['description_pdf'] = $this->language->get("description_pdf");
		$data['entry_checkout'] = $this->language->get("entry_checkout");
		$data['description_checkout'] = $this->language->get("description_checkout");
				$data['description_events'] = $this->language->get("description_events");
		
		$data['title_downloads'] = $this->language->get("title_downloads");
		$data['entry_download_invoice'] = $this->language->get("entry_download_invoice");
		$data['entry_download_packaging'] = $this->language->get("entry_download_packaging");
		$data['description_download_invoice'] = $this->language->get("description_download_invoice");
		$data['description_download_packaging'] = $this->language->get("description_download_packaging");
		
		$data['title_migration'] = $this->language->get('title_migration');
		$data['entry_migration_name'] = $this->language->get('entry_migration_name');
		$data['entry_migration_version'] = $this->language->get('entry_migration_version');
		
		$data['title_version'] = $this->language->get('title_version');
		$data['entry_version'] = $this->language->get('entry_version');
		$data['entry_date'] = $this->language->get('entry_date');
		
		$data['text_edit'] = $this->language->get("text_edit");
		$data['text_information'] = $this->language->get('text_information');
		
		$data['button_save'] = $this->language->get("button_save");
		$data['button_cancel'] = $this->language->get("button_cancel");
		
		$data['text_enabled'] = $this->language->get("text_enabled");
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['entry_status'] = $this->language->get('entry_status');
		
		$data['tab_general'] = $this->language->get("tab_general");
		
		return $data;
	}
	
	private function getLogLevels(){
		return array(
			\PostFinanceCheckoutHelper::LOG_ERROR => $this->language->get('log_level_error'),
			\PostFinanceCheckoutHelper::LOG_DEBUG => $this->language->get('log_level_debug')
		);
	}
	
	private function getOrderStatusTemplateVariables(){
		$data = array();
		$statuses = array(
			'processing_status',
			'authorized_status',
			'completed_status',
			'fulfill_status',
			'failed_status',
			'voided_status',
			'decline_status',
			'refund_status'
		);
		
		foreach ($statuses as $status) {
			$data[] = array(
				'entry' => $this->language->get('entry_' . $status),
				'description' => $this->language->get('description_' . $status),
				'key' => 'postfinancecheckout_' . $status . '_id'
			);
		}
		
		return $data;
	}
	
	private function getAlertTemplateVariables(){
		$data = array();
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
		}
		else {
			$data['success'] = false;
		}
		
		// If there are errors, show the error.
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		}
		else {
			$data['error_warning'] = '';
		}
		
		return $data;
	}
	
	private function getSettingsPageBreadcrumbs(){
		return array(
			'breadcrumbs' => array(
				array(
					"href" => $this->createUrl("common/home",
							array(
								\PostFinanceCheckoutVersionHelper::TOKEN => $this->session->data[\PostFinanceCheckoutVersionHelper::TOKEN]
							)),
					"text" => $this->language->get("text_home"),
					"separator" => false
				),
				array(
					"href" => $this->createUrl("marketplace/extension",
							array(
								\PostFinanceCheckoutVersionHelper::TOKEN => $this->session->data[\PostFinanceCheckoutVersionHelper::TOKEN]
							)),
					"text" => $this->language->get("text_payment"),
					"separator" => ' :: '
				),
				array(
					"href" => $this->createUrl("extension/payment/postfinancecheckout",
							array(
								\PostFinanceCheckoutVersionHelper::TOKEN => $this->session->data[\PostFinanceCheckoutVersionHelper::TOKEN]
							)),
					"text" => $this->language->get("heading_title"),
					"separator" => " :: "
				)
			)
		);
	}
	
	private function getSettingPageStoreVariables($shops){
		$this->load->model('setting/setting');
		$data = array();
		
		// 	Load defaults.
		$defaults = $this->getSettingsDefaults();
		
		foreach ($defaults['global'] as $setting_name => $default_value) {
			// Attempt to read from post
			if (isset($this->request->post[$setting_name])) {
				$data[$setting_name] = $this->request->post[$setting_name];
			}
			
			// Otherwise, attempt to get the setting from the database
			else if ($this->config->has($setting_name)) {
				$data[$setting_name] = $this->config->get($setting_name);
			}
			else {
				$data[$setting_name] = $default_value;
			}
		}
		
		foreach ($shops as $store) {
			$savedSettings = $this->model_setting_setting->getSetting('postfinancecheckout', $store['id']);
			foreach ($defaults['multistore'] as $setting_name => $default_value) {
				// Attempt to read from post
				if (isset($this->request->post['stores'][$store['id']][$setting_name])) {
					$setting = $this->request->post['stores'][$store['id']][$setting_name];
				}
				// then database
				else if (isset($savedSettings[$setting_name])) {
					$setting = $savedSettings[$setting_name];
				}
				// then default
				else {
					$setting = $default_value;
				}
				$data['stores'][$store['id']][$setting_name] = $setting;
			}
		}
		
		return $data;
	}
	
	/**
	 * Returns all settings, and their respective default values.
	 * Global settings are returned in 'global', multistore settings returned in 'multistore'
	 *
	 * @return string[][]
	 */
	private function getSettingsDefaults(){
		$multiStoreSettings = array(
			"postfinancecheckout_status" => 1,
			
			"postfinancecheckout_space_id" => null,
			"postfinancecheckout_space_view_id" => null,
			
			"postfinancecheckout_processing_status_id" => 0,
			"postfinancecheckout_failed_status_id" => 0,
			"postfinancecheckout_voided_status_id" => 16,
			"postfinancecheckout_decline_status_id" => 8,
			"postfinancecheckout_fulfill_status_id" => 5,
			"postfinancecheckout_authorized_status_id" => 15,
			"postfinancecheckout_refund_status_id" => 11,
			"postfinancecheckout_completed_status_id" => 2,
			
			"postfinancecheckout_log_level" => \PostFinanceCheckoutHelper::LOG_ERROR,
			
			"postfinancecheckout_notification_url" => null,
			
			"postfinancecheckout_rounding_adjustment" => 0,
			
			"postfinancecheckout_download_packaging" => 1,
			"postfinancecheckout_download_invoice" => 1,
			\PostFinanceCheckout\Service\ManualTask::CONFIG_KEY => 0
		);
		
		$globalSettings = array(
			"postfinancecheckout_application_key" => null,
			"postfinancecheckout_user_id" => null,
			"postfinancecheckout_migration_name" => 'uninitialized',
			"postfinancecheckout_migration_version" => "0.0.0"
		);
		
		return array(
			'multistore' => $multiStoreSettings,
			'global' => $globalSettings
		);
	}
	
	/**
	 * Check the post and check if the user has permission to edit the module settings
	 *
	 * @param int $store The store id
	 * @return bool
	 */
	private function validateStore($store){
		if (!$this->user->hasPermission("modify", "extension/payment/postfinancecheckout")) {
			$this->error['warning'] = $this->language->get("error_permission");
		}
		
		return (count($this->error) == 0);
	}
	
	/**
	 * Retrieve additional store id's from store table.
	 * Will not include default store. Only the additional stores. So we inject the default store here.
	 *
	 * @return array
	 */
	protected function getMultiStores(){
		$sql = $this->db->query(sprintf("SELECT store_id as id, name FROM %sstore", DB_PREFIX));
		$rows = $sql->rows;
		$default = array(
			array(
				'id' => 0,
				'name' => $this->config->get('config_name')
			)
		);
		$allStores = array_merge($default, $rows);
		
		return $allStores;
	}
	
	protected function retrieveMultiStoreConfigs($shops){
		$data = array();
		foreach ($shops as $store) {
			$sql = $this->db->query(sprintf("SELECT * FROM %ssetting WHERE store_id = %s", DB_PREFIX, $store['id']));
			$rows = $sql->rows;
			$newArrray = array();
			foreach ($rows as $setting) {
				$newArrray[$setting['key']] = $setting['value'];
			}
			$data['stores'][$store['id']] = $newArrray;
		}
		return $data;
	}
	
	protected function getRequiredPermission(){
		return 'extension/payment/postfinancecheckout';
	}
}