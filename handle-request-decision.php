<?php
add_action('wp_ajax_ppa_update_db_for_decision', 'ppa_update_db_for_decision');
function ppa_update_db_for_decision()
{
    global $wpdb;

    $requestId = $_POST['requestId'];
    $action = $_POST['decision'];

    $request_table = $wpdb->prefix . 'ppa_service_hour_requests';
    $data = array('status' => $action);
    $where = array('id' => $requestId);
    $wpdb->update($request_table, $data, $where);

    $user_id = $_POST['requestUserId'];
    if ($action == 'approved') {
        ppa_update_user_hours_db($user_id);
    }

    echo json_encode(array('message' => 'Request processed successfully'));
    wp_die();
}

// Update user service hours in db.
function ppa_update_user_hours_db($user_id)
{
    global $wpdb;

    $service_hour_requests_table = $wpdb->prefix . 'ppa_service_hour_requests';
    $total_service_hours = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT SUM(hours) FROM {$service_hour_requests_table} WHERE user_id = %d AND status = 'approved'",
            $user_id
        )
    );

    // Update the user meta with the calculated total
    update_user_meta($user_id, 'service_hours', $total_service_hours);
}
?>