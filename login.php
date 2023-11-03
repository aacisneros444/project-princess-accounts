<?php
// Define shortcode to display login form.
add_shortcode('custom_login', 'ppa_custom_login_page');
function ppa_custom_login_page()
{
    if (!is_user_logged_in()) {
        wp_login_form();

        // Display a link to the registration page
        echo '<p>Don\'t have an account? <a href="' . wp_registration_url() . '">Register here</a></p>';
    } else {
        echo 'You are already logged in.';
    }
}


// Hide wordpress admin bar if logged in.
add_action('after_setup_theme', 'ppa_hide_admin_bar');
function ppa_hide_admin_bar()
{
    if (is_user_logged_in()) {
        show_admin_bar(false);
    }
}

?>