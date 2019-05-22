<?php
require_once modification(DIR_SYSTEM . 'library/postfinancecheckout/helper.php');
use PostFinanceCheckout\Model\AbstractModel;

/**
 * This class handles the creation of all files required to display payment methods dynamically.
 *
 * @author sebastian
 *
 */
class ModelExtensionPostFinanceCheckoutDynamic extends AbstractModel {

	public function install(){
		$this->load->model("user/user_group");
		$this->load->model("setting/setting");
		
		$this->load->model("setting/store");
		$stores = $this->model_setting_store->getStores();
		$stores[] = array(
			'store_id' => 0 
		); // Add default store
		foreach ($stores as $store) {
			$configurations = $this->loadConfigurations($store['store_id']);
			
			foreach ($configurations as $configuration) {
				$this->getExtensionModel()->install("payment", $configuration['code']);
				
				// Set permissions.
				$this->model_user_user_group->addPermission($this->user->getGroupId(), "access", "extension/payment/" . $configuration['code']);
				$this->model_user_user_group->addPermission($this->user->getGroupId(), "modify", "extension/payment/" . $configuration['code']);
				
				$settings = array();
				foreach ($configuration as $partialKey => $value) {
					$settings[PostFinanceCheckoutVersionHelper::extractPaymentSettingCode($configuration['code']) . '_' . $partialKey] = $value;
				}
				$this->model_setting_setting->editSetting(PostFinanceCheckoutVersionHelper::extractPaymentSettingCode($configuration['code']),
						$settings, $store['store_id']);
				
				$this->createModel($configuration);
				$this->createController($configuration);
				$this->createAdminController($configuration);
				$this->createLanguageFiles($configuration, 'admin');
				$this->createLanguageFiles($configuration, 'catalog');
			}
		}
	}

	/**
	 * Clears database data for given configurations - does not delete files (auto remove on next modification refresh)
	 */
	public function uninstall(){
		$this->load->model("user/user_group");
		$this->load->model("setting/setting");
		
		$this->load->model("setting/store");
		$stores = $this->model_setting_store->getStores();
		$stores[] = array(
			'store_id' => 0 
		); // Add default store
		
		$configurations = $this->loadConfigurations();
		
		foreach ($configurations as $configuration) {
			$code = $configuration['code'];
			
			$this->getExtensionModel()->uninstall("payment", $code);
			$this->model_user_user_group->removePermission($this->user->getGroupId(), "access", "extension/payment/" . $code);
			$this->model_user_user_group->removePermission($this->user->getGroupId(), "modify", "extension/payment/" . $code);
			
			foreach ($stores as $store) {
				$this->model_setting_setting->deleteSetting($code, $store['store_id']);
			}
		}
	}

	private function loadConfigurations($store_id = null){
		$configurations = array();
		if ($store_id !== null) {
			$space_id = \PostFinanceCheckoutHelper::instance($this->registry)->getSpaceId($store_id);
			$methods = \PostFinanceCheckout\Entity\MethodConfiguration::loadBySpaceId($this->registry, $space_id);
		}
		else {
			$methods = \PostFinanceCheckout\Entity\MethodConfiguration::loadAll($this->registry);
		}
		foreach ($methods as $method) {
			$configurations[] = array(
				'code' => 'postfinancecheckout_' . $method->getConfigurationId(),
				'title' => $method->getTitle(),
				'sort_order' => $method->getSortOrder(),
				'terms' => '',
				'status' => $method->getState() == \PostFinanceCheckout\Entity\MethodConfiguration::STATE_ACTIVE 
			);
		}
		return $configurations;
	}

