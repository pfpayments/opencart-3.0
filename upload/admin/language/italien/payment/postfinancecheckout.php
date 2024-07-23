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
$_['heading_title']									= "PostFinance Checkout";
$_['heading_postfinancecheckout']					= "PostFinance Checkout";
$_['text_postfinancecheckout']					= '<a target="_BLANK" href="https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html"><img src="view/image/extension/payment/postfinancecheckout.png" alt="PostFinance Checkout Website" title="PostFinance Checkout Website" style="border: 1px solid #EEEEEE;" /></a>';
// PostFinanceCheckout Configurations (user id, application key, spaces)
$_['title_global_settings']							= "Credenziali globali";
$_['title_store_settings']							= "Impostazioni del negozio";
$_['entry_user_id']									= "Id utente";
$_['help_user_id']									= "Potete trovare il vostro ID utente nel vostro account PostFinance Checkout. Questa impostazione è condivisa da tutti i negozi";
$_['entry_application_key']							= "Chiave di applicazione";
$_['help_application_key']							= "La chiave dell'applicazione viene visualizzata una sola volta quando si imposta PostFinance Checkout. Questa impostazione è condivisa da tutti i negozi.";
$_['entry_space_id']								= "Spazio Id";
$_['help_space_id']									= "Potete trovare il vostro Space Id nel vostro account PostFinance Checkout.";

$_['title_space_view_id']							= "Opzioni vista spazio";
$_['entry_space_view_id']							= "Id vista spazio";
$_['help_space_view_id']							= "L'ID della vista dello spazio consente di controllare lo stile del modulo di pagamento e della pagina di pagamento all'interno dello spazio. Nelle configurazioni multi-negozio, consente di adattare il modulo di pagamento a stili diversi per ogni sotto-negozio, senza richiedere uno spazio dedicato.";

// downloads
$_['title_downloads']								= "Documenti";
$_['entry_download_invoice']						= "Download fattura";
$_['entry_download_packaging']						= "Download della confezione";
$_['description_download_invoice']					= "Consentire ai clienti di scaricare la fattura";
$_['description_download_packaging']				= "Consentire ai clienti di scaricare la bolla di accompagnamento";

// Debug
$_['title_debug']									= "Debug";
$_['entry_log_level']								= "Livello di log";
$_['help_log_level']								= "Qui si può impostare il tipo di informazioni da registrare; gli errori vengono sempre registrati.";
$_['log_level_error']								= "Errore";
$_['log_level_debug']								= "Debug";

// Rounding
$_['title_rounding_adjustment']						= "Arrotondamenti";
$_['entry_rounding_adjustment']						= "Invia voce";
$_['description_rounding_adjustment']				= "Se non riusciamo a mappare esattamente i totali di Opencart con le voci di PostFinance Checkout, invece di disabilitare i metodi di pagamento si può scegliere di inviare un Articolo di rettifica che contenga la differenza. In questo caso, gli importi delle imposte potrebbero non essere più esatti e funzioni come la riconciliazione in PostFinance Checkout e i rimborsi dal backend di Opencart potrebbero non funzionare completamente.";

