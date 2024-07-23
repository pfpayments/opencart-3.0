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
$_['text_postfinancecheckout']					= '<a target="_BLANK" href="https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html"><img src="view/image/extension/payment/postfinancecheckout.png" alt="PostFinance Checkout Webseite" title="PostFinance Checkout Webseite" style="border: 1px solid #EEEEEE;" /></a>';
// PostFinanceCheckout Configurations (user id, application key, spaces)
$_['title_global_settings']							= "Références globales";
$_['title_store_settings']							= "Paramètres du magasin";
$_['entry_user_id']									= "Id utilisateur";
$_['help_user_id']									= "Vous pouvez trouver votre ID utilisateur dans votre compte PostFinance Checkout. Ce paramètre est partagé par tous les magasins";
$_['entry_application_key']							= "Clé d'application";
$_['help_application_key']							= "Votre clé d'application est affichée une fois lors de la configuration du PostFinance Checkout. Ce paramètre est partagé par tous les magasins.";
$_['entry_space_id']								= "ID de l'espace";
$_['help_space_id']									= "Vous pouvez trouver votre Identifiant d'espace dans votre compte PostFinance Checkout.";

$_['title_space_view_id']							= "Options de visualisation de l'espace";
$_['entry_space_view_id']							= "Vue de l'espace Id";
$_['help_space_view_id']							= "L'ID de vue de l'espace permet de contrôler le style du formulaire de paiement et de la page de paiement dans l'espace. Dans les configurations multi-boutiques, cela permet d'adapter le formulaire de paiement à un style différent pour chaque sous-boutique sans avoir besoin d'un espace dédié.";

// downloads
$_['title_downloads']								= "Documents";
$_['entry_download_invoice']						= "Téléchargement de la facture";
$_['entry_download_packaging']						= "Téléchargement de l'emballage";
$_['description_download_invoice']					= "Permettre aux clients de télécharger leur facture" ;
$_['description_download_packaging']				= "Permettre aux clients de télécharger leur bordereau d'emballage" ;

// Debug
$_['title_debug']									= "Debug";
$_['entry_log_level']								= "Niveau de journalisation";
$_['help_log_level']								= "Ici, vous pouvez définir quel type d'information doit être enregistré, les erreurs sont toujours enregistrées.";
$_['log_level_error']								= "Erreur";
$_['log_level_debug']								= "Debug";

// Rounding
$_['title_rounding_adjustment']						= "Ajustements d'arrondis";
$_['entry_rounding_adjustment']						= "Envoyer l'article";
$_['description_rounding_adjustment']				= "Si nous ne pouvons pas faire correspondre exactement les totaux d'Opencart aux postes de la PostFinance Checkout, au lieu de désactiver les méthodes de paiement, vous pouvez choisir d'envoyer un élément d'ajustement qui contient la différence. Dans ce cas, il se peut que les montants des taxes ne soient plus exacts et que des fonctions telles que la réconciliation dans PostFinance Checkout et les remboursements à partir du backend Opencart ne fonctionnent pas complètement.";

