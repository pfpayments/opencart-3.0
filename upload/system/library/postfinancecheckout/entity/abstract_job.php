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

namespace PostFinanceCheckout\Entity;

/**
 * This entity holds data about a transaction on the gateway.
 *
 * @method int getId()
 * @method int getJobId()
 * @method void setJobId(int $id)
 * @method string getState()
 * @method void setState(string $state)
 * @method int getSpaceId()
 * @method void setSpaceId(int $id)
 * @method int getTransactionId()
 * @method void setTransactionId(int $id)
 * @method int getOrderId()
 * @method void setOrderId(int $id)
 * @method void setFailureReason(map[string,string] $reasons)
 * @method map[string,string] getLabels()
 * @method void setLabels(map[string,string] $labels)
 *
 */
abstract class AbstractJob extends AbstractEntity {
	const STATE_CREATED = 'CREATED';
	const STATE_SENT = 'SENT';
	const STATE_SUCCESS = 'SUCCESS';
	const STATE_FAILED_CHECK = 'FAILED_CHECK';
	const STATE_FAILED_DONE = 'FAILED_DONE';

	protected static function getFieldDefinition(){
		return array(
			'job_id' => ResourceType::INTEGER,
			'state' => ResourceType::STRING,
			'space_id' => ResourceType::INTEGER,
			'transaction_id' => ResourceType::INTEGER,
			'order_id' => ResourceType::INTEGER,
			'labels' => ResourceType::OBJECT,
			'failure_reason' => ResourceType::OBJECT 
		);
	}

	/**
	 * Returns the translated failure reason.
	 *
	 * @param string $locale
	 * @return string
	 */
	public function getFailureReason($language = null){
		$value = $this->getValue('failure_reason');
		if (empty($value)) {
			return null;
		}
		
		return \PostFinanceCheckoutHelper::instance($this->registry)->translate($value, $language);
	}

	public static function loadByOrder(\Registry $registry, $order_id){
		$db = $registry->get('db');
		
		$table = DB_PREFIX . static::getTableName();
		$order_id = $db->escape($order_id);
		
		$query = "SELECT * FROM $table WHERE order_id='$order_id';";
		
		$db_result = self::query($query, $db);
		
		$result = array();
		if ($db_result->num_rows) {
			foreach ($db_result->rows as $row) {
				$result[] = new static($registry, $row);
			}
		}
		return $result;
	}

	public static function loadByJob(\Registry $registry, $space_id, $job_id){
		$db = $registry->get('db');
		
		$table = DB_PREFIX . static::getTableName();
		$job_id = $db->escape($job_id);
		$space_id = $db->escape($space_id);
		
		$query = "SELECT * FROM $table WHERE job_id='$job_id' AND space_id='$space_id';";
		
		$db_result = self::query($query, $db);
		
		if (isset($db_result->row) && !empty($db_result->row)) {
			return new static($registry, $db_result->row);
		}
		return new static($registry);
	}

	public static function countRunningForOrder(\Registry $registry, $order_id){
		$db = $registry->get('db');
		
		$table = DB_PREFIX . static::getTableName();
		$order_id = $db->escape($order_id);
		$success = self::STATE_SUCCESS;
		$failed_1 = self::STATE_FAILED_CHECK;
		$failed_2 = self::STATE_FAILED_DONE;
		
		$query = "SELECT COUNT(id) FROM $table WHERE order_id='$order_id' AND state NOT IN ('$success', '$failed_1', '$failed_2');";
		
		$db_result = $db->query($query);
		
		return $db_result->row['COUNT(id)'];
	}

	/**
	 * Load not sent jobs.
	 * If called on abstract object will load all completions, refunds and voids.
	 *
	 * @param \Registry $registry
	 * @param string $period
	 * @return array|\PostFinanceCheckout\Entity\AbstractJob[]
	 */
	public static function loadNotSent(\Registry $registry, $period = 'PT10M'){
		if (get_called_class() == get_class()) {
			return array_merge(CompletionJob::loadNotSent($registry, $period), VoidJob::loadNotSent($registry, $period),
					RefundJob::loadNotSent($registry, $period));
		}
		else {
			$time = new \DateTime();
			$time->sub(new \DateInterval($period));
			$table = DB_PREFIX . static::getTableName();
			$created = self::STATE_CREATED;
			$timestamp = $time->format('Y-m-dd h:i:s');
			
			$query = "SELECT * FROM $table WHERE STATE='$created' AND updated_at<'$timestamp';";
			$db_result = self::query($query, $registry->get('db'));
			$result = array();
			if ($db_result->num_rows) {
				foreach ($result->rows as $row) {
					$result[] = new static($registry, $row);
				}
			}
			return $result;
		}
	}