// Status
$_['title_payment_status']							= "Mappatura dello stato";
$_['entry_processing_status'] 						= "Stato di elaborazione";
$_['description_processing_status'] 				= "Stato in cui l'ordine entra quando la transazione si trova nello stato di elaborazione o di conferma PostFinance Checkout. In questo caso, l'utente sta inserendo i dettagli del pagamento o i dettagli sono in fase di elaborazione.";
$_['entry_failed_status']    						= "Stato di fallimento";
$_['description_failed_status']    					= "Stato in cui l'ordine entra quando la transazione si trova nello stato PostFinance Checkout failed.";
$_['entry_authorized_status'] 						= "Stato autorizzato";
$_['description_authorized_status'] 				= "Stato in cui l'ordine entra quando la transazione è nello stato autorizzato PostFinance Checkout. In questo caso, l'importo è stato riservato dal conto del cliente e deve essere completato manualmente.";
$_['entry_voided_status'] 							= "Stato di annullamento";
$_['description_voided_status'] 					= "Stato in cui l'ordine entra quando la transazione è nello stato di annullamento PostFinance Checkout.";
$_['entry_completed_status'] 						= "Stato completato";
$_['description_completed_status'] 					= "Stato in cui l'ordine entra quando la transazione si trova nello stato PostFinance Checkout completed. Questo stato viene impostato solo se è necessaria una decisione manuale per l'evasione.";
$_['entry_fulfill_status'] 							= "Adempiere allo stato";
$_['description_fulfill_status'] 					= "Stato in cui entra l'ordine quando la transazione è nello stato di adempimento PostFinance Checkout.";
$_['entry_decline_status'] 							= "Stato di declino";
$_['description_decline_status'] 					= "Stato in cui entra l'ordine quando la transazione si trova nello stato PostFinance Checkout declinato.";
$_['entry_refund_status'] 							= "Stato del rimborso";
$_['description_refund_status'] 					= "Stato in cui l'ordine entra quando la transazione è stata completamente rimborsata.";
$_['description_none_status']						= "Il comportamento standard di OpenCart specifica che un'e-mail di conferma dell'ordine viene inviata quando un ordine passa dallo stato 'Nessuno' a qualsiasi altro stato. Per questo motivo si potrebbe voler trattare gli stati 'In elaborazione' e 'Non riuscito' come 'Nessuno', in modo che non venga inviata alcuna e-mail.";

// Version
$_['title_version']									= "Versione del plugin";
$_['entry_version']									= "Numero di versione";
$_['entry_date']									= "Data di rilascio";

// Modifications
$_['title_modifications']							= "Modifiche";
$_['entry_core']									= "Core";
$_['description_core']								= "Questa modifica aiuta a creare tutti i file necessari e ne modifica altri, in modo che i file creati dinamicamente possano essere utilizzati correttamente.";
$_['entry_administration']							= "Amministrazione";
$_['description_administration']					= "Questa modifica aggiunge le informazioni PostFinance Checkout alla visualizzazione dell'ordine nell'area di amministrazione e consente di avviare le operazioni di completamento, rimborso e annullamento tramite Opencart.";
$_['entry_email']									= "Conferma via e-mail";
$_['description_email']								= "È possibile disattivare o attivare le e-mail di conferma dell'ordine utilizzando la nostra modifica 'Impedisci e-mail di conferma'. Questa modifica riguarda solo gli ordini creati con il plugin di pagamento PostFinance Checkout.";
$_['entry_alerts']									= "Avvisi / Notifiche";
$_['description_alerts']							= "Questa modifica aggiungerà degli avvisi nella parte superiore dell'area di amministrazione di Opencart che visualizzano i lavori falliti e le attività manuali aperte.";
$_['entry_pdf']										= "Frontend PDF";
$_['description_pdf']								= "Questa modifica consente ai clienti di scaricare fatture e bolle di accompagnamento nella visualizzazione dell'ordine. Può essere limitata nelle configurazioni.";
$_['entry_checkout']								= "Compatibilità con QuickCheckout";
$_['description_checkout']							= "Questa modifica aggiunge compatibilità al plugin Ajax QuickCheckout. Non influisce sui file del nucleo.";
$_['entry_events']									= "Eventi";
$_['description_events']							= "Questa modifica simula eventi che esistono nelle versioni successive di Opencart, ma non sono presenti in 3.0.x. A causa della natura di questa modifica, tutte le modifiche devono essere aggiornate due volte.";

// Migration
$_['title_migration']								= "Migrazione (stato attuale)";
$_['entry_migration_name']							= "Nome";
$_['entry_migration_version']						= "Versione";

// other
$_['text_edit']										= "Modifica";
$_['text_payment']									= "Pagamento";
$_['text_information']								= "Informazioni";

$_['text_enabled']									= "Abilitato";
$_['text_disabled']									= "Disabilitato";
$_['entry_status']									= "Stato";

