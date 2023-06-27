<?php
/*
Plugin Name: Propojení Fakturoid a EDD
Plugin URL: https://cleverstart.cz
Description: Vygeneruje fakturu po zprocesování platby a pošle ji zákazníkovi na e-mail který uvedl při nákupu
Version: 1.3.25
Author: Pavel Janíček
Author URI: https://cleverstart.cz
*/

require plugin_dir_path(__FILE__) .'vendor/autoload.php';

require plugin_dir_path(__FILE__) . 'libs/class_clvr_fakturoid.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://plugins.cleverstart.cz/?action=get_metadata&slug=edd-fakturoid',
	__FILE__, //Full path to the main plugin file or functions.php.
	'edd-fakturoid'
);

new Clvr_Fakturoid();
