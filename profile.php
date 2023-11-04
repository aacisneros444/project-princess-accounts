<?php

add_shortcode('member_profile', 'ppa_display_member_profile');
function ppa_display_member_profile()
{
    if (is_user_logged_in()) {
        $current_user = wp_get_current_user();
        $name_str = $current_user->first_name . ' ' . $current_user->last_name;
        $service_hours = get_user_meta($current_user->ID, 'service_hours', true);
        if (empty($service_hours)) {
            $service_hours = 0;
        }

        // Get the user's profile picture using get_avatar
        $avatar = get_avatar($current_user->ID, 96); // You can adjust the size (e.g., 96) as needed.
        echo $avatar; // Display the profile picture.
        ?>
        <p>
            <?php echo $name_str ?>
        </p>
        <p> Service Hours:
            <?php echo $service_hours ?>
            <br>
            <a href="<?php echo home_url('service-hours-portal') ?>">Make a service hours request</a>
        </p>
        <?php

        ppa_profile_logout_button();
    } else {
        // Redirect to the login page
        echo 'You are not signed in, redirecting you to the login page...';
        echo '<script>window.location = "' . home_url('login') . '";</script>';
    }
}


function ppa_profile_logout_button()
{
    $logout_url = wp_logout_url(home_url());
    ?>
    <form method="post" action="<?php echo esc_url($logout_url); ?>">
        <input type="submit" value="Log Out" name="logout" />
    </form>
    <?php
}
?>