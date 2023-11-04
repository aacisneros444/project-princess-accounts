<?php
add_shortcode('hours_request_form', 'ppa_service_hour_request_form');
function ppa_service_hour_request_form()
{
    global $wpdb;
    ob_start();

    if (isset($_POST['submit_request'])) {
        // Form submitted, handle putting data in DB

        // Get the logged-in user's ID
        $user_id = get_current_user_id();

        // Sanitize and validate form data
        $title = sanitize_text_field($_POST['request_title']);
        $description = sanitize_text_field($_POST['request_description']);
        $hours = floatval($_POST['request_hours']); // Convert to a float

        if ($hours <= 0) {
            return;
        }

        // Insert data into our custom service hour requests table
        $table_name = $wpdb->prefix . 'ppa_service_hour_requests';
        $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'title' => $title,
                'description' => $description,
                'hours' => $hours,
            )
        );

        // Show a success message or redirect to another page
        echo 'Request submitted successfully!';
    }

    // Styling for custom form.
    ?>
    <style>
        #request_description {
            width: 100%;
            max-width: 100%;
            resize: none;
        }
    </style>

    <script>
        // Ensures hours input field can only contain numbers and one decimal point
        // when receiving input.
        function isNumberKey(txt, evt) {
            var charCode = (evt.which) ? evt.which : evt.keyCode;
            if (charCode == 46) {
                //Check if the text already contains the . character
                if (txt.value.indexOf('.') === -1) {
                    return true;
                } else {
                    return false;
                }
            } else {
                if (charCode > 31 &&
                    (charCode < 48 || charCode > 57))
                    return false;
            }
            return true;
        }
    </script>

    <form method="post" action="">
        <label for="request_title">Event Title:</label>
        <br>
        <input type="text" id="request_title" name="request_title" required>
        <br>

        <label for="request_description">Description of Service:</label>
        <br>
        <textarea id="request_description" name="request_description" required rows="4" cols="50"></textarea>
        <br>

        <label for="request_hours">Hours:</label>
        <br>
        <input type="text" id="request_hours" name="request_hours" onkeypress="return isNumberKey(this, event)" required>
        <br>

        <br>
        <input id="submit_request_button" name="submit_request" type="submit" value="Submit Request">
    </form>
    <?php


    $output = ob_get_clean();
    return $output;
}

add_shortcode('hours_request_list', 'ppa_service_hour_requests_list');
function ppa_service_hour_requests_list()
{
    ob_start();

    ?>
    <style>
        .request-list {
            /* Remove bullet points */
            list-style: none;
            /* Add a border around the entire list */
            border: 1px solid #ccc;
            margin: 0;
            padding: 0;
        }

        .request-cell {
            /* Add a border around each request cell */
            border: 1px solid #ccc;
            /* Add padding inside each cell */
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        #requests_nav {
            height: 200px;
            width: 100%;
            overflow: hidden;
            overflow-y: scroll;
            border: none;
        }
    </style>

    <?php

    if (is_user_logged_in()) {
        global $wpdb;
        $current_user_id = get_current_user_id();
        $table_name = $wpdb->prefix . 'ppa_service_hour_requests';

        // Query the database to retrieve requests for the logged-in user
        $requests = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE user_id = %d",
                $current_user_id
            )
        );

        if (!empty($requests)) {
            echo '<nav id="requests_nav">';
            echo '<ul class="request-list">';
            foreach ($requests as $request) {
                echo '<li class="request-cell">';
                echo '<div>';
                echo esc_html($request->title);
                echo '<br>';
                echo esc_html($request->hours) . ' hours<br>';
                echo '</div>';
                echo '<button class="edit-request-button">Edit</button>';
                echo '</li>';
            }
            echo '</ul>';
            echo '</nav>';
        } else {
            echo 'You have no service hour requests.';
        }
    } else {
        echo 'You must be logged in to view your service hour requests.';
    }

    $output = ob_get_clean();
    return $output;
}
?>