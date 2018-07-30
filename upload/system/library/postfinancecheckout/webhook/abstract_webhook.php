<?php
namespace PostFinanceCheckout\Webhook;

/**
 * Abstract webhook processor.
 */
abstract class AbstractWebhook {
	private static $instances = array();
	protected $registry;
	
	private function __construct(\Registry $registry){
		$this->registry = $registry;
	}

	/**
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
	 * Processes the received webhook request.
	 *
	 * @param \PostFinanceCheckout\Webhook\Request $request
	 */
	abstract public function process(Request $request);
}