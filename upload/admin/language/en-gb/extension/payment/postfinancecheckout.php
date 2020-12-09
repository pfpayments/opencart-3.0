<?php
/**
 * ===========================================================================================
 * Module configuration
 * ===========================================================================================
 */
// @formatter:off
$_['heading_title']									= 'PostFinance Checkout';
$_['heading_postfinancecheckout']					= 'PostFinance Checkout';
$_['text_postfinancecheckout']					= '<a target="_BLANK" href="https://www.postfinance.ch/checkout"><img src="view/image/extension/payment/postfinancecheckout.png" alt="PostFinance Checkout Website" title="PostFinance Checkout Website" style="border: 1px solid #EEEEEE;" /></a>';
// PostFinanceCheckout Configurations (user id, application key, spaces)
$_['title_global_settings']							= "Global credentials";
$_['title_store_settings']							= "Store settings";
$_['entry_user_id']									= "User Id";
$_['help_user_id']									= "You can find your User ID in your PostFinance Checkout account. This setting is shared across all stores";
$_['entry_application_key']							= "Application key";
$_['help_application_key']							= "Your application key is displayed once when setting up PostFinance Checkout. This setting is shared across all stores.";
$_['entry_space_id']								= "Space Id";
$_['help_space_id']									= "You can find your Space Id in your PostFinance Checkout account.";

$_['title_space_view_id']							= "Space View Options";
$_['entry_space_view_id']							= "Space View Id";;
$_['help_space_view_id']							= "The space view ID allows to control the styling of the payment form and the payment page within the space. In multi-store setups it allows to adapt the payment form to different styling per sub store without requiring a dedicated space.";

// downloads
$_['title_downloads']								= "Documents";
$_['entry_download_invoice']						= "Invoice download";
$_['entry_download_packaging']						= "Packaging download";
$_['description_download_invoice']					= "Allow customers to download their invoice";
$_['description_download_packaging']				= "Allow customers to download their packaging slip";

// Debug
$_['title_debug']									= 'Debug';
$_['entry_log_level']								= 'Log level';
$_['help_log_level']								= 'Here you can set what sort of information should be logged, errors are always logged.';
$_['log_level_error']								= 'Error';
$_['log_level_debug']								= 'Debug';

// Rounding
$_['title_rounding_adjustment']						= 'Rounding adjustments';
$_['entry_rounding_adjustment']						= 'Send Item';
$_['description_rounding_adjustment']				= 'If we cannot map the Opencart totals to PostFinance Checkout line items exactly, instead of disabling the payment methods you can instead choose to send an Adjustment Item which contains the difference. In this case tax amounts may no longer be exact, and features such as reconciliation in PostFinance Checkout and refunds from the Opencart backend may not fully work.';

// Status
$_['title_payment_status']							= "Status mapping";
$_['entry_processing_status'] 						= "Processing status";
$_['description_processing_status'] 				= "Status the order enters when the transaction is in the PostFinance Checkout processing or confirmed status. Here the user is entering their payment details, or the details are being processed.";
$_['entry_failed_status']    						= "Failed status";
$_['description_failed_status']    					= "Status the order enters when the transaction is in the PostFinance Checkout failed status.";
$_['entry_authorized_status'] 						= "Authorized status";
$_['description_authorized_status'] 				= "Status the order enters when the transaction is in the PostFinance Checkout authorized status. Here the amount has been reserved from the customers account, and must manually be completed.";
$_['entry_voided_status'] 							= "Voided status";
$_['description_voided_status'] 					= "Status the order enters when the transaction is in the PostFinance Checkout voided status.";
$_['entry_completed_status'] 						= "Completed status";
$_['description_completed_status'] 					= "Status the order enters when the transaction is in the PostFinance Checkout completed status. This status is only set if a manual decision for the fulfillment is required.";
$_['entry_fulfill_status'] 							= "Fulfill status";
$_['description_fulfill_status'] 					= "Status the order enters when the transaction is in the PostFinance Checkout fulfill status.";
$_['entry_decline_status'] 							= "Decline status";
$_['description_decline_status'] 					= "Status the order enters when the transaction is in the PostFinance Checkout declined status.";
$_['entry_refund_status'] 							= "Refund status";
$_['description_refund_status'] 					= "Status the order enters when the transaction has been fully refunded.";
$_['description_none_status']						= "Standard OpenCart behaviour specificies that an order confirmation email is sent when an order is moved from the 'None' state into any other state. For this reason you may want to treat 'Processing' and 'Failed' as 'None' so no emails are sent.";

