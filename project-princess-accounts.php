<?php
/*
Plugin Name: Project Princess Accounts
*/

require_once(plugin_dir_path(__FILE__) . 'login.php');
require_once(plugin_dir_path(__FILE__) . 'profile.php');
require_once(plugin_dir_path(__FILE__) . 'registration.php');
require_once(plugin_dir_path(__FILE__) . 'service-hours-portal.php');
require_once(plugin_dir_path(__FILE__) . 'service-request-table-init.php');
require_once(plugin_dir_path(__FILE__) . 'admin-dash.php');
require_once(plugin_dir_path(__FILE__) . 'handle-request-decision.php');
register_activation_hook(__FILE__, 'ppa_create_service_request_table');
?>