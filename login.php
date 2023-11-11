<?php
// Define shortcode to display login form.
add_shortcode('custom_login', 'ppa_custom_login_page');
function ppa_custom_login_page()
{
    if (!is_user_logged_in()) {
        wp_login_form(
            array(
                'label_username' => 'Email'
            )
        );

        echo '<p>Forgot your password? <a href="' . wp_lostpassword_url() . '">Reset it here</a></p>';
        // Display a link to the registration page
        echo '<p>Don\'t have an account? <a href="' . wp_registration_url() . '">Register here</a></p>';
    } else {
        echo 'Successfully signed in.';
        echo '<script>';
        echo 'if (window.location.href.includes("login")) {';
        echo '  window.location = "' . home_url('member-profile') . '";';
        echo '}';
        echo '</script>';
    }
}

// Hide wordpress admin bar if logged in.
add_action('after_setup_theme', 'ppa_hide_admin_bar');
function ppa_hide_admin_bar()
{
    if (is_user_logged_in() && !current_user_can('administrator')) {
        show_admin_bar(false);
    }
}

// Enforces that users will be taken to their profile page when logging in,
// even through WP UI.
add_filter('login_redirect', 'custom_login_redirect', 10, 3);
function custom_login_redirect($redirect_to, $request, $user)
{
    // Check if the user is logging in from the wp-admin page (profile update).
    if (strpos($request, 'wp-admin') !== false) {
        // Redirect to the user's profile page.
        return home_url('/member-profile');
    }

    return $redirect_to;
}

?>