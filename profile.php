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

        echo change_profile_picture_form();
        ?>
        <script type="text/javascript">
            if (window.location.href.includes("member-profile")) {
                document.addEventListener("DOMContentLoaded", function () {
                    const profilePictureInput = document.getElementById(
                        "profile-picture-input"
                    );
                    profilePictureInput.addEventListener("change", function () {
                        document.getElementById("profile-picture-form").submit();
                    });
                });
            }
        </script>

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

function change_profile_picture_form()
{
    if (is_user_logged_in()) {
        // Display a form for changing the profile picture
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;
        $avatar_url = get_user_meta($user_id, 'avatar_url', true);
        if (empty($avatar_url)) {
            $avatar_url = plugin_dir_url(__FILE__) . 'pfp.jpg';
        }

        $output = '<div class="change-profile-picture-form">';
        $output .= '<img src="' . esc_url($avatar_url) . '" alt="Current Profile Picture" width="128" style="border-radius: 50%;" /><br>';
        $output .= '<form id="profile-picture-form" action="" method="post" enctype="multipart/form-data">';
        $output .= '<input type="file" name="new_profile_picture" accept="image/*" id="profile-picture-input">';
        $output .= wp_nonce_field('update_profile_picture', 'profile_picture_nonce', true, false);
        $output .= '</form>';
        $output .= '</div>';

        return $output;
    } else {
        return 'You must be logged in to change your profile picture.';
    }
}


function handle_profile_picture_upload()
{
    // Verify the nonce for security
    if (isset($_POST['profile_picture_nonce']) && wp_verify_nonce($_POST['profile_picture_nonce'], 'update_profile_picture')) {
        // Get the current user's ID
        $user_id = get_current_user_id();

        // Check if a file was uploaded
        if (isset($_FILES['new_profile_picture']) && $_FILES['new_profile_picture']['error'] == 0) {
            $uploaded_file = $_FILES['new_profile_picture'];
            $upload_overrides = array('test_form' => false);

            // Include the required file for wp_handle_upload
            require_once(ABSPATH . 'wp-admin/includes/file.php');

            $old_avatar_url = get_user_meta($user_id, 'avatar_url', true);
            // Delete the old profile picture if it exists
            if (!empty($old_avatar_url)) {
                $old_avatar_path = str_replace(site_url('/'), ABSPATH, $old_avatar_url);
                wp_delete_file($old_avatar_path);
            }

            // Upload the new profile picture
            $upload_result = wp_handle_upload($uploaded_file, $upload_overrides);
            if (!empty($upload_result['file'])) {
                $saved_file_path = $upload_result['file'];
                compress($saved_file_path, $saved_file_path, 50);
            }

            if (!empty($upload_result['url'])) {
                // Update the user's avatar with the new image URL
                update_user_meta($user_id, 'avatar_url', $upload_result['url']);
            }
        }
    }
}
add_action('template_redirect', 'handle_profile_picture_upload');

// Compress photo file
function compress($source, $destination, $quality)
{
    $info = getimagesize($source);

    if ($info['mime'] == 'image/jpeg')
        $image = imagecreatefromjpeg($source);
    elseif ($info['mime'] == 'image/gif')
        $image = imagecreatefromgif($source);
    elseif ($info['mime'] == 'image/png')
        $image = imagecreatefrompng($source);

    imagejpeg($image, $destination, $quality);

    return $destination;
}
?>