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
        $total_service_hours = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT SUM(hours) FROM {$request_table} WHERE user_id = %d AND status = 'approved'",
                $user_id
            )
        );
        // Update the user meta with the calculated total
        update_user_meta($user_id, 'service_hours', $total_service_hours);
    }

    // Return a response if necessary
    wp_die();
}
?>