// Status
$_['title_payment_status']							= "Cartographie de l'état ";
$_['entry_processing_status'] 						= "Statut de traitement";
$_['description_processing_status'] 				= "Statut dans lequel la commande entre lorsque la transaction est dans le statut PostFinance Checkout de traitement ou de confirmation. Ici, l'utilisateur est en train de saisir les détails de son paiement, ou les détails sont en cours de traitement.";
$_['entry_failed_status']    						= "Statut d'échec";
$_['description_failed_status']    					= "Statut dans lequel l'ordre entre lorsque la transaction est dans le statut PostFinance Checkout failed.";
$_['entry_authorized_status'] 						= "Statut autorisé";
$_['description_authorized_status'] 				= "Statut dans lequel la commande entre lorsque la transaction est dans le statut autorisé PostFinance Checkout. Ici, le montant a été réservé à partir du compte du client, et doit être complété manuellement.";
$_['entry_voided_status'] 							= "Statut annulé";
$_['description_voided_status'] 					= "Statut dans lequel l'ordre entre lorsque la transaction est dans le statut PostFinance Checkout annulé.";
$_['entry_completed_status'] 						= "État d'achèvement";
$_['description_completed_status'] 					= "Statut dans lequel l'ordre entre lorsque la transaction est dans le statut PostFinance Checkout completed. Ce statut n'est activé que si une décision manuelle pour l'accomplissement est requise.";
$_['entry_fulfill_status'] 							= "Remplir le statut";
$_['description_fulfill_status'] 					= "Statut dans lequel l'ordre entre lorsque la transaction est dans le statut de réalisation PostFinance Checkout.";
$_['entry_decline_status'] 							= "Statut de déclin";
$_['description_decline_status'] 					= "État dans lequel l'ordre entre lorsque la transaction est dans l'état refusé PostFinance Checkout.";
$_['entry_refund_status'] 							= "État des remboursements";
$_['description_refund_status'] 					= "Statut que prend la commande lorsque la transaction a été entièrement remboursée.";
$_['description_none_status']						= "Le comportement standard d'OpenCart spécifie qu'un e-mail de confirmation de commande est envoyé lorsqu'une commande passe de l'état \"Aucun\" à un autre état. Pour cette raison, vous pouvez vouloir traiter les états 'Processing' et 'Failed' comme 'None' afin qu'aucun email ne soit envoyé.";

// Version
$_['title_version']									= "Version du plugin";
$_['entry_version']									= "Numéro de version";
$_['entry_date']									= "Date de sortie";

// Modifications
$_['title_modifications']							= "Modifications";
$_['entry_core']									= "Core";
$_['description_core']								= "Cette modification permet de créer tous les fichiers requis et d'en modifier d'autres afin que les fichiers créés dynamiquement puissent être utilisés correctement.";
$_['entry_administration']							= "Administration";
$_['description_administration']					= "Cette modification ajoute des informations sur le PostFinance Checkout à la vue de la commande dans la zone d'administration, et permet d'initier des complétions, des remboursements et des annulations via Opencart.";
$_['entry_email']									= "Confirmation par courriel";
$_['description_email']								= "Vous pouvez désactiver ou activer les e-mails de confirmation de commande en utilisant notre modification, 'Prevent confirmation email'. Cette modification ne concerne que les commandes créées à l'aide du plugin de paiement PostFinance Checkout.";
$_['entry_alerts']									= "Alertes / Notifications";
$_['description_alerts']							= "Cette modification ajoutera des alertes en haut de la zone d'administration d'Opencart qui affichent les tâches échouées et les tâches manuelles ouvertes.";
$_['entry_pdf']										= "PDF frontal";
$_['description_pdf']								= "Cette modification permet aux clients de télécharger les factures et les bordereaux d'expédition dans la vue de leur commande. Elle peut être limitée dans les configurations.";
$_['entry_checkout']								= "Compatibilité avec QuickCheckout";
$_['description_checkout']							= "Cette modification ajoute la compatibilité avec le plugin Ajax QuickCheckout. N'affecte pas les fichiers de base.";
$_['entry_events']									= "Événements";
$_['description_events']							= "Cette modification simule des événements qui existent dans les versions ultérieures d'Opencart, mais qui ne sont pas présents dans 3.0.x. En raison de la nature de cette modification, toutes les modifications doivent être rafraîchies deux fois.";

// Migration
$_['title_migration']								= "Migration (état actuel)";
$_['entry_migration_name']							= "Nom";
$_['entry_migration_version']						= "Version";

// other
$_['text_edit']										= "Editer";
$_['text_payment']									= "Paiement";
$_['text_information']								= "Information";

$_['text_enabled']									= "Activé";
$_['text_disabled']									= "Désactivé";
$_['entry_status']									= "Statut";

$_['message_saved_settings']						= "Les paramètres ont été enregistrés avec succès. N'oubliez pas de rafraîchir les modifications!";

