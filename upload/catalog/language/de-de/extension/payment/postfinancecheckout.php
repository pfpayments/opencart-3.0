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
//@formatter:off
// Text
$_['button_confirm']				= 'Bestätige Zahlung';
$_['text_loading']	      		 	= '<i class=\'fa fa-spinner fa-spin\'></i> Bearbeitungsauftrag'; // is included as part of a html attribute ="", as such cannot contain double quotes
$_['text_further_details']			= 'Bitte geben Sie alle fehlenden Details ein, damit wir Ihre Zahlung unten verarbeiten können.';

// Error
$_['heading_error']					= 'Fehler';
$_['error_default']					= 'Diese Aktion ist nicht zulässig.';
$_['error_order_edit']				= 'Die PostFinance Checkout-Transaktion befindet sich in einem Zustand, in dem die Einzelposten nicht mehr geändert werden dürfen.';
$_['error_not_pending']				= 'Die Transaktion ist vorhanden und befindet sich nicht im Status "Ausstehend"..';
$_['error_confirmation']			= 'Die Transaktion konnte nicht bestätigt werden. Bitte überprüfen Sie, ob eine Zahlung von Ihrem Konto erfolgt ist, und versuchen Sie es erneut, wenn keine Belastung erfolgt ist.';

$_['rounding_adjustment_item_name']	= 'Rundungsanpassung';

// Order overview / download buttons
$_['button_invoice']				= 'Rechnung';
$_['button_packing_slip']			= 'Lieferschein';

// Webhook messages
$_['message_webhook_processing']	= 'Die Transaktion wird über einen Webhook verarbeitet.';
$_['message_webhook_confirm']		= 'Die Transaktion wurde per Webhook bestätigt.';
$_['message_webhook_authorize']		= 'Die Transaktion wurde über den Webhook autorisiert.';
$_['message_webhook_waiting']		= 'Transaktion wartet über Webhook.';
$_['message_webhook_decline']		= 'Die Transaktion wurde per Webhook abgelehnt.';
$_['message_webhook_failed']		= 'Transaktion über Webhook fehlgeschlagen.';
$_['message_webhook_fulfill']		= 'Die Transaktion wurde per Webhook ausgeführt.';
$_['message_webhook_voided']		= 'Die Transaktion wurde über den Webhook storniert.';

$_['message_webhook_manual']		= 'Eine manuelle Entscheidung darüber, ob die Zahlung akzeptiert wird, ist erforderlich.';
$_['message_refund_successful']		= 'Die Rückerstattung \'%s\' über Menge %s war erfolgreich.';
