<?php
require_once modification(DIR_SYSTEM . 'library/postfinancecheckout/helper.php');
use PostFinanceCheckout\Model\AbstractModel;

class ModelExtensionPostFinanceCheckoutModification extends AbstractModel {

	public function install(){
		$path = DIR_SYSTEM . "library/postfinancecheckout/modification/";
		$installedModifications = $this->getModificationModel()->getModifications();
		foreach (PostFinanceCheckoutVersionHelper::getModifications() as $code => $modification) {
			$status = $modification['default_status'];
			foreach($installedModifications as $installedModification) {
				if($installedModification['code'] == $code) {
					$status = $installedModification['status'];
				}
			}
			$this->importModification($path . $modification['file'], $status);
		}
	}

	public function uninstall(){
		foreach (PostFinanceCheckoutVersionHelper::getModifications() as $code => $modification) {
			$modification_info = $this->getModificationModel()->getModificationByCode($code);
			
			if ($modification_info) {
				$this->getModificationModel()->deleteModification($modification_info['modification_id']);
			}
		}
	}

	private function importModification($file, $status){
		$currentVersion = '0.0.0';
		$xml = file_get_contents($file);
		
		if ($xml) {
			$dom = new DOMDocument('1.0', 'UTF-8');
			$dom->loadXml($xml);
			
			$name = $dom->getElementsByTagName('name')->item(0);
			
			if ($name) {
				$name = $name->nodeValue;
			}
			else {
				$name = '';
			}
			
			$code = $dom->getElementsByTagName('code')->item(0);
			
			if ($code) {
				$code = $code->nodeValue;
				
				// Check to see if the modification is already installed or not.
				$modification_info = $this->getModificationModel()->getModificationByCode($code);
				
				if ($modification_info) {
					$currentVersion = $modification_info['version'];
				}
			}
			else {
				throw new Exception("Could not extract code from modification xml.");
			}
			
			$author = $dom->getElementsByTagName('author')->item(0);
			
			if ($author) {
				$author = $author->nodeValue;
			}
			else {
				$author = '';
			}
			
			$version = $dom->getElementsByTagName('version')->item(0);
			
			if ($version) {
				$version = $version->nodeValue;
			}
			else {
				$version = '';
			}
			
			$link = $dom->getElementsByTagName('link')->item(0);
			
			if ($link) {
				$link = $link->nodeValue;
			}
			else {
				$link = '';
			}
			
			$modification_data = array(
				'name' => $name,
				'code' => $code,
				'author' => $author,
				'version' => $version,
				'link' => $link,
				'xml' => $xml,
				'status' => $status,
				'extension_install_id' => null 
			);
			
			switch (version_compare($currentVersion, $version)) {
				case -1:
					// older. delete and add
					if ($modification_info) {
						$this->getModificationModel()->deleteModification($modification_info['modification_id']);
					}
					$this->getModificationModel()->addModification($modification_data);
				case 1:
					// newer. ignore
				case 0:
					// same. ignore
				default:
					break;
			}
		}
	}
}