$_['message_saved_settings']						= "Le impostazioni sono state salvate con successo. Non dimenticate di aggiornare le modifiche!";

// errors
$_['error_application_key_unset']					= "La chiave dell'applicazione non deve essere vuota";
$_['error_space_id_unset']							= "L'ID spazio non deve essere vuoto";
$_['error_space_id_numeric']						= "L'ID dello spazio deve essere numerico";
$_['error_space_view_id_numeric']					= "L'ID della vista dello spazio deve essere numerico";
$_['error_user_id_unset']							= "L'ID utente non deve essere vuoto";
$_['error_user_id_numeric']							= "L'ID utente deve essere numerico";

/**
 * ===========================================================================================
 * Transaction list
 * ===========================================================================================
 */
$_['heading_transaction_list']				        = "PostFinanceCheckout Transactions";
$_['text_transaction_list']	    			        = "Transazioni";
$_['column_id']										= "ID";
$_['column_order_id']								= "ID ordine";
$_['column_transaction_id']							= "ID transazione";
$_['column_space_id']								= "ID spazio";
$_['column_space_view_id']							= "ID vista spazio";
$_['column_state']									= "Stato";
$_['column_payment_method']							= "Metodo di pagamento";
$_['description_payment_method']					= "Qui è possibile effettuare la ricerca utilizzando l'ID del metodo di pagamento, ad esempio cercando 'Carta di credito' non si otterrà alcun risultato.";
$_['column_created']								= "Creato";
$_['column_updated']								= "Creato";
$_['column_authorization_amount']					= "Importo";

/**
 * ===========================================================================================
 * Alerts
 * ===========================================================================================
 */
$_['title_notifications']							= "Notifiche PostFinance Checkout";
$_['manual_task']									= "Attività manuali";
$_['failed_jobs']									= "Operazioni non riuscite";
/**
 * ===========================================================================================
 * Update
 * ===========================================================================================
 */
$_['message_refresh_success']						= "Refresh succesfull";
$_['message_resend_completion']						= "Reinvio completamento %s";
$_['message_resend_void']							= "Reinvio annullamento %s";
$_['message_resend_refund']							= "Reinvio rimborso %s";

/**
 * ===========================================================================================
 * Labels (order info
 * ===========================================================================================
 */

// Labels
$_['label_default']									= "Default";
$_['label_postfinancecheckout_id']				= "ID PostFinance Checkout";
$_['description_postfinancecheckout_id']			= "ID interno utilizzato da PostFinance Checkout per identificare la risorsa";
$_['label_postfinancecheckout_link']				= "PostFinance Checkout Link";
$_['description_postfinancecheckout_link']		= "Aprire la risorsa nell'applicazione PostFinance Checkout";
$_['label_status']									= "Stato";
$_['label_amount']									= "Importo";
$_['label_failure']									= "Motivo del fallimento";
$_['description_failure']							= "Il motivo per cui l'operazione non è riuscita";
$_['yes']											= "Sì";
$_['no']											= "No";

// downloads
$_['description_downloads']							= "Scaricare i documenti generati da PostFinance Checkout direttamente nel backend del negozio. I documenti disponibili dipendono dalle impostazioni del plugin e dallo stato della transazione.";
$_['link_download']									= "Download";
$_['label_invoice']									= "Fattura";
$_['description_invoice']							= "Qui è possibile scaricare il documento di fattura creato da PostFinance Checkout per questa transazione.";
$_['label_packing']									= "Foglio di imballaggio";
$_['description_packing']							= "Qui è possibile scaricare la bolla di accompagnamento creata da PostFinance Checkout per questa transazione.";

// Transaction information
$_['title_transaction_information']					= "Informazioni sulla transazione";
$_['description_default_transaction_information']	= "Informazioni predefinite sulla transazione";
$_['link_transaction']								= "Transazione aperta";

// Completion
$_['title_completion']								= "Informazioni sul completamento";
$_['label_completion']								= "Completamento %s";
$_['description_default_completion']				= "Informazioni di completamento predefinite";
$_['link_completion']								= "Completamento aperto";
$_['description_refund_amount']						= "Importo autorizzato da questa operazione";