// errors
$_['error_application_key_unset']					= "La clé de l'application ne doit pas être vide.";
$_['error_space_id_unset']							= "L'ID de l'espace ne doit pas être vide.";
$_['error_space_id_numeric']						= "L'ID de l'espace doit être numérique.";
$_['error_space_view_id_numeric']					= "L'ID de la vue de l'espace doit être numérique.";
$_['error_user_id_unset']							= "L'ID de l'utilisateur ne doit pas être vide.";
$_['error_user_id_numeric']							= "L'ID de l'utilisateur doit être numérique.";

/**
 * ===========================================================================================
 * Transaction list
 * ===========================================================================================
 */
$_['heading_transaction_list']				        = "PostFinanceCheckout Transactions";
$_['text_transaction_list']	    			        = "Transactions";
$_['column_id']										= "ID";
$_['column_order_id']								= "Commandez ID";
$_['column_transaction_id']							= "Transaction ID";
$_['column_space_id']								= "Espace ID";
$_['column_space_view_id']							= "ID de la vue de l'espace";
$_['column_state']									= "État";
$_['column_payment_method']							= "Mode de paiement";
$_['description_payment_method']					= "Ici, vous pouvez effectuer une recherche en utilisant l'ID du mode de paiement - par exemple, si vous recherchez 'Carte de crédit', vous n'obtiendrez aucun résultat.";
$_['column_created']								= "Créé";
$_['column_updated']								= "Créé";
$_['column_authorization_amount']					= "Montant";

/**
 * ===========================================================================================
 * Alerts
 * ===========================================================================================
 */
$_['title_notifications']							= "Notifications PostFinance Checkout";
$_['manual_task']									= "Tâches manuelles";
$_['failed_jobs']									= "Transactions échouées";
/**
 * ===========================================================================================
 * Update
 * ===========================================================================================
 */
$_['message_refresh_success']						= "Refresh succesfull";
$_['message_resend_completion']						= "Renvoi de l'achèvement %s";
$_['message_resend_void']							= "Renvoyer void %s";
$_['message_resend_refund']							= "Renvoi du remboursement %s";

/**
 * ===========================================================================================
 * Labels (order info
 * ===========================================================================================
 */

// Labels
$_['label_default']									= "Default";
$_['label_postfinancecheckout_id']				= "PostFinance Checkout ID";
$_['description_postfinancecheckout_id']			= "ID interne utilisé par PostFinance Checkout pour identifier la ressource";
$_['label_postfinancecheckout_link']				= "Lien PostFinance Checkout";
$_['description_postfinancecheckout_link']		= "Ouvrir la ressource dans l'application PostFinance Checkout";
$_['label_status']									= "Statut";
$_['label_amount']									= "Montant";
$_['label_failure']									= "Raison de l'échec'";
$_['description_failure']							= "La raison pour laquelle l'transaction a échoué";
$_['yes']											= "Oui";
$_['no']											= "Non";

// downloads
$_['description_downloads']							= "Téléchargez les documents générés par PostFinance Checkout directement dans le backend de la boutique. Ces documents disponibles dépendent des paramètres du plugin, ainsi que de l'état de la transaction.";
$_['link_download']									= "Télécharger";
$_['label_invoice']									= "Facture";
$_['description_invoice']							= "Vous pouvez télécharger ici la facture créée par PostFinance Checkout pour cette transaction.";
$_['label_packing']									= "Fiche d'emballage";
$_['description_packing']							= "Vous pouvez télécharger ici le bordereau d'expédition créé par PostFinance Checkout pour cette transaction.";

// Transaction information
$_['title_transaction_information']					= "Informations sur la transaction";
$_['description_default_transaction_information']	= "Informations sur la transaction par défaut";
$_['link_transaction']								= "Ouvrir une transaction";

// Completion
$_['title_completion']								= "Informations d'achèvement";
$_['label_completion']								= "Complément %s";
$_['description_default_completion']				= "Informations sur l'achèvement par défaut";
$_['link_completion']								= "Finalisation ouverte";
$_['description_refund_amount']						= "Montant qui a été autorisé par cette transaction";

