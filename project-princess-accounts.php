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

add_action('admin_enqueue_scripts', 'ppa_enqueue_scripts');
function ppa_enqueue_scripts()
{
    // Enqueue the JavaScript file
    wp_enqueue_script('ppa-js', plugins_url('ppa.js', __FILE__), array('jquery'), '1.0', true);

    // Enqueue the CSS file
    wp_enqueue_style('ppa-cs', plugins_url('ppa.css', __FILE__), array(), '1.0');
}
?>