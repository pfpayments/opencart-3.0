<?php
//@formatter:off
// Text
$_['button_confirm']				= 'Confirm Payment';
$_['text_loading']	      		 	= '<i class=\'fa fa-spinner fa-spin\'></i> Processing Order'; // is included as part of a html attribute ="", as such cannot contain double quotes
$_['text_further_details']			= 'Please enter any missing details so we can process your payment below.';

// Error
$_['heading_error']					= 'Error';
$_['error_default']					= 'This action is not permitted.';
$_['error_order_edit']				= 'The PostFinance Checkout transaction is in a state in which the line items may no longer be changed.';
$_['error_not_pending']				= 'Transaction exists and is not in state pending.';

// Order overview / download buttons
$_['button_invoice']				= 'Invoice';
$_['button_packing_slip']			= 'Packing slip';

// Webhook messages
$_['message_webhook_processing']	= 'Transaction is processing via webhook.';
$_['message_webhook_connfirm']		= 'Transaction has been confirmed via webhook.';
$_['message_webhook_authorize']		= 'Transaction has been authorized via webhook.';
$_['message_webhook_waiting']		= 'Transaction is waiting via webhook.';
$_['message_webhook_decline']		= 'Transaction has been declined via webhook.';
$_['message_webhook_failed']		= 'Transaction has failed via webhook.';
$_['message_webhook_fulfill']		= 'Transaction has been fulfilled via webhook.';
$_['message_webhook_voided']		= 'Transaction has been voided via webhook.';

$_['message_webhook_manual']		= 'A manual decision about whether to accept the payment is required.';
$_['message_refund_successful']		= 'The refund \'%s\' over amount %s was successful.';