	/**
	 *
	 * @param array $configuration
	 * @param string $type admin or catalog
	 * @throws Exception
	 */
	private function createLanguageFiles(array $configuration, $type){
		$source = DIR_SYSTEM . 'library/postfinancecheckout/dynamic/' . $type . '/language.mock';
		
		$target_base = DIR_MODIFICATION . $type . '/language/#code#/extension/payment/' . $configuration['code'] . '.php';
		
		$this->load->model('localisation/language');
		$languages = $this->model_localisation_language->getLanguages();
		foreach ($languages as $code => $language) {
			if (!$language['status'] || count($configuration['title']) === 0) {
				continue;
			}
			$restLang = \PostFinanceCheckout\Provider\Language::instance($this->registry)->findForStore($language['code'], $language['locale']);
			if ($restLang === false) {
				\PostFinanceCheckoutHelper::instance($this->registry)->log("Could not find language for code $code.");
				continue;
			}
			
			$title = array_values($configuration['title'])[0];
			if (isset($configuration['title'][$restLang->getIetfCode()])) {
				$title = $configuration['title'][$restLang->getIetfCode()];
			}
			$target = str_replace('#code#', PostFinanceCheckoutVersionHelper::extractLanguageDirectory($language), $target_base);
			
			$content = str_replace(array(
				'#Title#' 
			), array(
				$title
			), $this->loadSource($source));
			
			// create directories as required
			$this->createRequiredDirectories($target);
			
			if (file_put_contents($target, $content) === false) {
				throw new Exception("Could not write to $target file.");
			}
		}
	}

	private function createModel(array $configuration){
		$source = DIR_SYSTEM . 'library/postfinancecheckout/dynamic/catalog/model.mock';
		$target = DIR_MODIFICATION . 'catalog/model/extension/payment/' . $configuration['code'] . '.php';
		
		// replace dummy data
		$content = str_replace(array(
			'#PaymentMethod#',
			'#title#',
			'#code#',
			'#sort_order#',
			'#terms#' 
		),
				array(
					$this->getClassName('ModelExtensionPayment', $configuration['code']),
					base64_encode(serialize($configuration['title'])),
					$configuration['code'],
					$configuration['sort_order'],
					$configuration['terms'] 
				), $this->loadSource($source));
		
		// create directories as required
		$this->createRequiredDirectories($target);
		
		if (file_put_contents($target, $content) === false) {
			throw new Exception("Could not write to $target file.");
		}
	}

	private function createAdminController(array $configuration){
		$source = DIR_SYSTEM . 'library/postfinancecheckout/dynamic/admin/controller.mock';
		$target = DIR_MODIFICATION . 'admin/controller/extension/payment/' . $configuration['code'] . '.php';
		
		// replace dummy data
		$content = str_replace(array(
			'#classname#',
			'#code#' 
		), array(
			$this->getClassName('ControllerExtensionPayment', $configuration['code']),
			$configuration['code'] 
		), $this->loadSource($source));
		
		// create directories as required
		$this->createRequiredDirectories($target);
		
		if (file_put_contents($target, $content) === false) {
			throw new Exception("Could not write to $target file.");
		}
	}

	private function createController(array $configuration){
		$source = DIR_SYSTEM . 'library/postfinancecheckout/dynamic/catalog/controller.mock';
		$target = DIR_MODIFICATION . 'catalog/controller/extension/payment/' . $configuration['code'] . '.php';
		
		// replace dummy data
		$content = str_replace(array(
			'#classname#',
			'#code#' 
		), array(
			$this->getClassName('ControllerExtensionPayment', $configuration['code']),
			$configuration['code'] 
		), $this->loadSource($source));
		
		// create directories as required
		$this->createRequiredDirectories($target);
		
		if (file_put_contents($target, $content) === false) {
			throw new Exception("Could not write to $target file.");
		}
	}

	private function createRequiredDirectories($path){
		$path = substr($path, strlen(DIR_MODIFICATION));
		$required = explode(DIRECTORY_SEPARATOR, $path);
		array_pop($required); // remove filename
		$existing = rtrim(DIR_MODIFICATION, DIRECTORY_SEPARATOR);
		
		foreach ($required as $dir) {
			if (!is_dir($existing . DIRECTORY_SEPARATOR . $dir)) {
				if (!mkdir($existing . DIRECTORY_SEPARATOR . $dir)) {
					throw new Exception("Could not create folder '$existing/$dir'.");
				}
			}
			$existing .= DIRECTORY_SEPARATOR . $dir;
		}
	}

	private function loadSource($source){
		static $prototype = array();
		if (!isset($prototype[$source])) {
			$prototype[$source] = $check = file_get_contents($source);
			if (!$check) {
				throw new Exception("Could not load prototype file. Please check the following file exists, and has read permissions set: '$source'.");
			}
		}
		return $prototype[$source];
	}

	private function getClassName($base, $code){
		$class_name = $base;
		foreach (explode('_', $code) as $part) {
			$class_name .= ucfirst($part);
		}
		return $class_name;
	}
}
