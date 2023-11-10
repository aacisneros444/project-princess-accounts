<?php
// ajax server-side logic to get total member hours from db
add_action('wp_ajax_ppa_get_total_hours_for_member_db', 'ppa_get_total_hours_for_member_db');
function ppa_get_total_hours_for_member_db()
{
    global $wpdb;

    $user_id = $_POST['requestUserId'];

    $total_approved_hours = $wpdb->get_var($wpdb->prepare("SELECT SUM(hours) FROM {$wpdb->prefix}ppa_service_hour_requests WHERE user_id = %d AND status = 'approved'", $user_id));

    ppa_gen_active_member_spreadsheet();

    echo json_encode(array('hours' => $total_approved_hours));
    wp_die();
}
?>