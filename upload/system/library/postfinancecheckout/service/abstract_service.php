<?php

namespace PostFinanceCheckout\Service;

/**
 */
abstract class AbstractService {
	private static $instances = array();
	protected $registry;

	protected function __construct(\Registry $registry){
		$this->registry = $registry;
	}

	/**
	 *
	 * @return static
	 */
	public static function instance(\Registry $registry){
		$class = get_called_class();
		if (!isset(self::$instances[$class])) {
			self::$instances[$class] = new $class($registry);
		}
		return self::$instances[$class];
	}

	/**
	 * Creates and returns a new entity filter.
	 *
	 * @param string $field_name
	 * @param mixed $value
	 * @param string $operator
	 * @return \PostFinanceCheckout\Sdk\Model\EntityQueryFilter
	 */
	protected function createEntityFilter($field_name, $value, $operator = \PostFinanceCheckout\Sdk\Model\CriteriaOperator::EQUALS){
		$filter = new \PostFinanceCheckout\Sdk\Model\EntityQueryFilter();
		$filter->setType(\PostFinanceCheckout\Sdk\Model\EntityQueryFilterType::LEAF);
		$filter->setOperator($operator);
		$filter->setFieldName($field_name);
		$filter->setValue($value);
		return $filter;
	}

	/**
	 * Creates and returns a new entity order by.
	 *
	 * @param string $field_name
	 * @param string $sort_order
	 * @return \PostFinanceCheckout\Sdk\Model\EntityQueryOrderBy
	 */
	protected function createEntityOrderBy($field_name, $sort_order = \PostFinanceCheckout\Sdk\Model\EntityQueryOrderByType::DESC){
		$order_by = new \PostFinanceCheckout\Sdk\Model\EntityQueryOrderBy();
		$order_by->setFieldName($field_name);
		$order_by->setSorting($sort_order);
		return $order_by;
	}

	/**
	 * Changes the given string to have no more characters as specified.
	 *
	 * @param string $string
	 * @param int $max_length
	 * @return string
	 */
	protected function fixLength($string, $max_length){
		return mb_substr($string, 0, $max_length, 'UTF-8');
	}

	/**
	 * Removes all non printable ASCII chars
	 *
	 * @param string $string
	 * @return $string
	 */
	protected function removeNonAscii($string){
		return preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $string);
	}
}