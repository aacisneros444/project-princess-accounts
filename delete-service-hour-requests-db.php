<?php
// ajax server-side logic to delete all service hour requests in db
add_action('wp_ajax_ppa_delete_service_hour_requests_db', 'ppa_delete_service_hour_requests_db');
function ppa_delete_service_hour_requests_db()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'ppa_service_hour_requests';
    $wpdb->query("DELETE FROM $table_name");

    echo json_encode(array('response' => "Deletion successful."));
    wp_die();
}
?>