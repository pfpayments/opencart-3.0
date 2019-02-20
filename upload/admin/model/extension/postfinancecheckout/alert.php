<?php
require_once modification(DIR_SYSTEM . 'library/postfinancecheckout/helper.php');
use PostFinanceCheckout\Model\AbstractModel;

/**
 * Handles the display of alerts in the top right.
 * Is used in combination with
 * - controller/extension/postfinancecheckout/alert.php
 * - system/library/postfinancecheckout/modification/PostFinanceCheckoutAlerts.ocmod.xml
 */
class ModelExtensionPostFinanceCheckoutAlert extends AbstractModel {
	private $alerts;

	public function getAlertsTitle(){
		$this->load->language('extension/payment/postfinancecheckout');
		return $this->language->get('title_notifications');
	}

	public function getAlerts(){
		if ($this->alerts == null) {
			try {
				$this->load->language('extension/payment/postfinancecheckout');
				$this->alerts = array();
				$alert_entities = \PostFinanceCheckout\Entity\Alert::loadAll($this->registry);
			
				foreach ($alert_entities as $alert_entity) {
					$this->alerts[] = array(
						'url' => $this->createUrl($alert_entity->getRoute(),
								array(
									\PostFinanceCheckoutVersionHelper::TOKEN => $this->session->data[\PostFinanceCheckoutVersionHelper::TOKEN] 
								)),
						'text' => $this->language->get($alert_entity->getKey()),
						'level' => $alert_entity->getLevel(),
						'count' => $alert_entity->getCount() 
					);
				}
			}
			catch(\Exception $e) {
				// We ignore errors here otherwise we might not be albe to display the admin backend UI.
			}
		}
		return $this->alerts;
	}

	public function getAlertCount(){
		$count = 0;
		foreach ($this->getAlerts() as $alert) {
			$count += $alert['count'];
		}
		return $count;
	}
}
