<?php

add_action('wp_ajax_ppa_update_request_in_db', 'ppa_update_request_in_db');
function ppa_update_request_in_db()
{
    global $wpdb;

    $user_id = $_POST['requestUserId'];
    $requestId = $_POST['requestId'];
    $eventName = $_POST['eventName'];
    $eventDate = $_POST['eventDate'];
    $serviceHours = $_POST['serviceHours'];

    $request_table = $wpdb->prefix . 'ppa_service_hour_requests';
    $data = array('title' => $eventName, 'event_date' => $eventDate, 'hours' => $serviceHours);
    $where = array('id' => $requestId);
    $wpdb->update($request_table, $data, $where);

    ppa_update_user_hours_db($user_id);

    echo json_encode(array('message' => 'Request processed successfully'));
    wp_die();
}

?>