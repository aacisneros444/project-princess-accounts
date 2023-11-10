<?php
add_action('admin_menu', 'ppa_plugin_menu');
function ppa_plugin_menu()
{
    add_menu_page(
        'Service Hours Requests',
        'Manage Service Hour Requests',
        'manage_options',
        'manage-service-hour-requests',
        'ppa_service_hour_request_page',
        'dashicons-admin-plugins'
    );
}

function ppa_service_hour_request_page()
{
    echo '<div class="wrap"><h1>Manage Service Hour Requests</h1></div>';
    echo ppa_render_pending_service_hour_requests();
}

function ppa_render_pending_service_hour_requests()
{
    ob_start();

    global $wpdb;
    $users_table = 'wp_users';
    $user_meta_table = 'wp_usermeta';
    $service_hour_requests_table = 'wp_ppa_service_hour_requests';

    // Query the database to request data
    $requests = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT s.*, u1.user_email, u2.meta_value AS first_name, u3.meta_value AS last_name, u4.meta_value AS avatar_url
            FROM $service_hour_requests_table s
            LEFT JOIN $users_table u1 ON s.user_id = u1.ID
            LEFT JOIN $user_meta_table u2 ON s.user_id = u2.user_id AND u2.meta_key = 'first_name'
            LEFT JOIN $user_meta_table u3 ON s.user_id = u3.user_id AND u3.meta_key = 'last_name'
            LEFT JOIN $user_meta_table u4 ON s.user_id = u4.user_id AND u4.meta_key = 'avatar_url'
            WHERE s.status = 'pending approval'"
        )
    );

    if (!empty($requests)) {
        echo '<div class="card-grid">';
        foreach ($requests as $request) {
            echo ppa_service_hour_request_card($request);
        }
        echo '</div>';
    } else {
        echo 'There no pending service hour requests. :)';
    }

    $output = ob_get_clean();
    return $output;
}

$uid_counter = 0;
function ppa_service_hour_request_card($request)
{
    global $uid_counter;
    $output = '';

    $full_name = $request->first_name . ' ' . $request->last_name;
    $email = $request->user_email;
    $event_title = $request->title;
    $service_description = $request->description;
    $service_hours = $request->hours;
    $user_id = $request->user_id;
    $avatar_url = $request->avatar_url;
    $card_uid = $uid_counter;
    $uid_counter += 1;


    // Start building the HTML for the card with Bootstrap-like styles
    $output .= '<div class="service-hour-request-card" data-request-id="' . $request->id . '" for-user-id="' . $user_id . '" card-uid="' . $card_uid . '">';
    $output .= '<div class="user-display-div">';
    $output .= '<img class="user-avatar" src="' . $avatar_url . '" alt="pfp">';
    $output .= '<div class="name-email-div">';
    $output .= '<p class="card-full-name">' . $full_name . '</p>';
    $output .= '<p class="card-email">' . $email . '</p>';
    $output .= '</div>';
    $output .= '</div>';
    $output .= '<p class="card-event-title">' . $event_title . '</p>';
    $output .= '<p class="card-service-description">' . $service_description . '</p>';
    $output .= '<div class="card-service-hr-div">';
    $output .= '<p>Hours:</p>';
    $output .= '<input class="card-service-hours" value="' . $service_hours . '" onkeypress="return isNumberKey(this, event)"><br>';
    $output .= '</div">';
    $output .= '<button class="card-approve-btn" type="button">Approve Hours</button>';
    $output .= '<button class="card-deny-btn" type="button">Deny Hours</button>';
    $output .= '</div>';
    $output .= '</div>';

    return $output;
}
?>