<?php
require_once modification(DIR_SYSTEM . 'library/postfinancecheckout/helper.php');

/**
 * Handles the display of alerts in the top right.
 * Is used in combination with
 * - model/extension/postfinancecheckout/alert.php
 * - system/library/postfinancecheckout/modification/PostFinanceCheckoutAlerts.ocmod.xml
 */
class ControllerExtensionPostFinanceCheckoutAlert extends PostFinanceCheckout\Controller\AbstractEvent {

	/**
	 * Redirects the user to the manual task overview in the postfinancecheckout backend.
	 */
	public function manual(){
		try {
			$this->validate();
			$this->response->redirect(\PostFinanceCheckoutHelper::getBaseUrl() . '/s/' . $this->config->get('postfinancecheckout_space_id') . '/manual-task/list');
		}
		catch (Exception $e) {
			$this->displayError($e->getMessage());
		}
	}

	/**
	 * Redirect the user to the order with the oldest checkable failed job.
	 */
	public function failed(){
		try {
			$oldest_failed = \PostFinanceCheckout\Entity\RefundJob::loadOldestCheckable($this->registry);
			if (!$oldest_failed->getId()) {
				$oldest_failed = \PostFinanceCheckout\Entity\CompletionJob::loadOldestCheckable($this->registry);
			}
			if (!$oldest_failed->getId()) {
				$oldest_failed = \PostFinanceCheckout\Entity\VoidJob::loadOldestCheckable($this->registry);
			}
			$this->response->redirect(
					$this->createUrl('sale/order/info',
							array(
								\PostFinanceCheckoutVersionHelper::TOKEN => $this->session->data[\PostFinanceCheckoutVersionHelper::TOKEN],
								'order_id' => $oldest_failed->getOrderId() 
							)));
		}
		catch (Exception $e) {
			$this->displayError($e->getMessage());
		}
	}

	protected function getRequiredPermission(){
		return 'extension/postfinancecheckout/alert';
	}
}