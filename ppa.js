// Function to handle request card button clicks
function handleButtonClick(requestId, serviceHours, action, forUserId, cardID) {
  console.log("Request ID: " + requestId);
  console.log("Service Hours: " + serviceHours);
  console.log("Action: " + action);
  console.log("User ID: " + forUserId);
  // Send an AJAX request to the PHP function
  jQuery.ajax({
    type: "POST",
    url: ajaxurl, // WordPress AJAX URL
    data: {
      action: "ppa_update_db_for_decision",
      requestId: requestId,
      serviceHours: serviceHours,
      decision: action,
      requestUserId: forUserId,
    },
    success: function (response) {
      console.log(response);
      jQuery('.service-hour-request-card[card-uid="' + cardID + '"]').remove();
    },
  });
}

function cardButtonPassDataToHandler(button, action) {
  var parentCard = button.closest(".service-hour-request-card");
  var requestId = parentCard.getAttribute("data-request-id");
  var forUserId = parentCard.getAttribute("for-user-id");
  var cardUID = parentCard.getAttribute("card-uid");
  var serviceHoursInput = button
    .closest(".service-hour-request-card")
    .querySelector(".card-service-hours");
  var serviceHours = serviceHoursInput.value;
  handleButtonClick(requestId, serviceHours, action, forUserId, cardUID);
}

// Function to attach click event listeners to buttons
function attachEventListeners() {
  // Attach a click event listener to all "Approve Hours" buttons
  var approveButtons = document.querySelectorAll(".card-approve-btn");
  approveButtons.forEach(function (button) {
    button.addEventListener("click", function () {
      cardButtonPassDataToHandler(button, "approved");
    });
  });

  // Attach a click event listener to all "Deny Hours" buttons
  var denyButtons = document.querySelectorAll(".card-deny-btn");
  denyButtons.forEach(function (button) {
    button.addEventListener("click", function () {
      cardButtonPassDataToHandler(button, "denied");
    });
  });
}

if (window.location.href.includes("manage-service-hour-requests")) {
  // Wait for the DOM to be fully loaded
  document.addEventListener("DOMContentLoaded", function () {
    // Call the function to attach event listeners after the DOM is ready
    attachEventListeners();
  });
}

// Ensures hours input field can only contain numbers and one decimal point
// when receiving input.
function isNumberKey(txt, evt) {
  var charCode = evt.which ? evt.which : evt.keyCode;
  if (charCode == 46) {
    //Check if the text already contains the . character
    if (txt.value.indexOf(".") === -1) {
      return true;
    } else {
      return false;
    }
  } else {
    if (charCode > 31 && (charCode < 48 || charCode > 57)) return false;
  }
  return true;
}
