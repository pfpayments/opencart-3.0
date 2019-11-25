<?php
require_once modification(DIR_SYSTEM . 'library/postfinancecheckout/helper.php');

class ControllerExtensionPostFinanceCheckoutCron extends Controller {

	public function index(){
		$this->endRequestPrematurely();
		
		if (isset($this->request->get['security_token'])) {
			$security_token = $this->request->get['security_token'];
		}
		else {
			\PostFinanceCheckoutHelper::instance($this->registry)->log('Cron called without security token.', \PostFinanceCheckoutHelper::LOG_ERROR);
			die();
		}
		
		\PostFinanceCheckout\Entity\Cron::cleanUpCronDB($this->registry);
		
		try {
			\PostFinanceCheckoutHelper::instance($this->registry)->dbTransactionStart();
			$result = \PostFinanceCheckout\Entity\Cron::setProcessing($this->registry, $security_token);
			\PostFinanceCheckoutHelper::instance($this->registry)->dbTransactionCommit();
			if (!$result) {
				die();
			}
		}
		catch (Exception $e) {
			// 1062 is mysql duplicate constraint error. This is expected and doesn't need to be logged.
			if (strpos('1062', $e->getMessage()) === false && strpos('constraint_key', $e->getMessage()) === false) {
				\PostFinanceCheckoutHelper::instance($this->registry)->log('Updating cron failed: ' . $e->getMessage(), \PostFinanceCheckoutHelper::LOG_ERROR);
			}
			\PostFinanceCheckoutHelper::instance($this->registry)->dbTransactionRollback();
			die();
		}
		
		$errors = $this->runTasks();
		
		try {
			\PostFinanceCheckoutHelper::instance($this->registry)->dbTransactionStart();
			$result = \PostFinanceCheckout\Entity\Cron::setComplete($this->registry, $security_token, implode('. ', $errors));
			\PostFinanceCheckoutHelper::instance($this->registry)->dbTransactionCommit();
			if (!$result) {
				\PostFinanceCheckoutHelper::instance($this->registry)->log('Could not update finished cron job.', \PostFinanceCheckoutHelper::LOG_ERROR);
				die();
			}
		}
		catch (Exception $e) {
			\PostFinanceCheckoutHelper::instance($this->registry)->dbTransactionRollback();
			\PostFinanceCheckoutHelper::instance($this->registry)->log('Could not update finished cron job: ' . $e->getMessage(), \PostFinanceCheckoutHelper::LOG_ERROR);
			die();
		}
		die();
	}

	private function runTasks(){
		$errors = array();
		foreach (\PostFinanceCheckout\Entity\AbstractJob::loadNotSent($this->registry) as $job) {
			try {
				switch (get_class($job)) {
					case \PostFinanceCheckout\Entity\CompletionJob::class:
						$transaction_info = \PostFinanceCheckout\Entity\TransactionInfo::loadByTransaction($this->registry, $job->getSpaceId(),
								$job->getTransactionId());
						\PostFinanceCheckout\Service\Transaction::instance($this->registry)->updateLineItemsFromOrder($transaction_info->getOrderId());
						\PostFinanceCheckout\Service\Completion::instance($this->registry)->send($job);
						break;
					case \PostFinanceCheckout\Entity\RefundJob::class:
						\PostFinanceCheckout\Service\Refund::instance($this->registry)->send($job);
						break;
					case \PostFinanceCheckout\Entity\VoidJob::class:
						\PostFinanceCheckout\Service\VoidJob::instance($this->registry)->send($job);
						break;
					default:
						break;
				}
			}
			catch (Exception $e) {
				\PostFinanceCheckoutHelper::instance($this->registry)->log('Could not update job: ' . $e->getMessage(), \PostFinanceCheckoutHelper::LOG_ERROR);
				$errors[] = $e->getMessage();
			}
		}
		return $errors;
	}

	private function endRequestPrematurely(){
		if(ob_get_length()){
			ob_end_clean();
		}
		// Return request but keep executing
		set_time_limit(0);
		ignore_user_abort(true);
		ob_start();
		if (session_id()) {
			session_write_close();
		}
		header("Content-Encoding: none");
		header("Connection: close");
		header('Content-Type: text/javascript');
		ob_end_flush();
		flush();
		if (is_callable('fastcgi_finish_request')) {
			fastcgi_finish_request();
		}
	}
}