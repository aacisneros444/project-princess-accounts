jQuery(document).ready(function ($) {
  $(".edit-row-button").click(function () {
    var row = $(this).closest("tr");

    if (row.hasClass("editing")) {
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

      saveUpdatedEventDataToDB(saveEventData);

      row.removeClass("editing");
      $(this).text("Edit");
    } else {
      // Switch to edit mode
      var editableCells = row.find(".editable-cell");
      editableCells.each(function () {
        var text = $(this).text();
        var input = $('<input type="text" class="editing-input">').val(text);
        $(this).html(input);
      });

      row.addClass("editing");
      $(this).text("Save");
    }
  });
});

function saveUpdatedEventDataToDB(saveEventData) {
  console.log(
    saveEventData["event_name"],
    saveEventData["event_date"],
    saveEventData["event_hours"]
  );
  jQuery.ajax({
    type: "POST",
    url: ajaxurl, // WordPress AJAX URL
    data: {
      action: "ppa_update_hours_request_for_admin_edit",
      requestId: requestId,
      eventName: saveEventData["event_name"],
      eventDate: saveEventData["event_date"],
      serviceHours: saveEventData["event_hours"],
      requestUserId: forUserId,
    },
    success: function (response) {
      console.log(response);
    },
  });
}