	/**
	 * Checks if there is a not sent job.
	 * Always checks all job types, not specific.
	 *
	 * @param \Registry $registry
	 * @param string $period
	 * @return array|\PostFinanceCheckout\Entity\AbstractJob[]
	 */
	public static function hasNotSent(\Registry $registry, $period = 'PT10M'){
		$time = new \DateTime();
		$time->sub(new \DateInterval($period));
		$timestamp = $time->format('Y-m-dd h:i:s');
		$completions = DB_PREFIX . CompletionJob::getTableName();
		$voids = DB_PREFIX . VoidJob::getTableName();
		$refunds = DB_PREFIX . RefundJob::getTableName();
		$created = self::STATE_CREATED;
		
		//@formatter:off
		$query = "SELECT ( " .
				"EXISTS ( SELECT id FROM $completions WHERE STATE='$created' AND updated_at<'$timestamp' LIMIT 1) " .
				"OR EXISTS ( SELECT id FROM $voids WHERE STATE='$created' AND updated_at<'$timestamp' LIMIT 1) " .
				"OR EXISTS ( SELECT id FROM $refunds WHERE STATE='$created' AND updated_at<'$timestamp' LIMIT 1) " .
				") as pending_job";
		//@formatter:on
		
		$db_result = self::query($query, $registry->get('db'));
		
		if ($result->row) {
			return true;
		}
		return false;
	}

	public static function loadNotSentForOrder(\Registry $registry, $order_id){
		$db = $registry->get('db');
		
		$table = DB_PREFIX . static::getTableName();
		$order_id = $db->escape($order_id);
		$created = self::STATE_CREATED;
		
		$query = "SELECT * FROM $table WHERE order_id='$order_id' AND state='$created';";
		
		$result = self::query($query, $db);
		
		if (isset($result->row) && !empty($result->row)) {
			return new static($registry, $result->row);
		}
		return new static($registry);
	}

	public static function loadRunningForOrder(\Registry $registry, $order_id){
		$db = $registry->get('db');
		
		$table = DB_PREFIX . static::getTableName();
		$order_id = $db->escape($order_id);
		$created = self::STATE_CREATED;
		$success = self::STATE_SUCCESS;
		$failed_1 = self::STATE_FAILED_CHECK;
		$failed_2 = self::STATE_FAILED_DONE;
		
		$query = "SELECT * FROM $table WHERE order_id='$order_id' AND state NOT IN ('$created', '$success', '$failed_1', '$failed_2');";
		
		$result = self::query($query, $db);
		
		if (isset($result->row) && !empty($result->row)) {
			return new static($registry, $result->row);
		}
		return new static($registry);
	}

	public static function loadOldestCheckable(\Registry $registry){
		$db = $registry->get('db');
		
		$table = DB_PREFIX . static::getTableName();
		$state = self::STATE_FAILED_CHECK;
		
		$query = "SELECT * FROM $table WHERE state='$state' ORDER BY updated_at ASC LIMIT 1;";
		
		$result = self::query($query, $db);
		
		if (isset($db_result->row) && !empty($db_result->row)) {
			return new static($registry, $db_result->row);
		}
		return new static($registry);
	}

	public static function countForOrder(\Registry $registry, $order_id){
		$db = $registry->get('db');
		
		$table = DB_PREFIX . static::getTableName();
		$order_id = $db->escape($order_id);
		
		$query = "SELECT COUNT(id) FROM $table WHERE order_id='$order_id';";
		
		$db_result = self::query($query, $db);
		
		return $db_result->row['COUNT(id)'];
	}

	public static function loadFailedCheckedForOrder(\Registry $registry, $order_id){
		$db = $registry->get('db');
		
		$table = DB_PREFIX . static::getTableName();
		$order_id = $db->escape($order_id);
		$state = self::STATE_FAILED_CHECK;
		
		$query = "SELECT * FROM $table WHERE order_id='$order_id' AND state='$state';";
		
		$db_result = self::query($query, $db);
		
		$result = array();
		if ($db_result->num_rows) {
			foreach ($db_result->rows as $row) {
				$result[] = new static($registry, $row);
			}
		}
		return $result;
	}

	/**
	 * Marks all failed_check jobs as failed_done.
	 *
	 * @param \Registry $registry
	 */
	public static function markFailedAsDone(\Registry $registry, $order_id){
		$db = $registry->get('db');
		
		$table = DB_PREFIX . static::getTableName();
		$order_id = $db->escape($order_id);
		$state = self::STATE_FAILED_CHECK;
		
		$query = "SELECT * FROM $table WHERE order_id='$order_id' AND state='$state';";
		
		$db_result = self::query($query, $db);
		
		if ($db_result->num_rows) {
			foreach ($db_result->rows as $row) {
				$job = new static($registry, $row);
				$job->setState(static::STATE_FAILED_DONE);
				$job->save();
			}
			\PostFinanceCheckout\Entity\Alert::loadFailedJobs($registry)->modifyCount(-($db_result->num_rows));
		}
	}
}