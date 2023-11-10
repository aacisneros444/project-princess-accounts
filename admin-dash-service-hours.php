<?php

add_action('admin_menu', 'ppa_admin_dash_service_hours');
function ppa_admin_dash_service_hours()
{
    add_menu_page(
        'View Active Member Hours',
        'View Active Member Hours',
        'manage_options',
        'view-active-member-hours',
        'ppa_render_active_service_member_hours_page',
        'dashicons-admin-plugins'
    );
}

function ppa_render_active_service_member_hours_page()
{
    ppa_render_active_service_member_table();
}

function ppa_render_active_service_member_table()
{
    $active_user_ids = ppa_get_active_user_ids_db();
    foreach ($active_user_ids as $user_id) {
        ppa_member_info_and_events_row($user_id);
    }
}

// Create a "cell" for an active member, displaying their info and service hour events.
function ppa_member_info_and_events_row($user_id)
{
    $user_info = ppa_get_user_info_db($user_id);

    ob_start();

    // Table with single cell for member info
    echo '<table class="member-service-table">';
    // Row for table column names
    echo '<tr>';
    echo '<th>Member Name</th>';
    echo '<th>Email</th>';
    echo '<th>Service Hours</th>';
    echo '<th></th>';
    echo '<tr>';

    echo ppa_member_info_row($user_info["full_name"], $user_info["user_email"], $user_info["total_hours"], $user_info["avatar_url"]);

    echo '</table>';


    // Table for member events
    echo '<table class="member-service-events-table">';

    // Row for table column names
    echo '<tr>';
    echo '<th>Event Name</th>';
    echo '<th>Date</th>';
    echo '<th>Service Hours</th>';
    echo '<th></th>';
    echo '<tr>';

    $approved_service_hour_requests = ppa_get_approved_service_hours_requests_db($user_id);
    foreach ($approved_service_hour_requests as $request) {
        echo ppa_service_event_row($request['title'], $request['event_date'], $request['hours']);
    }

    echo '</table>';


    $output = ob_get_clean();
    echo $output;
}

// Create a member info row
function ppa_member_info_row($name, $email, $hours, $avatar_url)
{
    ob_start();

    echo '<tr>';
    echo '<td class="user-info-cell">';
    echo '<div class="user-info-wrapper">';
    echo '<img class="user-info-cell-pfp" src="' . $avatar_url . '">';
    echo $name;
    echo '</div>';
    echo '</td>';
    echo '<td class="user-info-cell ">' . $email . '</td>';
    echo '<td class="user-info-cell ">' . $hours . '</td>';
    echo '</tr>';

    $output = ob_get_clean();
    return $output;
}

// Create a service event row
function ppa_service_event_row($event_name, $date, $hours)
{
    ob_start();

    echo '<tr>';
    echo '<td class="editable-cell" data-column="event_name">' . $event_name . '</td>';
    echo '<td class="editable-cell" data-column="event_date">' . $date . '</td>';
    echo '<td class="editable-cell" data-column="event_hours">' . $hours . '</td>';
    echo '<td>';
    echo '<button class="edit-row-button">Edit</button>';
    echo '</td>';
    echo '</tr>';

    $output = ob_get_clean();
    return $output;
}

function ppa_get_active_user_ids_db()
{
    global $wpdb;
    $service_hour_requests_table = $wpdb->prefix . 'ppa_service_hour_requests';

    $unique_user_ids = $wpdb->get_col("SELECT DISTINCT user_id FROM $service_hour_requests_table");

    return $unique_user_ids;
}

function ppa_get_user_info_db($user_id)
{
    global $wpdb;

    // Get user_email from wp_users table
    $user_email = $wpdb->get_var($wpdb->prepare("SELECT user_email FROM {$wpdb->prefix}users WHERE ID = %d", $user_id));

    // Get first_name, last_name, and avatar_url from wp_usermeta table
    $first_name = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM {$wpdb->prefix}usermeta WHERE user_id = %d AND meta_key = 'first_name'", $user_id));
    $last_name = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM {$wpdb->prefix}usermeta WHERE user_id = %d AND meta_key = 'last_name'", $user_id));
    $avatar_url = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM {$wpdb->prefix}usermeta WHERE user_id = %d AND meta_key = 'avatar_url'", $user_id));

    // Get the sum of hours from wp_ppa_service_hour_requests where status is "approved"
    $total_approved_hours = $wpdb->get_var($wpdb->prepare("SELECT SUM(hours) FROM {$wpdb->prefix}ppa_service_hour_requests WHERE user_id = %d AND status = 'approved'", $user_id));

    // Return an associative array with the user information
    return array(
        'user_email' => $user_email,
        'full_name' => $first_name . ' ' . $last_name,
        'avatar_url' => $avatar_url,
        'total_hours' => $total_approved_hours,
    );
}

function ppa_get_approved_service_hours_requests_db($user_id)
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'ppa_service_hour_requests';

    $approved_service_hours = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT title, event_date, hours FROM $table_name WHERE user_id = %d AND status = 'approved'",
            $user_id
        ),
        ARRAY_A
    );

    return $approved_service_hours;
}

?>