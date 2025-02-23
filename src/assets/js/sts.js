$(document).ready(function () {
  hljs.highlightAll();

  $("#btnSubmit").click(function (e) {
    e.preventDefault();
    if ($(this).hasClass("disabled")) {
      return;
    }

    $(".button").addClass("disabled");
    $("#txtResponse").html("");
    $.ajax({
      data: {
        controller: "StsController",
        function: "issue",
      },
    })
      .done(function (data) {
        $("#txtRequest").html(convertToHtml(data.Request));
        $("#txtResponse").html(convertToHtml(data.Response));
        hljs.highlightAll();
        $("#txtRequest, #txtResponse").scrollTop(0);
      })
      .always(function () {
        $(".button").removeClass("disabled");
      });
  });
});
