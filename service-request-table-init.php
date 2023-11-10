<?php
// Create service hour requests table in WP DB.
function ppa_create_service_request_table()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'ppa_service_hour_requests';

    $sql = "CREATE TABLE $table_name (
        id INT NOT NULL AUTO_INCREMENT,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        event_date DATE NOT NULL,
        description TEXT NOT NULL,
        hours DECIMAL(10,2) NOT NULL,
        status VARCHAR(50) NOT NULL,
        PRIMARY KEY (id)
    );";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
?>