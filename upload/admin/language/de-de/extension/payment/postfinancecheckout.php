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
/**
 * ===========================================================================================
 * Module configuration
 * ===========================================================================================
 */
// @formatter:off
$_['heading_title']									= 'PostFinance Checkout';
$_['heading_postfinancecheckout']					= 'PostFinance Checkout';
$_['text_postfinancecheckout']					= '<a target="_BLANK" href="https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html"><img src="view/image/extension/payment/postfinancecheckout.png" alt="PostFinance Checkout Webseite" title="PostFinance Checkout Webseite" style="border: 1px solid #EEEEEE;" /></a>';
// PostFinanceCheckout Configurations (user id, application key, spaces)
$_['title_global_settings']							= "Globale Anmeldeinformationen";
$_['title_store_settings']							= "Einstellungen speichern";
$_['entry_user_id']									= "User ID";
$_['help_user_id']									= "Sie finden Ihre Benutzer-ID in Ihrem PostFinance Checkout-Konto. Diese Einstellung wird von allen Shops gemeinsam genutzt.";
$_['entry_application_key']							= "Application Key";
$_['help_application_key']							= "Ihr 'Application Key' wird einmalig beim Einrichten von PostFinance Checkout angezeigt. Diese Einstellung wird von allen Shops gemeinsam genutzt.";
$_['entry_space_id']								= "Space ID";
$_['help_space_id']									= "Sie finden Ihre Space-ID in Ihrem PostFinance Checkout-Konto.";

$_['title_space_view_id']							= "Space View Options";
$_['entry_space_view_id']							= "Space View Id";;
$_['help_space_view_id']							= "Die Bereichsansichts-ID ermöglicht die Steuerung des Stils des Zahlungsformulars und der Zahlungsseite innerhalb des Bereichs. In Multi-Store-Setups ermöglicht es die Anpassung des Zahlungsformulars an unterschiedliche Stile pro Sub-Store, ohne dass ein eigener Platz erforderlich ist.";

// downloads
$_['title_downloads']								= "Unterlagen";
$_['entry_download_invoice']						= "Rechnung herunterladen";
$_['entry_download_packaging']						= "Verpackung herunterladen";
$_['description_download_invoice']					= "Kunden erlauben, ihre Rechnung herunterzuladen";
$_['description_download_packaging']				= "Ermöglichen Sie Kunden, ihren Lieferschein herunterzuladen";

// Debug
$_['title_debug']									= 'Debuggen';
$_['entry_log_level']								= 'Protokollebene';
$_['help_log_level']								= 'Hier können Sie einstellen, welche Informationen protokolliert werden sollen, Fehler werden immer protokolliert.';
$_['log_level_error']								= 'Fehler';
$_['log_level_debug']								= 'Debuggen';

// Rounding
$_['title_rounding_adjustment']						= 'Rundungsanpassungen';
$_['entry_rounding_adjustment']						= 'Artikel senden';
$_['description_rounding_adjustment']				= 'Wenn wir die Opencart-Gesamtsummen nicht genau den PostFinance Checkout-Werbebuchungen zuordnen können, können Sie statt der Deaktivierung der Zahlungsmethoden stattdessen einen Anpassungsposten senden, der die Differenz enthält. In diesem Fall sind die Steuerbeträge möglicherweise nicht mehr genau und Funktionen wie der Abgleich in PostFinance Checkout und Rückerstattungen aus dem Opencart-Backend funktionieren möglicherweise nicht vollständig.';