// Refund
$_['title_refund']									= "Informazioni sul rimborso";
$_['label_refund']									= "Rimborso %s";
$_['description_default_refund']					= "Informazioni di rimborso predefinite";
$_['link_refund']									= "Rimborso aperto";
$_['label_external']								= "ID esterno";
$_['description_external']							= "ID esterno trasmesso a PostFinance Checkout. Di solito è r-{order id}-{refund number}";
$_['description_refund_amount']						= "Importo rimborsato da questa operazione";
$_['label_restock']									= "Ripristino";
$_['description_restock']							= "Se questo rimborso ha rifornito automaticamente le quantità disponibili nel negozio. Questo può essere impostato quando si avvia il rimborso.";

// Void
$_['title_void']									= "Informazioni sul vuoto";
$_['label_void']									= "Void %s";
$_['description_default_void']						= "Informazioni predefinite sul vuoto";
$_['link_void']										= "Aprire il vuoto";


/**
 * ===========================================================================================
 * Completion
 * ===========================================================================================
 */
// errors
$_['error_permission']								= "Non hai il permesso di accedere a questa pagina";
$_['error_already_running']							= "Un lavoro è già in esecuzione, si prega di attendere il suo completamento";
$_['error_cannot_create_job']						= "Non è stato possibile creare il lavoro. La transazione potrebbe essere in uno stato non valido";

// success
$_['message_completion_success']					= "Transazione %s: Il completamento è stato creato con successo.";


/**
 * ===========================================================================================
 * Void
 * ===========================================================================================
 */
// success
$_['message_void_success']							= "Transazione %s: Il vuoto è stato creato con successo.";

/**
 * ===========================================================================================
 * Order info (buttons)
 * ===========================================================================================
 */
$_['button_refund']									= "Rimborso";
$_['button_complete']								= "Completa";
$_['button_void']									= "Non valido";
$_['button_update']									= "Aggiornamento";

/**
 * ===========================================================================================
 * Refund page
 * ===========================================================================================
 */
$_['heading_refund']								= "PostFinance Checkout Rimborso";

// Description
$_['entry_refund']									= "Rimborso";
$_['description_refund']							= "Qui è possibile rimborsare la transazione.";
$_['description_fixed_tax']							= "Questa transazione contiene articoli con tasse fisse. Queste devono essere rimborsate separatamente ai rispettivi articoli.";

// breadcrumb
$_['entry_order']									= "Informazioni sull'ordine";

// table headers
$_['entry_item']									= "Voce";
$_['entry_type']									= "Tipo";
$_['entry_quantity']								= "Quantità";
$_['entry_tax']										= "Imposte";
$_['entry_amount']									= "Importo incl. imposte";
$_['entry_unit_amount']								= "Prezzo unitario";
$_['entry_total']									= "Totale";

// item labels
$_['entry_name']									= "Nome";
$_['entry_sku']										= "SKU";
$_['entry_id']										= "ID univoco";

$_['rounding_adjustment_item_name']					= "Aggiustamento dell'arrotondamento";

$_['entry_restock']									= "Ripristino prodotti";
$_['button_reset']									= "Ripristino";
$_['button_full']									= "Ordine completo";
$_['button_refund']									= "Rimborso";
$_['button_cancel']									= "Annulla";

// tooltip
$_['type_shipping']									= "Spedizione";
$_['type_product']									= "Prodotto";
$_['type_discount']									= "Sconto";
$_['type_fee']										= "Tassa";

// errors
$_['error_order_id']								= "Non è stato impostato alcun ID ordine.";
$_['error_not_postfinancecheckout']				= "Non è stato possibile associare l'id dell'ordine a una transazione PostFinance Checkout.";
$_['error_empty_refund']							= "Non è ammesso un rimborso a vuoto.";
$_['error_currency']								= "Impossibile recuperare le informazioni sulla valuta.";