// Version
$_['title_version']									= 'Plugin Version';
$_['entry_version']									= 'Version Number';
$_['entry_date']									= 'Release Date';

// Modifications
$_['title_modifications']							= "Modifications";
$_['entry_core']									= "Core";
$_['description_core']								= "This modification helps create all required files, and modifies others so the dynamically created files can be correctly utilized.";
$_['entry_administration']							= "Administration";
$_['description_administration']					= "This modification adds PostFinance Checkout information to the order view in the admin area, and allows completions, refunds and voids to be initiated through Opencart.";
$_['entry_email']									= "Email confirmation";
$_['description_email']								= "You can deactivate or activate order confirmation emails by usage of our modification, 'Prevent confirmation email'. This modification affects only orders created using the PostFinance Checkout payment plugin.";
$_['entry_alerts']									= "Alerts / Notifications";
$_['description_alerts']							= "This modification will add alerts to the top of the Opencart admin area which displays failed jobs and open manual tasks.";
$_['entry_pdf']										= "Frontend PDF";
$_['description_pdf']								= "This modification allows customers to download invoices and packing slips in their order view. Can be restricted in the configurations.";
$_['entry_checkout']								= "QuickCheckout Compatibility";
$_['description_checkout']							= "This modification adds compatibility to the Ajax QuickCheckout plugin. Does not affect core files.";

// Migration
$_['title_migration']								= "Migration (current state)";
$_['entry_migration_name']							= "Name";
$_['entry_migration_version']						= "Version";

// other
$_['text_edit']										= "Edit";
$_['text_payment']									= "Payment";
$_['text_information']								= "Information";

$_['text_enabled']									= "Enabled";
$_['text_disabled']									= "Disabled";
$_['entry_status']									= "Status";

$_['message_saved_settings']						= "The settings have been saved successfully. Don't forget to refresh the modifications!";

// errors
$_['error_application_key_unset']					= "Application key must not be empty.";
$_['error_space_id_unset']							= "Space ID must not be empty.";
$_['error_space_id_numeric']						= "Space ID must be numeric.";
$_['error_space_view_id_numeric']					= "Space view ID must be numeric.";
$_['error_user_id_unset']							= "User ID must not be empty.";
$_['error_user_id_numeric']							= "User ID must be numeric.";

/**
 * ===========================================================================================
 * Transaction list
 * ===========================================================================================
 */
$_['heading_transaction_list']				        = "PostFinanceCheckout Transactions";
$_['text_transaction_list']	    			        = "Transactions";
$_['column_id']										= "ID";
$_['column_order_id']								= "Order ID";
$_['column_transaction_id']							= "Transaction ID";
$_['column_space_id']								= "Space ID";
$_['column_space_view_id']							= "Space View ID";
$_['column_state']									= "State";
$_['column_payment_method']							= "Payment Method";
$_['description_payment_method']					= "Here you may search using the payment method ID - e.g. searching for 'Credit Card' will not return any results.";
$_['column_created']								= "Created";
$_['column_updated']								= "Created";
$_['column_authorization_amount']					= "Amount";

/**
 * ===========================================================================================
 * Alerts
 * ===========================================================================================
 */
$_['title_notifications']							= 'PostFinance Checkout notifications';
$_['manual_task']									= 'Manual tasks';
$_['failed_jobs']									= 'Failed operations';	
/**
 * ===========================================================================================
 * Update
 * ===========================================================================================
 */
$_['message_refresh_success']						= 'Refresh succesfull';
$_['message_resend_completion']						= 'Re-sent completion %s';
$_['message_resend_void']							= 'Re-sent void %s';
$_['message_resend_refund']							= 'Re-sent refund %s';

/**
 * ===========================================================================================
 * Labels (order info
 * ===========================================================================================
 */

// Labels
$_['label_default']									= 'Default';
$_['label_postfinancecheckout_id']				= 'PostFinance Checkout ID';
$_['description_postfinancecheckout_id']			= 'Internal ID used by PostFinance Checkout to identify resource';
$_['label_postfinancecheckout_link']				= 'PostFinance Checkout Link';
$_['description_postfinancecheckout_link']		= 'Open the resource in the PostFinance Checkout application';
$_['label_status']									= 'Status';
$_['label_amount']									= 'Amount';
$_['label_failure']									= 'Failure reason';
$_['description_failure']							= 'The reason why the operation failed.';
$_['yes']											= 'Yes';
$_['no']											= 'No';

