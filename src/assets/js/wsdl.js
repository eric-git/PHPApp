$(document).ready(function () {
  $("#btnRefresh").click(function (e) {
    e.preventDefault();
    if ($(this).hasClass("disabled")) {
      return;
    }

    $(".button").addClass("disabled");
    $("#txtWsdl").html("");
    $.ajax({
      data: {
        controller: "WsdlController",
        function: "getWsdl",
      },
    })
      .done(function (data) {
        $("#txtWsdl").html(convertToHtml(data.Wsdl));
        hljs.highlightAll();
      })
      .always(function () {
        $(".button").removeClass("disabled");
      });
  });

  hljs.highlightAll();
});
