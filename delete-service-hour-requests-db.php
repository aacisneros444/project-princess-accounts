<?php
// ajax server-side logic to delete all service hour requests in db
add_action('wp_ajax_ppa_delete_service_hour_requests_db', 'ppa_delete_service_hour_requests_db');
function ppa_delete_service_hour_requests_db()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'ppa_service_hour_requests';
    $wpdb->query("DELETE FROM $table_name");

    update_all_user_service_hours_to_zero();

    echo json_encode(array('response' => "Deletion successful."));
    wp_die();
}

// Set all user service hours to zero.
function update_all_user_service_hours_to_zero()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'usermeta';

    $sql = "UPDATE $table_name
            SET meta_value = '0'
            WHERE meta_key = 'service_hours'
            AND user_id IN (
                SELECT DISTINCT user_id
                FROM $table_name
                WHERE meta_key = 'service_hours'
            )";

    $wpdb->query($sql);
}
?>