// downloads
$_['description_downloads']							= 'Download documents generated by PostFinance Checkout directly in the store backend. These available documents depends on the settings of the plugin, as well as the state of the transaction.';
$_['link_download']									= 'Download';
$_['label_invoice']									= 'Invoice';
$_['description_invoice']							= 'Here you may download the invoice document created by PostFinance Checkout for this transaction.';
$_['label_packing']									= 'Packing slip';
$_['description_packing']							= 'Here you may download the packing slip created by PostFinance Checkout for this transaction.';

// Transaction information
$_['title_transaction_information']					= 'Transaction information';
$_['description_default_transaction_information']	= 'Default transaction information';
$_['link_transaction']								= 'Open transaction';

// Completion
$_['title_completion']								= 'Completion information';
$_['label_completion']								= 'Completion %s';
$_['description_default_completion']				= 'Default completion information';
$_['link_completion']								= 'Open completion';
$_['description_refund_amount']						= 'Amount which was authorized by this operation';

// Refund
$_['title_refund']									= 'Refund information';
$_['label_refund']									= 'Refund %s';
$_['description_default_refund']					= 'Default refund information';
$_['link_refund']									= 'Open refund';
$_['label_external']								= 'External ID';
$_['description_external']							= 'External ID transmitted to PostFinance Checkout. Will usually be r-{order id}-{refund number}';
$_['description_refund_amount']						= 'Amount which was refunded by this operation';
$_['label_restock']									= 'Restock';
$_['description_restock']							= 'If this refund automatically restocked the available quantities in the store. This can be set when initiating the refund.';

// Void
$_['title_void']									= 'Void information';
$_['label_void']									= 'Void %s';
$_['description_default_void']						= 'Default void information';
$_['link_void']										= 'Open void';


/**
 * ===========================================================================================
 * Completion
 * ===========================================================================================
 */
// errors
$_['error_permission']								= 'You do not have permission to access this page.';
$_['error_already_running']							= 'A job is already running, please wait for it to complete.';
$_['error_cannot_create_job']						= 'The job could not be created. The transaction may be in an invalid state.';

// success
$_['message_completion_success']					= 'Transaction %s: Completion has been created successfully.';


/**
 * ===========================================================================================
 * Void
 * ===========================================================================================
 */
// success
$_['message_void_success']							= 'Transaction %s: Void has been created successfully.';

/**
 * ===========================================================================================
 * Order info (buttons)
 * ===========================================================================================
 */
$_['button_refund']									= 'Refund';
$_['button_complete']								= 'Complete';
$_['button_void']									= 'Void';
$_['button_update']									= 'Update';

/**
 * ===========================================================================================
 * Refund page
 * ===========================================================================================
 */
$_['heading_refund']								= 'PostFinance Checkout Refund';

// Description
$_['entry_refund']									= 'Refund';
$_['description_refund']							= 'Here you can refund the transaction.';
$_['description_fixed_tax']							= 'This transaction contains items with fixed taxes. These must refunded separately to their respective items.';

// breadcrumb
$_['entry_order']									= 'Order info';

// table headers
$_['entry_item']									= 'Line Item';
$_['entry_type']									= 'Type';
$_['entry_quantity']								= 'Quantity';
$_['entry_tax']										= 'Taxes';
$_['entry_amount']									= 'Amount incl. tax';
$_['entry_unit_amount']								= 'Unit Price';
$_['entry_total']									= 'Total';

// item labels
$_['entry_name']									= 'Name';
$_['entry_sku']										= 'SKU';
$_['entry_id']										= 'Unique ID';

$_['rounding_adjustment_item_name']					= 'Rounding Adjustment';

$_['entry_restock']									= 'Restock products';
$_['button_reset']									= 'Reset';
$_['button_full']									= 'Full order';
$_['button_refund']									= 'Refund';
$_['button_cancel']									= 'Cancel';

// tooltip
$_['type_shipping']									= 'Shipping';
$_['type_product']									= 'Product';
$_['type_discount']									= 'Discount';
$_['type_fee']										= 'Fee';

// errors
$_['error_order_id']								= 'No order id set.';
$_['error_not_postfinancecheckout']				= 'The order id could not be matched to a PostFinance Checkout transaction.';
$_['error_empty_refund']							= 'An empty refund is not permitted.';
$_['error_currency']								= 'Could not retrieve currency information.';
