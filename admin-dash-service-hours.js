// Init custom event editing logic.
if (window.location.href.includes("view-active-member-hours")) {
  jQuery(document).ready(function ($) {
    $(".edit-row-button").click(function () {
      var row = $(this).closest("tr");

      if (row.hasClass("editing")) {
        var requestID = row.attr("request-id");

        var memberInfoAndEventsCell = row.closest(
          ".member-info-and-events-cell"
        );
        var userID = memberInfoAndEventsCell.attr("user-id");

        var saveEventData = {};

        // Save the edited data and switch back to view mode
        var editableCells = row.find(".editable-cell");
        editableCells.each(function () {
          var input = $(this).find("input");
          var text = input.val();
          var dataName = $(this).data("column");
          saveEventData[dataName] = text;
          $(this).text(text);
        });

        var tdToUpdateOnSuccess =
          memberInfoAndEventsCell.find(".user-hours-cell");
        saveUpdatedEventDataToDB(
          tdToUpdateOnSuccess,
          userID,
          requestID,
          saveEventData
        );

        row.removeClass("editing");
        $(this).text("Edit");
      } else {
        // Switch to edit mode
        var editableCells = row.find(".editable-cell");
        editableCells.each(function () {
          var text = $(this).text();
          var dataName = $(this).data("column");
          var input = $('<input type="text" class="editing-input">').val(text);

          // Prevent bad inputs for event_hours input field
          if (dataName === "event_hours") {
            input.attr("onkeypress", "return isNumberKey(this, event)");
          }
          // Change event_date input field to be of type date
          if (dataName === "event_date") {
            input.attr("type", "date");
          }

          $(this).html(input);
        });

        row.addClass("editing");
        $(this).text("Save");
      }
    });

    // Function to handle delete button click
    function handleDeleteButtonClick() {
      var userInput = prompt(
        "WARNING: this will delete all existing service hour data! This cannot be undone. If proceeding, consider downloading the service data from this period using the link at the top of this page. To confirm deletion, type 'delete all data':"
      );
      if (userInput && userInput.toLowerCase() === "delete all data") {
        deleteAllServiceRequestsDB();
      } else {
        alert("Deletion canceled or incorrect input.");
      }
    }

    // Delete button click event
    $("#delete-all-data-btn").click(function () {
      handleDeleteButtonClick();
    });
  });
}
// Save edited event to db.
function saveUpdatedEventDataToDB(
  toUpdateTD,
  forUserID,
  requestID,
  saveEventData
) {
  jQuery.ajax({
    type: "POST",
    url: ajaxurl, // WordPress AJAX URL
    data: {
      action: "ppa_update_request_in_db",
      requestId: requestID,
      eventName: saveEventData["event_name"],
      eventDate: saveEventData["event_date"],
      serviceHours: saveEventData["event_hours"],
      requestUserId: forUserID,
    },
    success: function (response) {
      changeMemberHoursOnPage(toUpdateTD, forUserID);
    },
  });
}

// Query the db to get updated hours and updat the total service
// hours table cell for the member.
function changeMemberHoursOnPage(toUpdateTD, forUserID) {
  jQuery.ajax({
    type: "POST",
    url: ajaxurl, // WordPress AJAX URL
    data: {
      action: "ppa_get_total_hours_for_member_db",
      requestUserId: forUserID,
    },
    success: function (response) {
      var responseObject = JSON.parse(response);
      toUpdateTD.text(responseObject.hours);
    },
  });
}

// Ask server to delete all service requests in the db.
function deleteAllServiceRequestsDB() {
  jQuery.ajax({
    type: "POST",
    url: ajaxurl, // WordPress AJAX URL
    data: {
      action: "ppa_delete_service_hour_requests_db",
    },
    success: function (response) {
      console.log(response);
      alert("Data deleted successfully!");
      location.reload();
    },
    error: function (err) {
      console.log("error!", err);
    },
  });
}
