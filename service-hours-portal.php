<?php
// Hours request form shortcode for service hours portal.
add_shortcode('hours_request_form', 'ppa_service_hour_request_form');
function ppa_service_hour_request_form()
{
    ob_start();

    ppa_create_or_update_request_in_db();

    ppa_render_request_form();

    $output = ob_get_clean();
    return $output;
}

// Hours request form update/delete shortcode for request edit page.
add_shortcode('hours_request_form_update', 'ppa_service_hour_request_update_form');
function ppa_service_hour_request_update_form()
{
    ob_start();

    ppa_create_or_update_request_in_db();

    if (isset($_POST['delete_request'])) {
        $request_id = isset($_GET['request_id']) ? intval($_GET['request_id']) : -1;
        if ($request_id == -1) {
            echo 'Failed to get request id';
            return;
        }
        ppa_delete_request_from_db($request_id);
        echo '<script>window.location = "' . home_url('service-hours-portal') . '";</script>';
    }

    ppa_render_request_form(true);

    // Add a button at the bottom for deleting the request
    ppa_delete_request_button();

    $output = ob_get_clean();
    return $output;
}

// Defines a delete request button.
function ppa_delete_request_button()
{
    ?>
    <script type="text/javascript">
        function confirmDelete() {
            if (confirm('Are you sure you want to delete this service hours request?')) {
                document.getElementById('delete_request_form').submit();
            }
        }
    </script>
    <br>
    <br>
    <input id="delete_request_button" class="delete-button" name="delete_request" type="button" value="DELETE Request"
        onclick="confirmDelete()">
    <!-- hidden form to set delete_request -->
    <form method="post" action="" id="delete_request_form">
        <input type="hidden" name="delete_request" value="1">
    </form>
    <?php
}

// Create / updates a request DB entry.
function ppa_create_or_update_request_in_db()
{
    global $wpdb;
    if (isset($_POST['submit_request']) || isset($_POST['update_request'])) {
        // Form submitted, handle putting data in DB

        // Get the logged-in user's ID
        $user_id = get_current_user_id();

        // Sanitize and validate form data
        $title = sanitize_text_field($_POST['request_title']);
        $description = sanitize_text_field($_POST['request_description']);
        $hours = floatval($_POST['request_hours']); // Convert to a float
        $date = sanitize_text_field($_POST['request_event_date']);

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return;
        }

        if ($hours <= 0) {
            return;
        }

        // Insert data into our custom service hour requests table
        $table_name = $wpdb->prefix . 'ppa_service_hour_requests';
        if (isset($_POST['submit_request'])) {
            $wpdb->insert(
                $table_name,
                array(
                    'user_id' => $user_id,
                    'title' => $title,
                    'event_date' => $date,
                    'description' => $description,
                    'hours' => $hours,
                    'status' => 'pending approval'
                )
            );
        } else if (isset($_POST['update_request'])) {
            $request_id = isset($_GET['request_id']) ? intval($_GET['request_id']) : -1;
            if ($request_id == -1) {
                echo 'Failed to get request id';
                return;
            }
            $wpdb->update(
                $table_name,
                array(
                    'user_id' => $user_id,
                    'title' => $title,
                    'event_date' => $date,
                    'description' => $description,
                    'hours' => $hours,
                ),
                array(
                    'ID' => $request_id
                ),
                array(
                    '%d',
                    '%s',
                    '%s',
                    '%s',
                    '%f',
                ),
                array(
                    '%d'
                )
            );
        }

        // Show a success message or redirect to another page
        echo 'Request submitted successfully!';
        echo '<script>window.location = "' . home_url('service-hours-portal') . '";</script>';
    }
}

// Deletes a request from the DB.
function ppa_delete_request_from_db($request_id)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'ppa_service_hour_requests';

    // Delete the request with the specified ID
    $result = $wpdb->delete(
        $table_name,
        array('ID' => $request_id),
        array('%d')
    );

    if ($result === false) {
        echo 'Failed to delete the request.';
    } else {
        echo 'Request deleted successfully.';
    }
}

