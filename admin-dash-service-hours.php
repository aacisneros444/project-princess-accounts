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
    echo '<h2>Active Member Service Hours</h2>';
    echo '<p>Members will appear in the table below if they have at least one approved service hour request.<p>';

    // Output the download link
    $downloadLink = admin_url('admin-ajax.php?action=ppa_generate_and_download_spreadsheet');
    echo '<p>You can download this data to open in Excel/Google Sheets <a href="' . esc_url($downloadLink) . '">here</a> (note: edits made to downloaded data will not be reflected on the website)</p>';

    ppa_render_active_service_member_table();
}

function ppa_render_active_service_member_table()
{
    $active_user_ids = ppa_get_active_user_ids_db();
    if (count($active_user_ids) == 0) {
        echo '<p>--No active members yet--</p>';
        return;
    }

    foreach ($active_user_ids as $user_id) {
        ppa_member_info_and_events_row($user_id, events_editable: true);
    }

    // Add the delete button and confirmation script
    echo '<button class="delete-all-data-btn" id="delete-all-data-btn">DELETE ALL SERVICE DATA</button>';
}

// Create a "cell" for an active member, displaying their info and service hour events.
function ppa_member_info_and_events_row($user_id, $events_editable = true)
{
    $user_info = ppa_get_user_info_db($user_id);

    ob_start();

    echo '<div class="member-info-and-events-cell" user-id="' . $user_id . '">';

    // Table with single cell for member info
    echo '<table class="member-service-table">';
    // Row for table column names
    echo '<tr>';
    echo '<th>Member Name</th>';
    echo '<th>Email</th>';
    echo '<th>Total Service Hours</th>';
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
        echo ppa_service_event_row($request['id'], $request['title'], $request['event_date'], $request['hours'], $events_editable);
    }

    echo '</table>';

    echo '</div>';


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
    echo '<td class="user-info-cell">' . $email . '</td>';
    echo '<td class="user-info-cell user-hours-cell">' . $hours . '</td>';
    echo '</tr>';

    $output = ob_get_clean();
    return $output;
}

// Create a service event row
function ppa_service_event_row($request_id, $event_name, $date, $hours, $editable)
{
    ob_start();

    echo '<tr request-id="' . $request_id . '">';
    echo '<td class="editable-cell" data-column="event_name">' . $event_name . '</td>';
    echo '<td class="editable-cell" data-column="event_date">' . $date . '</td>';
    echo '<td class="editable-cell" data-column="event_hours">' . $hours . '</td>';
    if ($editable) {
        echo '<td>';
        echo '<button class="edit-row-button">Edit</button>';
        echo '</td>';
    }
    echo '</tr>';

    $output = ob_get_clean();
    return $output;
}

// Get a list of the active member ids by querying the requests table.
function ppa_get_active_user_ids_db()
{
    global $wpdb;
    $service_hour_requests_table = $wpdb->prefix . 'ppa_service_hour_requests';

    $unique_user_ids = $wpdb->get_col("SELECT DISTINCT user_id FROM $service_hour_requests_table WHERE status='approved'");
    return $unique_user_ids;
}

// Get user info from db.
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

// Get all approved service hour requests for a user.
function ppa_get_approved_service_hours_requests_db($user_id)
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'ppa_service_hour_requests';

    $approved_service_hours = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT id, title, event_date, hours FROM $table_name WHERE user_id = %d AND status = 'approved'",
            $user_id
        ),
        ARRAY_A
    );

    return $approved_service_hours;
}

function ppa_delete_service_data_button()
{
    ob_start();

    // Add the Delete All Data Button
    echo '<button id="delete-all-data-btn" onclick="confirmDelete()">DELETE ALL SERVICE DATA</button>';

    // Add the confirmation modal
    echo '<div id="deleteConfirmationModal" class="modal fade" role="dialog">';
    echo '  <div class="modal-dialog">';
    echo '    <div class="modal-content">';
    echo '      <div class="modal-header">';
    echo '        <h4 class="modal-title">Confirmation</h4>';
    echo '        <button type="button" class="close" data-dismiss="modal">&times;</button>';
    echo '      </div>';
    echo '      <div class="modal-body">';
    echo '        <p>Please enter <strong>DELETE ALL DATA</strong> to confirm:</p>';
    echo '        <input type="text" id="deleteConfirmationInput">';
    echo '      </div>';
    echo '      <div class="modal-footer">';
    echo '        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>';
    echo '        <button type="button" class="btn btn-danger" onclick="deleteAllData()">Confirm</button>';
    echo '      </div>';
    echo '    </div>';
    echo '  </div>';
    echo '</div>';

    $output = ob_get_clean();
    return $output;
}

?>