// Status
$_['title_payment_status']							= "Statuszuordnung";
$_['entry_processing_status'] 						= "Bearbeitungsstatus";
$_['description_processing_status'] 				= "Status, den die Bestellung einnimmt, wenn sich die Transaktion im Bearbeitungs- oder Bestätigungsstatus von PostFinance Checkout befindet. Hier gibt der Benutzer seine Zahlungsdaten ein oder die Daten werden verarbeitet.";
$_['entry_failed_status']    						= "Fehlgeschlagener Status";
$_['description_failed_status']    					= "Status, den der Auftrag einnimmt, wenn sich die Transaktion im Status „PostFinance Checkout fehlgeschlagen“ befindet.";
$_['entry_authorized_status'] 						= "Autorisierter Status";
$_['description_authorized_status'] 				= "Status, den die Bestellung einnimmt, wenn sich die Transaktion im autorisierten PostFinance Checkout-Status befindet. Hier wurde der Betrag vom Kundenkonto reserviert und muss manuell vervollständigt werden.";
$_['entry_voided_status'] 							= "Ungültiger Status";
$_['description_voided_status'] 					= "Status, den die Bestellung einnimmt, wenn sich die Transaktion im PostFinance Checkout-Status „ungültig“ befindet.";
$_['entry_completed_status'] 						= "Abgeschlossen-Status";
$_['description_completed_status'] 					= "Status, den die Bestellung einnimmt, wenn sich die Transaktion im Status „PostFinance Checkout abgeschlossen“ befindet. Dieser Status wird nur gesetzt, wenn eine manuelle Entscheidung für die Erfüllung erforderlich ist.";
$_['entry_fulfill_status'] 							= "Status erfüllen";
$_['description_fulfill_status'] 					= "Status, den die Bestellung einnimmt, wenn sich die Transaktion im Erfüllungsstatus PostFinance Checkout befindet.";
$_['entry_decline_status'] 							= "Status ablehnen";
$_['description_decline_status'] 					= "Status, den die Bestellung einnimmt, wenn sich die Transaktion im PostFinance Checkout-Ablehnungsstatus befindet.";
$_['entry_refund_status'] 							= "Rückerstattungsstatus";
$_['description_refund_status'] 					= "Status, den die Bestellung einnimmt, wenn die Transaktion vollständig zurückerstattet wurde.";
$_['description_none_status']						= "Das Standardverhalten von OpenCart legt fest, dass eine Bestellbestätigungs-E-Mail gesendet wird, wenn eine Bestellung vom Status „Keine“ in einen anderen Status verschoben wird. Aus diesem Grund sollten Sie „In Bearbeitung“ und „Fehlgeschlagen“ als „Keine“ behandeln, damit keine E-Mails gesendet werden.";

// Version
$_['title_version']									= 'Plugin-Version';
$_['entry_version']									= 'Versionsnummer';
$_['entry_date']									= 'Veröffentlichungsdatum';

// Modifications
$_['title_modifications']							= "Anpassungen";
$_['entry_core']									= "Kern";
$_['description_core']								= "Diese Anpassungen hilft bei der Erstellung aller erforderlichen Dateien und modifiziert andere, damit die dynamisch erstellten Dateien korrekt verwendet werden können.";
$_['entry_administration']							= "Verwaltung";
$_['description_administration']					= "Diese Anpassungen fügt PostFinance Checkout-Informationen zur Bestellansicht im Admin-Bereich hinzu und ermöglicht die Initiierung von Vervollständigungen, Rückerstattungen und Stornierungen über Opencart.";
$_['entry_email']									= "Email Bestätigung";
$_['description_email']								= "Sie können Bestellbestätigungs-E-Mails deaktivieren oder aktivieren, indem Sie unsere Änderung „Bestätigungs-E-Mail verhindern“ verwenden. Diese Änderung betrifft nur Bestellungen, die mit dem Zahlungs-Plugin PostFinance Checkout erstellt wurden.";
$_['entry_alerts']									= "Benachrichtigungen";
$_['description_alerts']							= "Diese Anpassungen fügt Benachrichtigungen oben im Opencart-Adminbereich hinzu, der fehlgeschlagene Jobs und offene manuelle Aufgaben anzeigt.";
$_['entry_pdf']										= "Frontend PDF";
$_['description_pdf']								= "Diese Anpassungen ermöglicht es Kunden, Rechnungen und Lieferscheine in ihrer Bestellansicht herunterzuladen. Kann in den Konfigurationen eingeschränkt werden.";
$_['entry_checkout']								= "QuickCheckout Kompatibilität";
$_['description_checkout']							= "Diese Anpassungen fügt dem Ajax QuickCheckout-Plugin Kompatibilität hinzu. Wirkt sich nicht auf Core-Dateien aus.";
$_['entry_events']									= "Ereignisse";
$_['description_events']							= "Diese Anpassungen simuliert Ereignisse, die in späteren Opencart-Versionen vorhanden sind, aber in 3.0.x nicht vorhanden sind. Aufgrund der Art dieser Modifikation müssen alle Modifikationen zweimal aktualisiert werden.";

// Migration
$_['title_migration']								= "Migration (aktueller Stand)";
$_['entry_migration_name']							= "Name";
$_['entry_migration_version']						= "Ausführung";

// other
$_['text_edit']										= "Bearbeiten";
$_['text_payment']									= "Zahlung";
$_['text_information']								= "Information";

