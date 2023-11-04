<?php

add_action('register_form', 'ppa_custom_registration_form');
function ppa_custom_registration_form()
{
    $first_name = !empty($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
    $last_name = !empty($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';

    ?>
    <p>
        <label for="first_name">
            <?php esc_html_e('First Name', 'ppa') ?><br />
            <input type="text" id="first_name" name="first_name" value="<?php echo esc_attr($first_name); ?>" class="input />
        </label>
    </p>
    <?php

    ?>
    <p>
        <label for=" last_name">
            <?php esc_html_e('Last Name', 'ppa') ?><br />
            <input type="text" id="last_name" name="last_name" value="<?php echo esc_attr($last_name); ?>" class="input />
        </label>
    </p>
    <?php
}

// Save custom registration field inputs
add_action('user_register', 'ppa_user_register');
function ppa_user_register($user_id)
{
    if (!empty($_POST['first_name'])) {
        update_user_meta($user_id, 'first_name', $_POST['first_name']);
    }

    if (!empty($_POST['last_name'])) {
        update_user_meta($user_id, 'last_name', $_POST['last_name']);
    }

    // create service hours field
    update_user_meta($user_id, 'service_hours', 0);
}

// Sub email for username in registration process
add_action('login_form_register', 'ppa_copy_email_to_username');
function ppa_copy_email_to_username()
{
    if (isset($_POST['user_email']) && !empty($_POST['user_email'])) {
        $_POST['user_login'] = $_POST['user_email'];
    }
}

// Removes username field from registration
add_action('login_head', function () {
    ?>
    <style>
        #registerform>p:first-child {
            display: none;
        }
    </style>

    <script type=" text/javascript" src="<?php echo site_url('/wp-includes/js/jquery/jquery.js'); ?>"></script>
            <script type="text/javascript">
                jQuery(document).ready(function ($) {
                    $('#registerform > p:first-child').css('display', 'none');
                });
            </script>
            <?php
});

// Remove error for no username, only show error for email only.
add_filter('registration_errors', function ($wp_error, $sanitized_user_login, $user_email) {
    if (isset($wp_error->errors['empty_username'])) {
        unset($wp_error->errors['empty_username']);
    }

    if (isset($wp_error->errors['username_exists'])) {
        unset($wp_error->errors['username_exists']);
    }
    return $wp_error;
}, 10, 3);

?>