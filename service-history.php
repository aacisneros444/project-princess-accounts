<?php
add_shortcode('service-history', 'ppa_render_service_history');
function ppa_render_service_history()
{
    if (!is_user_logged_in()) {
        echo 'Error: not logged in.';
        echo '<script>window.location = "' . home_url('login') . '";</script>';
        return;
    }

    wp_enqueue_style('ppa-admin-hours-cs', plugins_url('admin-dash-service-hours.css', __FILE__), array(), '1.0');

    $userID = get_current_user_id();

    ppa_member_info_and_events_row($userID, events_editable: false);
}
?>