// Gets a request from the DB.
function ppa_get_request_from_db($entry_id)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'ppa_service_hour_requests';

    $result = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM $table_name WHERE ID = %d",
            $entry_id
        )
    );

    if ($result) {
        return $result; // Return the result as an object
    } else {
        return null; // Return null if no record is found
    }
}

// Renders the request from.
// If isUpdating is set to true, we expect requestID param to be set in URL -
// will autofill fields with existing request data.
function ppa_render_request_form($isUpdating = false)
{
    if (!is_user_logged_in()) {
        echo 'Error: Not logged in.';
        return;
    }

    $request_id = 0;
    $user_id = '';
    $requestTitle = '';
    $requestEventDate = '';
    $requestDescription = '';
    $requestHours = '';
    $requestStatus = '';
    if ($isUpdating) {
        $request_id = isset($_GET['request_id']) ? intval($_GET['request_id']) : -1;
        if ($request_id == -1) {
            echo 'Failed to get request id';
            return;
        }
        $request_data = ppa_get_request_from_db($request_id);
        if ($request_data) {
            $user_id = $request_data->user_id;
            $requestTitle = $request_data->title;
            $requestEventDate = $request_data->event_date;
            $requestDescription = $request_data->description;
            $requestHours = $request_data->hours;
            $requestStatus = $request_data->status;
        } else {
            echo 'Failed to get request';
            return;
        }

        if (get_current_user_id() != $user_id) {
            echo 'Unauthorized access';
            return;
        }

        if ($request_data->status != 'pending approval') {
            if ($requestStatus == 'approved') {
                echo 'Request was already approved, nothing to do here.';
                return;
            } else {
                echo 'Request was denied, nothing to do here.';
                return;
            }
        }
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

        <label for="request_title">Event Date:</label>
        <br>
        <input type="date" id="request_event_date" name="request_event_date" required>
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
        <?php
        if ($isUpdating) {
            // Display an "Update Request" button if this form is for updating.
            ?>
            <input id="update_request_button" name="update_request" type="submit" value="Update Request">
            <?php
        } else {
            // Display the regular "Submit Request" button.
            ?>
            <input id="submit_request_button" name="submit_request" type="submit" value="Submit Request">
            <?php
        }
        ?>
    </form>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // jQuery code to conditionally pre-fill the form fields when the page loads
        $(document).ready(function () {
            if (<?php echo $isUpdating ? 'true' : 'false'; ?>) {
                // Set the values for form fields using jQuery
                $('#request_title').val('<?php echo $requestTitle; ?>');
                $('#request_event_date').val('<?php echo $requestEventDate; ?>');
                $('#request_description').val('<?php echo $requestDescription; ?>');
                $('#request_hours').val('<?php echo $requestHours; ?>');
            }
        });
    </script>
    <?php
}

// Shortcode for displaying a list of service hour requests.
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
                "SELECT * FROM $table_name WHERE user_id = %d AND status = 'pending approval'",
                $current_user_id
            )
        );

        if (!empty($requests)) {
            echo '<nav id="requests_nav">';
            echo '<ul class="request-list">';
            foreach ($requests as $request) {
                $request_id = $request->id;
                echo '<li class="request-cell">';
                echo '<div>';
                echo esc_html($request->title) . ' - ' . esc_html($request->event_date);
                echo '<br>';
                echo esc_html($request->hours) . ' hours<br>';
                echo '</div>';
                echo '<a href="' . ppa_get_edit_request_url($request_id) . '" class="edit-request-button">Edit</a>';
                echo '</li>';
            }
            echo '</ul>';
            echo '</nav>';
            echo '<small>Pending officer approval for hours to be added to your account.</small>';
        } else {
            echo 'You have no pending service hour requests.';
        }
    } else {
        echo 'You must be logged in to view your service hour requests.';
    }

    $output = ob_get_clean();
    return $output;
}

// Provides the edit request URL for a service hours request.
function ppa_get_edit_request_url($request_id)
{
    return home_url('edit-service-hours-request') . '?request_id=' . $request_id;
}
?>