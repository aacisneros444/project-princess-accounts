<?php
add_action('admin_menu', 'ppa_plugin_menu');
function ppa_plugin_menu()
{
    add_menu_page(
        'Service Hours Requests',
        'Manage Service Hour Requests',
        'manage_options',
        'manage-service-hour-requests',
        'ppa_plugin_page',
        'dashicons-admin-plugins'
    );
}

function ppa_plugin_page()
{
    echo '<div class="wrap"><h1>Manage Service Hour Requests</h1></div>';
    echo ppa_render_pending_service_hour_requests();
}

function ppa_render_pending_service_hour_requests()
{
    ob_start();

    ?>
    <style>
        /* Style for the container holding the grid */
        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
    </style>
    <?php
    global $wpdb;
    $users_table = 'wp_users';
    $user_meta_table = 'wp_usermeta';
    $service_hour_requests_table = 'wp_ppa_service_hour_requests';

    // Query the database to request data
    $requests = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT s.*, u1.user_email, u2.meta_value AS first_name, u3.meta_value AS last_name
            FROM $service_hour_requests_table s
            LEFT JOIN $users_table u1 ON s.user_id = u1.ID
            LEFT JOIN $user_meta_table u2 ON s.user_id = u2.user_id AND u2.meta_key = 'first_name'
            LEFT JOIN $user_meta_table u3 ON s.user_id = u3.user_id AND u3.meta_key = 'last_name'
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

?>
<style>
    .service-hour-request-card {
        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
        max-width: 300px;
        padding: 5px;
        border-radius: 5%;
        background-color: #fff;
    }

    .user-avatar {
        border-radius: 50%;
        max-width: 20%;
        max-height: 20%;
    }

    .user-display-div {
        display: flex;
        align-items: center;
    }

    .name-email-div {
        margin-left: 10px;
    }

    .card-service-hours {
        width: 20%;
    }

    .card-service-hr-div {
        display: flex;
        align-items: center;
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
<?php
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
    $card_uid = $uid_counter;
    $uid_counter += 1;


    // Start building the HTML for the card with Bootstrap-like styles
    $output .= '<div class="service-hour-request-card" data-request-id="' . $request->id . '" for-user-id="' . $user_id . '" card-uid="' . $card_uid . '">';
    $output .= '<div class="user-display-div">';
    $output .= '<img class="user-avatar" src="https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_1280.png" alt="pfp">';
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

// Add functionality to card buttons
?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Function to handle button clicks
    function handleButtonClick(requestId, serviceHours, action, forUserId) {
        console.log("Request ID: " + requestId);
        console.log("Service Hours: " + serviceHours);
        console.log("Action: " + action);
        console.log("User ID: " + forUserId);
        // Send an AJAX request to the PHP function
        $.ajax({
            type: "POST",
            url: ajaxurl, // WordPress AJAX URL
            data: {
                action: 'ppa_update_db_for_decision',
                requestId: requestId,
                serviceHours: serviceHours,
                decision: action,
                requestUserId: forUserId
            }
        });
    }

    // Function to attach click event listeners to buttons
    function attachEventListeners() {
        // Attach a click event listener to all "Approve Hours" buttons
        var approveButtons = document.querySelectorAll(".card-approve-btn");
        approveButtons.forEach(function (button) {
            button.addEventListener("click", function () {
                var parentCard = button.closest(".service-hour-request-card");
                var requestId = parentCard.getAttribute("data-request-id");
                var forUserId = parentCard.getAttribute("for-user-id");
                var serviceHoursInput = button.closest(".service-hour-request-card").querySelector(".card-service-hours");
                var serviceHours = serviceHoursInput.value;
                var action = "approved";
                handleButtonClick(requestId, serviceHours, action, forUserId);
            });
        });

        // Attach a click event listener to all "Deny Hours" buttons
        var denyButtons = document.querySelectorAll(".card-deny-btn");
        denyButtons.forEach(function (button) {
            button.addEventListener("click", function () {
                var parentCard = button.closest(".service-hour-request-card");
                var requestId = parentCard.getAttribute("data-request-id");
                var forUserId = parentCard.getAttribute("for-user-id");
                var serviceHoursInput = button.closest(".service-hour-request-card").querySelector(".card-service-hours");
                var serviceHours = serviceHoursInput.value;
                var action = "denied";
                handleButtonClick(requestId, serviceHours, action, forUserId);
            });
        });
    }

    // Wait for the DOM to be fully loaded
    document.addEventListener("DOMContentLoaded", function () {
        // Call the function to attach event listeners after the DOM is ready
        attachEventListeners();
    });
</script>
<?php
?>