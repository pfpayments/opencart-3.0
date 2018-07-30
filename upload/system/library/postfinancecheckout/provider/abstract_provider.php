<?php

namespace PostFinanceCheckout\Provider;

/**
 * Abstract implementation of a provider.
 */
abstract class AbstractProvider {
	private static $instances;
	protected $registry;
	private $cache_key;
	private $data;

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
	 * Constructor.
	 *
	 * @param Cache cache
	 * @param string $cache_key
	 */
	protected function __construct(\Registry $registry, $cache_key){
		$this->registry = $registry;
		$this->cache_key = $cache_key;
	}

	/**
	 * Fetch the data from the remote server.
	 *
	 * @return array
	 */
	abstract protected function fetchData();

	/**
	 * Returns the id of the given entry.
	 *
	 * @param mixed $entry
	 * @return string
	 */
	abstract protected function getId($entry);

	/**
	 * Returns a single entry by id.
	 *
	 * @param string $id
	 * @return mixed
	 */
	public function find($id){
		if ($this->data == null) {
			$this->loadData();
		}
		
		if (isset($this->data[$id])) {
			return $this->data[$id];
		}
		else {
			return false;
		}
	}

	/**
	 * Returns all entries.
	 *
	 * @return array
	 */
	public function getAll(){
		if ($this->data == null) {
			$this->loadData();
		}
		
		return $this->data;
	}
	
	public function clearCache() {
		$this->registry->get('cache')->delete($this->cache_key);
	}

	private function loadData(){
		$cached_data = $this->registry->get('cache')->get($this->cache_key);
		if ($cached_data !== false) {
			$this->data = unserialize($cached_data);
		}
		else {
			$this->data = array();
			foreach ($this->fetchData() as $entry) {
				$this->data[$this->getId($entry)] = $entry;
			}
			
			$this->registry->get('cache')->set($this->cache_key, serialize($this->data));
		}
	}
}