<?php
require_once modification(DIR_SYSTEM . 'library/postfinancecheckout/helper.php');
use PostFinanceCheckout\Controller\AbstractController;

class ControllerExtensionPostFinanceCheckoutAddress extends AbstractController {
	
	private static $ADDRESS_FIELDS = array(
		'firstname',
		'lastname',
		'company',
		'address_id',
		'address_1',
		'address_2',
		'city',
		'postcode',
		'country_id',
		'zone_id',
		'custom_field'
	);
	
	private static $ADDRESS_TYPES = array(
		'payment',
		'shipping'
	);
	
	public function update(){
		foreach(self::$ADDRESS_TYPES as $type) {
			foreach(self::$ADDRESS_FIELDS as $field) {
				if(isset($this->request->get[$type . "_" . $field])) {
					$this->session->data[$type . "_" . $field] = $this->request->get[$type . "_" . $field];
				}
			}
		}
	}
}