// Refund
$_['title_refund']									= "Informations sur le remboursement";
$_['label_refund']									= "Remboursement %s";
$_['description_default_refund']					= "Informations sur le remboursement par défaut";
$_['link_refund']									= "Remboursement ouvert";
$_['label_external']								= "ID externe";
$_['description_external']							= "ID externe transmis au PostFinance Checkout. Sera généralement r-{order id}-{refund number}";
$_['description_refund_amount']						= "Montant qui a été remboursé par cette transaction";
$_['label_restock']									= "Réapprovisionnement";
$_['description_restock']							= "Si ce remboursement réapprovisionne automatiquement les quantités disponibles dans le magasin. Ce paramètre peut être défini lors du lancement du remboursement.";

// Void
$_['title_void']									= "Informations nulles";
$_['label_void']									= "Void %s";
$_['description_default_void']						= "Information void par défaut";
$_['link_void']										= "Ouvrir void";


/**
 * ===========================================================================================
 * Completion
 * ===========================================================================================
 */
// errors
$_['error_permission']								= "Vous n'êtes pas autorisé à accéder à cette page"; ;
$_['error_already_running']							= "Une tâche est déjà en cours d'exécution, veuillez attendre qu'elle se termine";
$_['error_cannot_create_job']						= "Le travail n'a pas pu être créé. La transaction est peut-être dans un état invalide";

// success
$_['message_completion_success']					= "Transaction %s: L'achèvement a été créé avec succès.";


/**
 * ===========================================================================================
 * Void
 * ===========================================================================================
 */
// success
$_['message_void_success']							= "Transaction %s: Void a été créée avec succès.";

/**
 * ===========================================================================================
 * Order info (buttons)
 * ===========================================================================================
 */
$_['button_refund']									= "Remboursement";
$_['button_complete']								= "Compléter";
$_['button_void']									= "Annuler";
$_['button_update']									= "Mise à jour";

/**
 * ===========================================================================================
 * Refund page
 * ===========================================================================================
 */
$_['heading_refund']								= "PostFinance Checkout Remboursement";

// Description
$_['entry_refund']									= "Remboursement";
$_['description_refund']							= "Ici, vous pouvez rembourser la transaction.";
$_['description_fixed_tax']							= "Cette transaction contient des articles avec des taxes fixes. Celles-ci doivent être remboursées séparément à leurs articles respectifs.";

// breadcrumb
$_['entry_order']									= "Informations sur la commande";

// table headers
$_['entry_item']									= "Poste de ligne";
$_['entry_type']									= "Type";
$_['entry_quantity']								= "Quantité";
$_['entry_tax']										= "Taxes";
$_['entry_amount']									= "Montant TTC";
$_['entry_unit_amount']								= "Prix unitaire";
$_['entry_total']									= "Total";

// item labels
$_['entry_name']									= "Nom";
$_['entry_sku']										= "SKU";
$_['entry_id']										= "ID unique";

$_['rounding_adjustment_item_name']					= "Ajustement des arrondis";

$_['entry_restock']									= "Réapprovisionner les produits";
$_['button_reset']									= "Réinitialiser";
$_['button_full']									= "Commande complète";
$_['button_refund']									= "Remboursement";
$_['button_cancel']									= "Annuler";

// tooltip
$_['type_shipping']									= "Expédition";
$_['type_product']									= "Produit";
$_['type_discount']									= "Remise";
$_['type_fee']										= "Frais";

// errors
$_['error_order_id']								= "Aucun identifiant de commande n'a été défini.";
$_['error_not_postfinancecheckout']				= "L'identifiant de commande n'a pas pu être associé à une transaction PostFinance Checkout";
$_['error_empty_refund']							= "Un remboursement vide n'est pas autorisé";
$_['error_currency']								= "Impossible de récupérer les informations sur la devise";
