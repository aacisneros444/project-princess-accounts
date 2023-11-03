<?php

add_shortcode('member_profile', 'ppa_display_member_profile');
function ppa_display_member_profile()
{
    if (is_user_logged_in()) {
        $current_user = wp_get_current_user();
        return $current_user->first_name;
    }
}

?>