$_['text_enabled']									= "Aktiv";
$_['text_disabled']									= "Inaktiv";
$_['entry_status']									= "Status";

$_['message_saved_settings']						= "Die Einstellungen wurden erfolgreich gespeichert. Vergessen Sie nicht, die Anpassungen zu aktualisieren!";

// errors
$_['error_application_key_unset']					= "Application Key darf nicht leer sein.";
$_['error_space_id_unset']							= "Space ID darf nicht leer sein.";
$_['error_space_id_numeric']						= "Space ID muss numerisch sein.";
$_['error_space_view_id_numeric']					= "Space View ID muss numerisch sein.";
$_['error_user_id_unset']							= "User ID darf nicht leer sein.";
$_['error_user_id_numeric']							= "User ID muss numerisch sein.";

/**
 * ===========================================================================================
 * Transaction list
 * ===========================================================================================
 */
$_['heading_transaction_list']				        = "PostFinanceCheckout Transaktionen";
$_['text_transaction_list']	    			        = "Transaktionen";
$_['column_id']										= "ID";
$_['column_order_id']								= "Order ID";
$_['column_transaction_id']							= "Transaction ID";
$_['column_space_id']								= "Space ID";
$_['column_space_view_id']							= "Space View ID";
$_['column_state']									= "State";
$_['column_payment_method']							= "Zahlungsmethode";
$_['description_payment_method']					= "Hier können Sie anhand der ID der Zahlungsmethode suchen - z. Die Suche nach „Kreditkarte“ liefert keine Ergebnisse.";
$_['column_created']								= "Erstellt";
$_['column_updated']								= "Aktualisiert";
$_['column_authorization_amount']					= "Menge";

/**
 * ===========================================================================================
 * Alerts
 * ===========================================================================================
 */
$_['title_notifications']							= 'PostFinance Checkout Benachrichtigungen';
$_['manual_task']									= 'Manuelle Aufgaben';
$_['failed_jobs']									= 'Fehlgeschlagene Operationen';
/**
 * ===========================================================================================
 * Update
 * ===========================================================================================
 */
$_['message_refresh_success']						= 'Aktualisieren erfolgreich';
$_['message_resend_completion']						= 'Abschluss erneut gesendet %s';
$_['message_resend_void']							= 'Erneut gesendet ungültig %s';
$_['message_resend_refund']							= 'Rückerstattung erneut gesendet %s';

/**
 * ===========================================================================================
 * Labels (order info
 * ===========================================================================================
 */

// Labels
$_['label_default']									= 'Default';
$_['label_postfinancecheckout_id']				= 'PostFinance Checkout ID';
$_['description_postfinancecheckout_id']			= 'Internal ID benutzt von PostFinance Checkout Ressource zu identifizieren';
$_['label_postfinancecheckout_link']				= 'PostFinance Checkout Link';
$_['description_postfinancecheckout_link']		= 'Öffnen Sie die Ressource in der PostFinance Checkout Anwendung';
$_['label_status']									= 'Status';
$_['label_amount']									= 'Menge';
$_['label_failure']									= 'Ausfallgrund';
$_['description_failure']							= 'Der Grund, warum die Operation fehlgeschlagen ist.';
$_['yes']											= 'Ja';
$_['no']											= 'Nein';

// downloads
$_['description_downloads']							= 'Laden Sie Dokumente herunter, die von PostFinance Checkout erstellt wurden direkt im Shop-Backend. Diese verfügbaren Dokumente hängen von den Einstellungen des Plugins sowie vom Status der Transaktion ab.';
$_['link_download']									= 'Herunterladen';
$_['label_invoice']									= 'Rechnung';
$_['description_invoice']							= 'Hier können Sie das von PostFinance Checkout für diese Transaktion erstellte Rechnungsdokument herunterladen.';
$_['label_packing']									= 'Lieferschein';
$_['description_packing']							= 'Hier können Sie den von PostFinance Checkout erstellten Lieferschein herunterladen für diese Transaktion.';

// Transaction information
$_['title_transaction_information']					= 'Transaktionsinformationen';
$_['description_default_transaction_information']	= 'Standardtransaktionsinformationen';
$_['link_transaction']								= 'Transaktion öffnen';

// Completion
$_['title_completion']								= 'Abschlussinformationen';
$_['label_completion']								= 'Abschluss %s';
$_['description_default_completion']				= 'Standardabschlussinformationen';
$_['link_completion']								= 'Offener Abschluss';
$_['description_refund_amount']						= 'Betrag, der durch diese Operation autorisiert wurde';

