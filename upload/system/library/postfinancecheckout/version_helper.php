<?php
require_once (DIR_SYSTEM . 'library/postfinancecheckout/autoload.php');

/**
 * Versioning helper which offers implementations depending on opencart version. (Internal) Some version differences may be handled via rewriter.
 *
 * @author sebastian
 *
 */
class PostFinanceCheckoutVersionHelper {
	const TOKEN = 'user_token';

	public static function getModifications(){
		return array(
			'PostFinanceCheckoutCore' => array(
				'file' => 'PostFinanceCheckoutCore.ocmod.xml',
				'default_status' => 1 
			),
			'PostFinanceCheckoutAlerts' => array(
				'file' => 'PostFinanceCheckoutAlerts.ocmod.xml',
				'default_status' => 1 
			),
			'PostFinanceCheckoutAdministration' => array(
				'file' => 'PostFinanceCheckoutAdministration.ocmod.xml',
				'default_status' => 1 
			),
			'PostFinanceCheckoutQuickCheckoutCompatibility' => array(
				'file' => 'PostFinanceCheckoutQuickCheckoutCompatibility.ocmod.xml',
				'default_status' => 0 
			),
			'PostFinanceCheckoutXFeeProCompatibility' => array(
				'file' => 'PostFinanceCheckoutXFeeProCompatibility.ocmod.xml',
				'default_status' => 0
			),
			'PostFinanceCheckoutPreventConfirmationEmail' => array(
				'file' => 'PostFinanceCheckoutPreventConfirmationEmail.ocmod.xml',
				'default_status' => 0 
			),
			'PostFinanceCheckoutFrontendPdf' => array(
				'file' => 'PostFinanceCheckoutFrontendPdf.ocmod.xml',
				'default_status' => 1 
			) ,
			'PostFinanceCheckoutTransactionView' => array(
				'file' => 'PostFinanceCheckoutTransactionView.ocmod.xml',
				'default_status' => 1
			)
		);
	}

	public static function wrapJobLabels(\Registry $registry, $content){
		return $content;
	}

	public static function getPersistableSetting($value, $default){
		return $value;
	}

	public static function getTemplate($theme, $template){
		return $template;
	}

	public static function newTax(\Registry $registry){
		return new \Cart\Tax($registry);
	}

	public static function getSessionTotals(\Registry $registry){		// Totals
		$registry->get('load')->model('setting/extension');
		
		$totals = array();
		$taxes = $registry->get('cart')->getTaxes();
		$total = 0;
		
		// Because __call can not keep var references so we put them into an array.
		$total_data = array(
			'totals' => &$totals,
			'taxes' => &$taxes,
			'total' => &$total
		);
		
		$sort_order = array();
		$results = $registry->get('model_setting_extension')->getExtensions('total');
		foreach ($results as $key => $value) {
			$sort_order[$key] = $registry->get('config')->get('total_' . $value['code'] . '_sort_order');
		}
		
		array_multisort($sort_order, SORT_ASC, $results);
		
		foreach ($results as $result) {
			if ($registry->get('config')->get('total_' . $result['code'] . '_status')) {
				$registry->get('load')->model('extension/total/' . $result['code']);
				
				// We have to put the totals in an array so that they pass by reference.
				$registry->get('model_extension_total_' . $result['code'])->getTotal($total_data);
			}
		}
		
		$sort_order = array();
		
		foreach ($totals as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}
		
		array_multisort($sort_order, SORT_ASC, $totals);
		return $total_data['totals'];
	}
	
	public static function persistPluginStatus(\Registry $registry, array $post) {
		$status = array(
			'payment_postfinancecheckout_status' => $post['postfinancecheckout_status']
		);
		$registry->get('model_setting_setting')->editSetting('payment_postfinancecheckout', $status, $post['id']);
	}
	
	public static function extractPaymentSettingCode($code) {
		return 'payment_' . $code;
	}

	public static function extractLanguageDirectory($language){
		return $language['code'];
	}

	public static function createUrl(Url $url_provider, $route, $query, $ssl){
		if ($route === 'extension/payment') {
			$route = 'marketplace/extension';
			// all calls with extension/payment createUrl use array
			$query['type'] = 'payment';
		}
		if (is_array($query)) {
			$query = http_build_query($query);
		}
		else if (!is_string($query)) {
			throw new Exception("Query must be of type string or array, " . get_class($query) . " given.");
		}
		return $url_provider->link($route, $query, $ssl);
	}
}