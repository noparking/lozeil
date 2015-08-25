<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

$param['ext_treasury'] = "1"; // Activation du compte de Trésorerie (1 - Oui par défaut)
$param['ext_simulation'] = "1"; // Activation des simulations (1 - Oui par défaut)
$param['ext_account_custom_result'] = "1"; // Activation du compte de résultats personnalisés (1 - Oui par défaut)
$param['ext_api'] = "1"; // Activitation des APIs (1 - Oui par défaut)

$param['nb_max_writings'] = "100";

//Probabilities bayesian filter
$param['comment_weight'] = "1";
$param['amount_inc_vat_weight'] = "0.3";
$param['threshold'] = "3";
$param['fisher_threshold'] = "0.4";

## définition des paramètres de la gestion des courriels automatiques
$param['email_from'] = "lozeil@noparking.net";		// adresse de l'envoi des messages automatiques ("", par défaut)
$param['email_wrap'] = "50";		// nombre de caractères avant le retour à la ligne automatique (50 - 50 caractères, par défaut)
$param['smtp_host'] = "smtp.noparking.net";
$param['smtp_port'] = "465";

$param['locale_timezone'] = "Europe/Paris";
$param['locale_lang'] = "fr_FR";
$param['currency'] = "&euro;";

$param['accountant_view'] = "0";
$param['contact_help'] = "";

$param['fiscal year begin'] = "03";
$param['nb default activities'] = "1";