// Refund
$_['title_refund']									= 'Rückerstattung-Informationen';
$_['label_refund']									= 'Rückerstattung %s';
$_['description_default_refund']					= 'Standardrückerstattungsinformationen';
$_['link_refund']									= 'Rückerstattung öffnen';
$_['label_external']								= 'External ID';
$_['description_external']							= 'External ID übermittelt an PostFinance Checkout. Wird normalerweise im format r-{order id}-{Rückerstattungsnummer} sein';
$_['description_refund_amount']						= 'Betrag, der durch diesen Vorgang erstattet wurde';
$_['label_restock']									= 'Rückerstatten';
$_['description_restock']							= 'Bei dieser Rückerstattung werden automatisch die verfügbaren Mengen im Laden wieder aufgefüllt. Dies kann beim Erstellen der Rückerstattung eingestellt werden.';

// Void
$_['title_void']									= 'Stornierung Informationen';
$_['label_void']									= 'Stornierung %s';
$_['description_default_void']						= 'Standard Stornierungsinformationen';
$_['link_void']										= 'Stornierung Öffnen';


/**
 * ===========================================================================================
 * Completion
 * ===========================================================================================
 */
// errors
$_['error_permission']								= 'Sie sind nicht berechtigt, auf diese Seite zuzugreifen.';
$_['error_already_running']							= 'Ein Job wird bereits ausgeführt, bitte warten Sie, bis er abgeschlossen ist.';
$_['error_cannot_create_job']						= 'Der Job konnte nicht erstellt werden. Die Transaktion befindet sich möglicherweise in einem ungültigen Zustand.';

// success
$_['message_completion_success']					= 'Transaktion %s: Abschluss wurde erfolgreich erstellt.';


/**
 * ===========================================================================================
 * Void
 * ===========================================================================================
 */
// success
$_['message_void_success']							= 'Transaktion %s: Stornierung wurde erfolgreich erstellt.';

/**
 * ===========================================================================================
 * Order info (buttons)
 * ===========================================================================================
 */
$_['button_refund']									= 'Rückerstatten';
$_['button_complete']								= 'Abschliessen';
$_['button_void']									= 'Stornieren';
$_['button_update']									= 'Aktualisieren';

/**
 * ===========================================================================================
 * Refund page
 * ===========================================================================================
 */
$_['heading_refund']								= 'PostFinance Checkout Rückerstattung';

// Description
$_['entry_refund']									= 'Rückerstattung';
$_['description_refund']							= 'Hier können Sie die Transaktion rückerstatten.';
$_['description_fixed_tax']							= 'Diese Transaktion enthält Artikel mit festen Steuern. Diese müssen separat zu den jeweiligen Artikeln erstattet werden.';

// breadcrumb
$_['entry_order']									= 'Bestellinformationen';

// table headers
$_['entry_item']									= 'Einzelposten';
$_['entry_type']									= 'Typ';
$_['entry_quantity']								= 'Menge';
$_['entry_tax']										= 'Steuern';
$_['entry_amount']									= 'Betrag inkl. MwSt';
$_['entry_unit_amount']								= 'Stückpreis';
$_['entry_total']									= 'Gesamt';

// item labels
$_['entry_name']									= 'Name';
$_['entry_sku']										= 'SKU';
$_['entry_id']										= 'Unique ID';

$_['rounding_adjustment_item_name']					= 'Rundungsanpassung';

$_['entry_restock']									= 'Produkte aufstocken';
$_['button_reset']									= 'Zurücksetzen';
$_['button_full']									= 'Vollständige Bestellung';
$_['button_refund']									= 'Rückerstatten';
$_['button_cancel']									= 'Abbrechen';

// tooltip
$_['type_shipping']									= 'Versand';
$_['type_product']									= 'Produkt';
$_['type_discount']									= 'Rabatt';
$_['type_fee']										= 'Gebühr';

// errors
$_['error_order_id']								= 'Keine Bestellnummer vorhanden.';
$_['error_not_postfinancecheckout']				= 'Order id konnte keiner PostFinance Checkout Transaktion zugeordnet werden.';
$_['error_empty_refund']							= 'Eine leere Rückerstattung ist nicht zulässig.';
$_['error_currency']								= 'Währungsinformationen konnten nicht abgerufen werden.';
